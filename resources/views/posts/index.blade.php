<x-layout>
    <x-slot:title>{{ $heading }} - Mini-CMS</x-slot:title>

    <h1 class="text-3xl font-bold">{{ $heading }}</h1>

    @if (count($posts) > 0)
        <ul class="mt-6 space-y-4">
            @foreach ($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </ul>
    @else
        <x-alert class="mt-6">Aucun article ne correspond à cette année.</x-alert>
    @endif
</x-layout>
