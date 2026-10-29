<x-layout>
    <x-slot:title>{{ $heading }} - Mini-CMS</x-slot:title>

    <h1 class="mb-6 text-3xl font-bold">{{ $heading }}</h1>

    <div class="grid gap-6 md:grid-cols-2">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <x-alert type="warning" title="Aucun article" class="md:col-span-2">
                Il n'y a pas encore d'article publié.
            </x-alert>
        @endforelse
    </div>
</x-layout>
