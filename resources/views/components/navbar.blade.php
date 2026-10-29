<header class="border-b border-gray-200 bg-white">
    <nav class="mx-auto flex max-w-5xl flex-wrap items-center gap-2 px-4 py-3" aria-label="Navigation principale">
        <a href="{{ route('home') }}" class="mr-4 text-lg font-bold text-indigo-600">Mini-CMS</a>

        <x-nav-link to="home">Accueil</x-nav-link>
        <x-nav-link to="posts.index" match="posts.*">Articles</x-nav-link>
        <x-nav-link to="time">Heure</x-nav-link>
        <x-nav-link to="about">À propos</x-nav-link>
    </nav>
</header>
