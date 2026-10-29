<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mini-CMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
    <x-navbar />

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">
        Mini-CMS - Atelier Framework Côté Serveur - ISET Sidi Bouzid
    </footer>
</body>
</html>
