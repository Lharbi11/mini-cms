<x-layout>
    <x-slot:title>Catégorie introuvable - Mini-CMS</x-slot:title>

    <x-page-header title="Catégorie introuvable" subtitle="Cette catégorie n'existe pas ou a été supprimée." />

    <x-alert type="info" title="Que faire ?" class="max-w-2xl">
        Choisissez une catégorie existante pour parcourir ses articles.
    </x-alert>

    @if ($categories->isNotEmpty())
        <section class="mt-8" aria-labelledby="categories-title">
            <h2 id="categories-title" class="mb-4 text-xl font-bold">Catégories disponibles</h2>

            <ul class="flex flex-wrap gap-3">
                @foreach ($categories as $category)
                    <li>
                        <a
                            href="{{ route('posts.category', $category) }}"
                            class="inline-flex rounded-full bg-indigo-50 px-4 py-2 font-medium text-indigo-700 hover:bg-indigo-100"
                        >
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @else
        <p class="mt-8 text-gray-600">Aucune catégorie n'est disponible pour le moment.</p>
    @endif

    <p class="mt-6">
        <a href="{{ route('posts.index') }}" class="font-medium text-indigo-600 hover:underline">Voir tous les articles</a>
    </p>
</x-layout>
