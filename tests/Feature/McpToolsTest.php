<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class McpToolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('sources');
        Schema::dropIfExists('workspaces');
        Schema::dropIfExists('users');

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('github_token')->nullable();
            $table->string('api_token')->nullable()->unique();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('workspaces', function ($table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sources', function ($table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('workspaces');
            $table->string('type')->nullable();
            $table->string('identifier');
            $table->json('meta')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_tools_list_exposes_repo_discovery_and_repo_filtering(): void
    {
        $user = User::factory()->create([
            'api_token' => 'test-token',
        ]);

        Workspace::create([
            'user_id' => $user->id,
            'name' => 'Notes',
        ]);

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ], [
            'Authorization' => 'Bearer test-token',
        ]);

        $response->assertOk();

        $tools = $response->json('result.tools');
        $searchTool = collect($tools)->firstWhere('name', 'search_context');
        $listReposTool = collect($tools)->firstWhere('name', 'list_repos');

        $this->assertNotNull($searchTool);
        $this->assertNotNull($listReposTool);
        $this->assertArrayHasKey('repo', $searchTool['inputSchema']['properties']);
    }

    public function test_list_repos_returns_connected_source_identifiers_for_the_user(): void
    {
        $user = User::factory()->create([
            'api_token' => 'test-token',
        ]);

        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Notes',
        ]);

        Source::create([
            'workspace_id' => $workspace->id,
            'type' => 'github',
            'identifier' => 'owner/Notes',
        ]);

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_repos',
                'arguments' => new \stdClass(),
            ],
        ], [
            'Authorization' => 'Bearer test-token',
        ]);

        $response->assertOk();

        $text = $response->json('result.content.0.text');
        $this->assertStringContainsString('owner/Notes', $text);
    }
}
