<x-layout>
    <x-slot:title>Article introuvable - Mini-CMS</x-slot:title>

    <x-page-header title="Article introuvable" subtitle="Cet article n'existe pas ou a été supprimé." />

    <x-alert type="info" title="Que faire ?" class="max-w-2xl">
        Vérifiez l'adresse, ou parcourez la liste des articles.
    </x-alert>

    <p class="mt-6">
        <a href="{{ route('posts.index') }}" class="font-medium text-indigo-600 hover:underline">Voir tous les articles</a>
    </p>

    @if ($recentPosts->isNotEmpty())
        <section class="mt-12" aria-labelledby="recent-posts-title">
            <h2 id="recent-posts-title" class="mb-4 text-xl font-bold">Articles récents</h2>

            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($recentPosts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
