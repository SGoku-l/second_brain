<?php

namespace App\Http\Controllers;

use App\Exceptions\SubscriptionLimitExceeded;
use App\Jobs\IngestRepoJob;
use App\Models\Source;
use App\Models\Chunks;
use App\Models\Workspace;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Subscriptions\SubscriptionLimits;

class RepoController extends Controller
{
    public function available(Request $request)
    {
        $user = $request->user();

        if (! $user || empty($user->github_token)) {
            return response()->json([
                'repos' => [],
            ], 403);
        }

        $token = decrypt($user->github_token);

        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'second-brain-app',
            ],
        ]);

        $response = $client->get('user/repos', [
            'query' => [
                'per_page' => 100,
                'sort' => 'updated',
            ],
        ]);

        $repos = json_decode($response->getBody()->getContents(), true) ?? [];

        $alreadyAdded = Source::query()
            ->whereHas('workspace', fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'github')
            ->pluck('identifier')
            ->all();

        $payload = collect($repos)->map(function (array $repo) use ($alreadyAdded) {
            return [
                'full_name' => $repo['full_name'] ?? null,
                'private' => (bool) ($repo['private'] ?? false),
                'updated_at' => $repo['updated_at'] ?? null,
                'already_added' => in_array($repo['full_name'] ?? '', $alreadyAdded, true),
            ];
        })->filter(fn (array $repo) => ! empty($repo['full_name']))->values()->all();

        return response()->json([
            'repos' => $payload,
        ]);
    }

    public function ingest(Request $request, SubscriptionLimits $limits)
    {
        $validated = $request->validate([
            'repo_full_names' => ['required', 'array'],
            'repo_full_names.*' => ['required', 'string'],
        ]);

        $workspace = Workspace::firstOrCreate([
            'user_id' => $request->user()->id,
            'name' => 'Default',
        ]);

        $selected = collect($validated['repo_full_names'])
            ->filter()
            ->unique()
            ->values()
            ->all();

        try {
            $limits->ensureCanAddRepositories($request->user(), $selected);
        } catch (SubscriptionLimitExceeded $e) {
            return redirect('/dashboard')->with('limit_error', $e->getMessage());
        }

        $count = 0;

        foreach ($selected as $repoFullName) {
            $source = Source::firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'type' => 'github',
                    'identifier' => $repoFullName,
                ],
                [
                    'meta' => [
                        'source' => 'github',
                    ],
                ]
            );

            $source->update([
                'meta' => array_merge($source->meta ?? [], [
                    'source' => 'github',
                    'status' => 'indexing',
                    'last_error' => null,
                    'last_started_at' => now()->toIso8601String(),
                ]),
            ]);

            dispatch(new IngestRepoJob($source->id, $repoFullName));
            $count++;
        }

        return redirect('/dashboard')->with('repo_ingest_message', 'Indexing started for ' . $count . ' repositories');
    }

    public function resync(Request $request, Source $source, SubscriptionLimits $limits)
    {
        $this->ensureOwnedBy($request, $source);

        try {
            $limits->ensureActive($request->user());
        } catch (SubscriptionLimitExceeded $e) {
            return redirect('/dashboard')->with('limit_error', $e->getMessage());
        }

        $source->update([
            'meta' => array_merge($source->meta ?? [], [
                'status' => 'indexing',
                'last_error' => null,
                'last_started_at' => now()->toIso8601String(),
            ]),
        ]);

        dispatch(new IngestRepoJob($source->id, $source->identifier));

        return redirect('/dashboard')->with('repo_ingest_message', 'Re-sync started for '.$source->identifier);
    }

    public function destroy(Request $request, Source $source, SubscriptionLimits $limits)
    {
        $this->ensureOwnedBy($request, $source);
        $repoName = $source->identifier;

        DB::transaction(function () use ($request, $source, $limits) {
            $bytes = (int) Chunks::query()->where('source_id', $source->id)->sum(DB::raw('LENGTH(content)'));
            Chunks::query()->where('source_id', $source->id)->delete();
            $limits->releaseStorage($request->user(), $bytes);
            $source->delete();
        });

        return redirect('/dashboard')->with('repo_ingest_message', $repoName.' removed');
    }

    private function ensureOwnedBy(Request $request, Source $source): void
    {
        abort_unless($source->workspace?->user_id === $request->user()->id, 403);
    }
}
