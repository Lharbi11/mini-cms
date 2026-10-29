# Session 03 - Routes, contrôleurs et layout

## Défi

URL produite par `route('about')` :

http://localhost:8000/a-propos

URL produite par `route('posts.show', 'routes-et-controleurs')` :

http://localhost:8000/posts/routes-et-controleurs

URL produite par `route('posts.archive', 2025)` :

http://localhost:8000/posts/archive/2025

Pourquoi `/posts/archive/abc` et `/posts/article-inconnu` répondent toutes deux 404, et quelle ligne de code en est responsable dans chaque cas ?

`/posts/archive/abc` ne satisfait pas la contrainte `whereNumber('year')` définie dans `routes/web.php`. `/posts/article-inconnu` satisfait la route des slugs, mais `PostController::show` appelle `abort(404)` quand le slug n'existe pas dans le tableau des articles.

Que se passe-t-il si une vue appelle `route('posts.show')` sans paramètre ?

Laravel lève une exception `UrlGenerationException` : le paramètre obligatoire `{slug}` de la route `posts.show` manque.
