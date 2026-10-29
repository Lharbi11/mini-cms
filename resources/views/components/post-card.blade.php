@props(['post'])

<li {{ $attributes->class(['rounded border border-gray-200 bg-white p-4']) }}>
    <div class="flex items-start justify-between gap-4">
        <a href="{{ route('posts.show', $post['slug']) }}" class="text-xl font-medium text-red-600 hover:underline">
            {{ $post['title'] }}
        </a>
        @if ($post['featured'])
            <span class="shrink-0 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-700">À la une</span>
        @endif
    </div>
    <p class="mt-2 text-gray-700">{{ $post['excerpt'] }}</p>
    <p class="mt-2 text-sm text-gray-500">
        Par {{ $post['author'] }} · {{ $post['date'] }} ·
        <a href="{{ route('posts.archive', $post['year']) }}" class="underline">{{ $post['year'] }}</a>
    </p>
</li>
