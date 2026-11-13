# Session 07 - Défi : articles de la même catégorie

La section « Dans la même catégorie » apparaît sous un article publié qui a une catégorie : oui

L'article affiché n'apparaît pas dans sa propre section : oui

Un brouillon de la même catégorie n'apparaît pas : oui

La page s'ouvre sans LazyLoadingViolationException : oui

Le nouveau test passe et php artisan test est vert : oui

Combien de requêtes SQL charge la section, et pourquoi pas une par carte ?

Quatre requêtes : une pour les articles recommandés, puis une pour charger en lot les auteurs, une pour les catégories et une pour les tags. `with()` regroupe les relations des trois articles dans des requêtes par relation, plutôt que d'en exécuter une pour chaque carte.
