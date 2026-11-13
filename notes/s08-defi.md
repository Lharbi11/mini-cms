# Session 08 - Défi

## Auto-vérification

- La route `posts.tag` utilise `{tag:slug}` : oui
- La page d'un tag n'affiche ni brouillon ni article d'un autre tag : oui
- Les tags des cartes sont des liens : oui
- L'accueil n'affiche que les tags avec au moins un article publié : oui
- Les trois nouveaux tests passent et toute la suite `php artisan test` est verte : oui

## Questions

**Pourquoi filtrer les tags vides après `get()` plutôt que d'afficher (0) ?**

Un tag sans article publié n'est pas populaire et n'a pas de page utile à proposer aux visiteurs. On le retire donc de la collection avant l'affichage, sans ajouter de clause SQL de filtrage pour ce petit résultat.

**Combien de requêtes ajoute la liste des tags populaires, et pourquoi pas une par tag ?**

Une requête récupère les tags et leurs compteurs. `withCount()` calcule le nombre d'articles publiés pour chaque tag dans cette même requête, donc il n'y a pas de requête supplémentaire par tag ni de problème N+1.

**Pourquoi aucune migration, aucun modèle et aucun test de base de données spécifique au pilote n'a changé pour PostgreSQL ?**

Eloquent et le constructeur de schéma génèrent des requêtes compatibles avec les différents pilotes. Le code utilise les API Laravel et des comparaisons booléennes explicites plutôt que du SQL propre à un moteur ; seules les informations de connexion changent.
