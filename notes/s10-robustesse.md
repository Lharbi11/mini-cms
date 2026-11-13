# Session 10 - Robustesse

TagAdminTest contient 5 tests verts : oui
/posts/categorie/inconnue affiche Catégorie introuvable avec le statut 404 : oui
php artisan test est vert : oui

1. Pourquoi le message de messages() l'emporte-t-il sur celui de lang/fr/validation.php ?

Laravel privilégie le message personnalisé propre à une règle et à un champ, fourni par la Form Request. Le catalogue lang/fr/validation.php sert de message général quand aucun message plus précis n'est défini.

2. Pourquoi phpunit.xml fixe-t-il APP_LOCALE ?

Les tests vérifient les textes de validation français. Fixer APP_LOCALE dans phpunit.xml garantit le même résultat sur tous les postes, indépendamment de la locale définie dans le fichier .env local.

3. Pourquoi CategoryController n'a-t-il besoin d'aucun try/catch ?

La Form Request traite les données invalides avant d'appeler le contrôleur, le binding renvoie une 404 pour une catégorie inconnue, et le middleware renvoie une 503 lorsque le site est en lecture seule. Le gestionnaire d'exceptions rend ces réponses adaptées ; le contrôleur n'a pas d'erreur à intercepter et réparer lui-même.
