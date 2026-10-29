@props(['post'])

<article {{ $attributes->class(['flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-center gap-2">
        <h2 class="text-lg font-semibold text-gray-900">
            <a href="{{ route('posts.show', $post['slug']) }}" class="hover:text-indigo-600">{{ $post['title'] }}</a>
        </h2>

        @if ($post['featured'])
            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">À la une</span>
        @endif
    </div>

    <p class="text-sm text-gray-600">{{ $post['excerpt'] }}</p>
    <p class="text-xs text-gray-500">
        {{ $post['author'] }} - {{ $post['date'] }} -
        <a href="{{ route('posts.archive', $post['year']) }}" class="underline">Archive {{ $post['year'] }}</a>
    </p>

    <a href="{{ route('posts.show', $post['slug']) }}" class="text-sm font-medium text-indigo-600 hover:underline">Lire l'article</a>
</article>
