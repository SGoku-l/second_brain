<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h1 class="text-xl font-bold mb-4">Ask your Second Brain</h1>

        <form method="POST" action="{{ route('chat.ask') }}" class="mb-6">
            @csrf
            <input type="text" name="question" placeholder="Ask something about your repo..."
                   class="w-full border rounded p-3" value="{{ $question ?? '' }}">
            <button type="submit" class="mt-2 bg-black text-white px-4 py-2 rounded">Ask</button>
        </form>

        @if(isset($answer) && $answer)
            <div class="bg-gray-100 p-4 rounded mb-6">
                <strong>Answer:</strong>
                <p>{{ $answer }}</p>
            </div>
        @endif

        @if(isset($error) && $error)
            <div class="bg-yellow-100 p-4 rounded mb-6 text-sm">{{ $error }}</div>
        @endif

        @if(isset($results))
            <h2 class="font-semibold mb-2">Relevant files</h2>
            <div class="space-y-3">
                @foreach($results as $r)
                    <div class="border rounded p-3">
                        <div class="text-sm font-mono text-gray-600">{{ $r->file_path }}</div>
                        <div class="text-xs text-gray-400">distance: {{ round($r->distance, 4) }}</div>
                        <pre class="text-xs mt-2 whitespace-pre-wrap">{{ Str::limit($r->content, 300) }}</pre>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>