<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Exceptions\SubscriptionLimitExceeded;
use App\Services\Retrieval\Answerer;
use App\Services\Retrieval\Retriever;
use App\Services\Subscriptions\SubscriptionLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        return view('chat.index', [
            'repos' => $this->getReposForUser(auth()->id()),
        ]);
    }

    public function ask(Request $request, SubscriptionLimits $limits): View|JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'repos' => 'nullable|array',
            'repos.*' => 'uuid',
        ]);

        $user = auth()->user();
        try {
            $limits->ensureCanUseTokens($user);
        } catch (SubscriptionLimitExceeded $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 429);
            }

            return redirect()->route('chat.index')->with('limit_error', $e->getMessage());
        }
        $retriever = new Retriever();
        $sourceIds = $request->input('repos', []);
        $results = $retriever->search($request->question, $user->id, 5, $sourceIds);

        $answer = null;
        $error = null;

        try {
            $answerer = new Answerer();
            $response = $answerer->answer($request->question, $results);
            $answer = $response['answer'];
            $limits->recordTokens($user, $response['tokens']);
        } catch (\Exception $e) {
            $error = 'Answer generation is temporarily unavailable, but here are the relevant files found.';
        }

        $sources = collect($results)->map(fn ($r) => [
            'file_path' => $r->file_path,
            'content' => $r->content,
            'distance' => round($r->distance, 4),
        ])->values()->all();

        if ($request->expectsJson()) {
            return response()->json([
                'answer' => $answer,
                'sources' => $sources,
                'error' => $error,
            ]);
        }

        return view('chat.index', [
            'repos' => $this->getReposForUser($user->id),
            'initialMessages' => [
                ['role' => 'user', 'content' => $request->question],
                [
                    'role' => 'assistant',
                    'content' => $answer ?? 'No answer could be generated.',
                    'sources' => $sources,
                    'error' => $error,
                ],
            ],
        ]);
    }

    private function getReposForUser(string $userId): array
    {
        return Source::query()
            ->whereHas('workspace', fn ($q) => $q->where('user_id', $userId))
            ->orderBy('identifier')
            ->get()
            ->map(fn (Source $source) => [
                'id' => $source->id,
                'name' => $source->identifier,
                'status' => $this->resolveSyncStatus($source),
            ])
            ->values()
            ->all();
    }

    private function resolveSyncStatus(Source $source): string
    {
        $meta = is_array($source->meta) ? $source->meta : [];

        if (($meta['status'] ?? null) === 'error') {
            return 'error';
        }

        if (($meta['status'] ?? null) === 'indexing') {
            return 'indexing';
        }

        if ($source->last_synced_at) {
            return 'indexed';
        }

        return 'indexing';
    }
}
