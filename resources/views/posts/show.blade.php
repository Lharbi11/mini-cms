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
    </article>
</x-layout>
