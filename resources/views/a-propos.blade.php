<x-layout>
    <x-slot:title>À propos - Mini-CMS</x-slot:title>

    <x-page-header title="À propos" subtitle="Le projet Mini-CMS et ses objectifs." />

    <div class="max-w-2xl space-y-4">
        <p class="leading-relaxed text-gray-700">
            Mini-CMS est le projet fil rouge de l'Atelier Framework Côté Serveur, 3e année MDW, ISET Sidi Bouzid.
        </p>

        <p class="text-gray-700">Auteur : {{ $auteur }} - Groupe : {{ $groupe }}</p>

        <x-alert type="info" title="Projet pédagogique">
            Ce site évolue à chaque session de l'atelier.
        </x-alert>

        <x-alert type="success" title="Données en base">
            Les articles sont enregistrés dans une base SQLite et lus avec Eloquent.
        </x-alert>
    </div>
</x-layout>
