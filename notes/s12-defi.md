# Session 12 - Défi

1. Pourquoi role n'est-il pas dans `#[Fillable]`, et comment votre contrôleur le modifie-t-il quand même ?

Le rôle n'est pas mass-assignable afin qu'une donnée reçue dans un formulaire ne puisse pas élever les privilèges d'un compte. Le contrôleur autorise d'abord l'action, valide que le rôle vaut `author` ou `admin`, puis affecte directement `$user->role` et appelle `$user->save()`.

2. Pourquoi le callback de `Gate::before` doit-il renvoyer `null`, et non `false`, pour un auteur ?

`null` signifie que le callback ne tranche pas et laisse Laravel poursuivre vers le Gate ou la Policy concernés. `false` refuserait définitivement l'action, y compris à un auteur qui modifie son propre article ; le callback laisse aussi la capacité `changeRole` à la Policy pour qu'un administrateur ne puisse pas changer son propre rôle.
