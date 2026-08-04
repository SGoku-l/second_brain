<x-app-layout>
    <div x-data="themeController()" class="min-h-screen transition-colors duration-theme">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @include('components.sb-topbar')

            <div class="mt-6 grid gap-6 lg:grid-cols-[0.95fr,1.05fr]">
                <section class="glass-panel order-1 rounded-[26px] p-6 lg:order-1">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Setup steps</p>
                    <ol class="mt-4 space-y-4 text-sm text-stone-700 dark:text-stone-300">
                        <li>
                            <span class="font-semibold text-stone-900 dark:text-stone-100">1.</span>
                            Open your Cursor or Claude Code workspace.
                        </li>
                        <li>
                            <span class="font-semibold text-stone-900 dark:text-stone-100">2.</span>
                            Create or open the MCP config file at <span class="font-mono text-xs text-sb-accent">.cursor/mcp.json</span>.
                        </li>
                        <li>
                            <span class="font-semibold text-stone-900 dark:text-stone-100">3.</span>
                            Paste the copied JSON block into that file, then restart the agent session.
                        </li>
                        <li>
                            <span class="font-semibold text-stone-900 dark:text-stone-100">4.</span>
                            Ask naturally: <span class="font-mono text-xs">“search my second-brain for auth in the Notes repo”</span>
                        </li>
                    </ol>

                    <div class="mt-6 rounded-2xl border border-dashed border-sb-accent/30 bg-sb-accent/10 p-4 text-sm text-stone-700 dark:text-stone-200">
                        Tip: the MCP server will only use the repos that are already connected to your account, and the repo selector is now discoverable through the server tools.
                    </div>
                </section>

                <section class="glass-panel order-2 rounded-[26px] p-6 lg:order-2">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">Personal token</p>
                            <h3 class="mt-1 text-lg font-semibold text-stone-900 dark:text-stone-100">Your MCP API token</h3>
                        </div>
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{{ $apiToken }}')"
                            class="inline-flex items-center gap-2 rounded-lg border border-sb-accent/30 bg-sb-accent/10 px-3 py-2 text-sm font-medium text-sb-accent hover:bg-sb-accent/15"
                        >
                            Copy token
                        </button>
                    </div>

                    <div class="mt-4 rounded-2xl border border-white/10 bg-black/5 p-3 text-sm font-mono text-stone-700 break-all dark:text-stone-300">
                        {{ $apiToken }}
                    </div>

                    <div class="mt-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sb-accent">mcp.json block</p>
                        <pre class="mt-3 overflow-x-auto rounded-2xl border border-white/10 bg-stone-950 p-4 text-xs text-emerald-200"><code>{
  "mcpServers": {
    "second-brain": {
      "url": "http://127.0.0.1:8000/mcp",
      "headers": {
        "Authorization": "Bearer {{ $apiToken }}"
      }
    }
  }
}</code></pre>

                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText('{\n  \"mcpServers\": {\n    \"second-brain\": {\n      \"url\": \"http://127.0.0.1:8000/mcp\",\n      \"headers\": {\n        \"Authorization\": \"Bearer {{ $apiToken }}\"\n      }\n    }\n  }\n}')"
                            class="mt-4 inline-flex items-center gap-2 rounded-lg border border-white/10 bg-black/5 px-3 py-2 text-sm font-medium text-stone-700 hover:bg-black/10 dark:text-stone-200 dark:hover:bg-white/5"
                        >
                            Copy config
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
