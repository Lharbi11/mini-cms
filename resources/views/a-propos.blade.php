<x-layout>
    <x-slot:title>À propos - Mini-CMS</x-slot:title>

    <h1 class="text-3xl font-bold">À propos de Mini-CMS</h1>
    <p class="mt-4 text-gray-700">Mini-CMS est le projet fil rouge de l'Atelier Framework Côté Serveur (Laravel 13).</p>
    <p class="mt-2 text-gray-700">Auteur : {{ $auteur }}</p>
    <p class="mt-2 text-gray-700">Groupe : {{ $groupe }}</p>
    <p class="mt-6">
        <a href="{{ route('home') }}" class="text-red-600 underline">Accueil</a> |
        <a href="{{ route('time') }}" class="text-red-600 underline">Heure</a>
    </p>
</x-layout>
