# Session 02 — Git

## Identité des commits

Git utilise l'identité configurée localement dans le dépôt :

```bash
git config --local user.name
git config --local user.email
git log --format="%h %an <%ae> %s"
```

Vérifier que chaque commit porte le bon nom et la bonne adresse avant le premier push. Si l'historique n'a pas encore été publié et qu'un auteur est incorrect, il peut être corrigé avant publication; après le premier push, ne pas réécrire l'historique.

## Tag et historique

Un tag nomme un commit précis. `lab-01` reste associé au commit de la session 01, même lorsque d'autres commits sont ajoutés ensuite.

```bash
git log --oneline --decorate
git tag
```

## Fichiers privés et ignorés

Avant de publier, vérifier que les fichiers locaux ou générés sont ignorés et ne sont pas suivis :

```bash
git check-ignore -v .env vendor node_modules database/database.sqlite
git ls-files .env vendor node_modules database/database.sqlite
```

La première commande doit montrer la règle d'exclusion de chaque chemin; la seconde ne doit rien afficher. Ne jamais ajouter de secrets au dépôt.

## Notes de session

Ajouter et enregistrer uniquement ce fichier de notes :

```bash
git add notes/s02-git.md
git commit -m "Ajouter les notes Git de la session 02"
```

## 6. Historique depuis lab-01

Nombre de commits depuis lab-01 (avant le commit de ces notes) : 6.

Fichiers modifiés depuis lab-01 : `README.md`, `notes/s02-git.md`, `resources/views/a-propos.blade.php`, `resources/views/heure.blade.php`, `routes/web.php`, `screenshots/s01-accueil.png`, `screenshots/s01-bienvenue.png`, `screenshots/s01-route-list.png`, `screenshots/s02-a-propos.png` et `tests/Feature/ExampleTest.php`.

Ce que `git tag -n` affiche pour lab-01 : `lab-01 lab-01`.