# Système de Modules

Ce document explique le fonctionnement du système de modules de l'application.

## Principe de Fonctionnement

Le système de modules est conçu pour être simple et permettre d'étendre les fonctionnalités de l'application sans modifier le code principal.

À chaque chargement de page, le système scanne automatiquement le répertoire `config_assets_manager/modules/`. Pour chaque sous-dossier trouvé (qui représente un module), il effectue les actions suivantes :

1. **Chargement du `header.php` :** Si un fichier nommé `header.php` existe à la racine du dossier du module, il est inclus dans le `<head>` de chaque page HTML. C'est l'endroit idéal pour exécuter du code PHP au début du chargement de la page ou pour ajouter des balises `<link>` (CSS) ou `<script>`.
2. **Chargement du `footer.php` :** Si un fichier nommé `footer.php` existe, il est inclus juste avant la fermeture de la balise `</body>`. C'est utile pour exécuter du code PHP en fin de script ou pour ajouter des scripts JavaScript qui doivent se lancer après le chargement du DOM.

## Comment créer un nouveau module ?

1. Créez un nouveau dossier dans `config_assets_manager/modules/`. Le nom du dossier sera le nom de votre module (par exemple, `mon_super_module`).
2. À l'intérieur de ce dossier, créez un fichier `header.php` et/ou un fichier `footer.php`.
3. Ajoutez votre code PHP, HTML, CSS ou JavaScript dans ces fichiers.

Le module sera automatiquement activé au prochain chargement de page. Pour désactiver un module, il suffit de renommer son dossier (par exemple, en ajoutant un `_` devant : `_mon_super_module`) ou de le supprimer.

## Capacités et Limitations

### Ce qui est possible

-  **Exécuter du code PHP à chaque page :** Toute logique PHP placée dans `header.php` ou `footer.php` sera exécutée.
-  **Observer l'état de l'application :** Les modules peuvent accéder aux variables globales et de session (comme `$_SESSION`, `$_POST`, `$_GET`) pour interagir de manière contextuelle avec l'application. Par exemple, un module peut vérifier si un utilisateur est connecté ou si une variable spécifique a été définie par la page en cours.
-  **Ajouter du CSS et du JavaScript :** Vous pouvez insérer des balises `<style>` ou `<script>` directement depuis les fichiers du module.

### Ce qui n'est pas (encore) possible

-  **Installation/Désinstallation automatique :** Il n'y a pas de mécanisme pour créer automatiquement des tables dans la base de données ou pour exécuter un script d'installation unique. Ces opérations doivent être effectuées manuellement.
-  **Hooks spécifiques :** Le système ne propose pas de "hooks" (points d'ancrage) pour s'exécuter à des moments précis de la logique de l'application (par exemple, "juste après la suppression d'un utilisateur"). L'exécution est limitée au début (`header.php`) et à la fin (`footer.php`) du rendu de la page.
