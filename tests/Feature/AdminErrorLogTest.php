<?php

namespace Tests\Feature;

use App\Models\ErrorLog;
use App\Models\Source;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminErrorLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_error_context_and_mark_it_resolved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $affectedUser = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $affectedUser->id, 'name' => 'Default']);
        $source = Source::create(['workspace_id' => $workspace->id, 'type' => 'github', 'identifier' => 'owner/broken-repo']);
        $error = ErrorLog::create([
            'user_id' => $affectedUser->id,
            'source_id' => $source->id,
            'level' => 'error',
            'message' => 'Repository ingestion failed.',
            'exception_class' => \RuntimeException::class,
            'context' => ['exception_message' => 'GitHub request failed', 'stack_trace' => '#0 /app/IngestRepoJob.php:42', 'repository' => 'owner/broken-repo'],
        ]);

        $this->actingAs($admin)->get(route('admin.errors.show', $error))
            ->assertOk()
            ->assertSee('GitHub request failed')
            ->assertSee('owner/broken-repo')
            ->assertSee($affectedUser->email);

        $this->actingAs($admin)->patch(route('admin.errors.resolve', $error))
            ->assertRedirect();

        $this->assertDatabaseMissing('error_logs', ['id' => $error->id, 'resolved_at' => null]);
        $this->actingAs($admin)->get(route('admin.errors', ['filter' => 'all']))
            ->assertOk()
            ->assertSee('Resolved');
    }
}
