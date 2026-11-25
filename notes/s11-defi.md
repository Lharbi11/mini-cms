# Session 11 - Défi

/mes-articles montre uniquement mes articles, brouillons compris : oui
Un visiteur qui ouvre /mes-articles arrive sur /connexion : oui
Le cookie remember_web_ apparaît quand la case est cochée : oui, vérifié par le test de connexion et le remember_token en base
Les deux nouveaux tests passent et php artisan test est vert : oui

Pourquoi partir de `$request->user()->posts()` plutôt que de `Post::where('user_id', $request->user()->id)` ?

La relation `posts()` porte déjà la condition de propriété, et la requête est construite depuis l'utilisateur authentifié. Cela évite de répéter manuellement la contrainte `user_id` et ne laisse pas un paramètre de formulaire ou d'URL choisir le propriétaire.

Pourquoi la case remember ne doit-elle pas être dans le tableau passé à `Auth::attempt()` ?

Chaque clé du tableau de credentials sert à chercher une colonne de l'utilisateur, sauf le mot de passe qui est vérifié séparément. `remember` n'est pas une colonne de la table `users`; l'option doit donc être transmise comme second argument booléen à `Auth::attempt()`.
