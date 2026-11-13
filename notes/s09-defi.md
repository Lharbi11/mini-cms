# Session 09 - Défi

## Auto-vérification

- Le bandeau s'affiche sur toutes les pages quand `SITE_READ_ONLY=true` : oui
- Le bandeau disparaît quand `SITE_READ_ONLY=false` : oui
- La page Article introuvable propose au plus trois articles publiés, sans brouillon : oui
- Aucune `LazyLoadingViolationException` sur `/posts/999` : oui
- Les deux nouveaux tests passent et `php artisan test` est vert : oui

## Questions

**Pourquoi le bandeau est-il dans le layout et pas dans chaque vue ?**

Le layout entoure toutes les pages du site, y compris les pages d'erreur. Le bandeau n'est ainsi défini qu'à un seul endroit et reste cohérent partout.

**Pourquoi le test du bandeau n'a-t-il pas besoin de modifier `.env` ?**

`config(['site.read_only' => true])` modifie la configuration uniquement pendant le test. La valeur de `phpunit.xml` reste fausse pour les autres tests, et `.env` n'a pas besoin d'être changé.
