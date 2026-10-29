<x-layout>
    <x-slot:title>{{ $post['title'] }} - Mini-CMS</x-slot:title>

    <article>
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-3xl font-bold">{{ $post['title'] }}</h1>
            @if ($post['featured'])
                <span class="shrink-0 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-700">À la une</span>
            @endif
        </div>

        <p class="mt-2 text-sm text-gray-500">
            Par {{ $post['author'] }} · {{ $post['date'] }} ·
            <a href="{{ route('posts.archive', $post['year']) }}" class="underline">{{ $post['year'] }}</a>
        </p>
        <p class="mt-6 text-lg text-gray-700">{{ $post['excerpt'] }}</p>

        <div class="mt-6 space-y-4 leading-relaxed text-gray-700">
            @foreach ($post['body'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>

    <p class="mt-8">
        <a href="{{ route('posts.index') }}" class="text-red-600 underline">Retour à la liste des articles</a>
    </p>
</x-layout>
