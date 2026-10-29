<nav class="mx-auto flex max-w-3xl items-center gap-6 px-4 py-4">
    <a href="{{ route('home') }}" class="text-lg font-bold">Mini-CMS</a>
    <x-nav-link route="home" :active="request()->routeIs('home')">Accueil</x-nav-link>
    <x-nav-link route="posts.index" :active="request()->routeIs('posts.*')">Articles</x-nav-link>
    <x-nav-link route="time" :active="request()->routeIs('time')">Heure</x-nav-link>
    <x-nav-link route="about" :active="request()->routeIs('about')">À propos</x-nav-link>
</nav>
