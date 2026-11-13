@props(['post'])

<article {{ $attributes->class(['flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-center gap-2">
        <h2 class="text-lg font-semibold text-gray-900">
            <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600">{{ $post->title }}</a>
        </h2>

        @if (! $post->is_published)
            <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">Brouillon</span>
        @endif
    </div>

    <p class="text-xs text-gray-500">
        @if ($post->category)
            <a href="{{ route('posts.category', $post->category) }}" class="underline hover:text-indigo-600">{{ $post->category->name }}</a>
        @else
            Sans catégorie
        @endif
        - par {{ $post->user?->name ?? 'auteur inconnu' }}
    </p>

    <p class="text-sm text-gray-600">{{ $post->excerpt ?? Str::limit($post->body, 120) }}</p>

    @if ($post->tags->isNotEmpty())
        <ul class="flex flex-wrap gap-2" aria-label="Tags de l'article">
            @foreach ($post->tags as $tag)
                <li class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $tag->name }}</li>
            @endforeach
        </ul>
    @endif

    <p class="text-xs text-gray-500">
        @if ($post->published_at)
            Publié le {{ $post->published_at->format('d/m/Y') }} -
            <a href="{{ route('posts.archive', $post->published_at->year) }}" class="underline">Archive {{ $post->published_at->year }}</a>
        @else
            Non publié
        @endif
    </p>

    <a href="{{ route('posts.show', $post) }}" class="text-sm font-medium text-indigo-600 hover:underline">Lire l'article</a>
</article>
