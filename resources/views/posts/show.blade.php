<x-layout>
    <x-slot:title>{{ $post->title }} - Mini-CMS</x-slot:title>

    <article class="max-w-2xl">
        <a href="{{ route('posts.index') }}" class="text-sm text-indigo-600 hover:underline">Retour aux articles</a>

        <h1 class="mt-4 mb-2 text-3xl font-bold">{{ $post->title }}</h1>

        <p class="mb-6 text-sm text-gray-500">
            @if ($post->published_at)
                Publié le {{ $post->published_at->format('d/m/Y') }}
            @else
                Brouillon
            @endif
        </p>

        @if ($post->excerpt)
            <p class="mb-6 text-lg text-gray-600">{{ $post->excerpt }}</p>
        @endif

        <div class="whitespace-pre-line leading-relaxed text-gray-800">{{ $post->body }}</div>

        <div class="mt-8 flex flex-wrap items-start gap-4">
            <a href="{{ route('posts.edit', $post) }}" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50">Modifier</a>
        </div>
    </article>
</x-layout>
