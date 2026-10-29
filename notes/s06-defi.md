# Session 06 - Défi : publier et dépublier

La route posts.publication apparaît dans route:list avec le verbe PATCH : oui

Le bouton affiche « Publier » sur un brouillon et « Dépublier » sur un article publié : oui

published_at est rempli à la publication et vidé à la dépublication : oui

Le nouveau test passe et php artisan test est vert : oui

Pourquoi PATCH et pas PUT ni GET pour ce bouton ?

PATCH modifie seulement l'état de publication de l'article. PUT sert à remplacer l'ensemble de la ressource, et GET ne doit pas modifier de données.
