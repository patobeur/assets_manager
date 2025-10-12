# assets_manager

**assets_manager** est une application web conçue pour gérer les prêts de matériel au sein d'une école ou d'une organisation. Elle offre une interface conviviale aux administrateurs et aux agents pour suivre le matériel, gérer les informations sur les étudiants et enregistrer les prêts. L'application est développée en PHP et utilise une base de données MySQL pour stocker les données.

## Fonctionnalités

-  **Gestion des utilisateurs**: Créez, modifiez et supprimez des utilisateurs avec différents rôles (administrateur ou agent).
-  **Gestion des étudiants**: Gérez les informations des étudiants, y compris leur prénom, leur nom et un code-barres unique.
-  **Gestion du matériel**: Suivez tous les matériaux disponibles, leurs descriptions et leur statut (disponible, prêté ou en maintenance).
-  **Gestion des prêts**: Enregistrez les prêts, y compris l'étudiant qui a emprunté le matériel, la date du prêt et la date de retour.
-  **Hydratation des données**: Une fonctionnalité permettant aux administrateurs de peupler la base de données avec des données de démonstration et de la nettoyer lorsqu'elle n'est plus nécessaire.
-  **Import et Export CSV**: Importez et exportez facilement des listes d'étudiants et de matériel.
-  **Interface conviviale**: Une interface simple et intuitive créée avec Tailwind CSS.

### Import de Données via CSV

L'application permet d'importer des listes d'étudiants et de matériel en utilisant des fichiers CSV. Cette fonctionnalité est accessible aux administrateurs depuis les pages de gestion des étudiants et du matériel.

**Important :** Pour que l'importation fonctionne correctement, l'ordre des colonnes dans votre fichier CSV doit impérativement correspondre au format attendu par l'application. La première ligne du fichier (contenant les en-têtes) est ignorée lors de l'importation.

-  **Format pour les étudiants :**

   1. `Prénom`
   2. `Nom`
   3. `Email`
   4. `Promo` (le nom de la promotion, ex: "25-27")
   5. `Section` (le nom de la section, ex: "BTS COM")
   6. `Code-barres`

-  **Format pour le matériel :**
   1. `Nom`
   2. `Description`
   3. `Statut` (doit être `available`, `loaned`, ou `maintenance`)
   4. `Code-barres`

Des fichiers d'exemple sont disponibles au téléchargement directement depuis l'interface d'importation pour vous aider à préparer vos données.

## Stack Technologique

-  **Backend**: PHP
-  **Base de données**: MySQL
-  **Frontend**: HTML, Tailwind CSS, JavaScript
-  **Serveur web**: Apache ou Nginx (peut également être exécuté avec le serveur web intégré de PHP pour le développement)

## Aperçu

| Page de Connexion                           | Page des Utilisateurs                            |
| ------------------------------------------- | ------------------------------------------------ |
| ![Page de Connexion](./vignettes/login.png) | ![Page des Utilisateurs](./vignettes/agents.png) |

| Page des Étudiants                              | Page du Matériel                               |
| ----------------------------------------------- | ---------------------------------------------- |
| ![Page des Étudiants](./vignettes/students.png) | ![Page du Matériel](./vignettes/materials.png) |

| Tableau de Bord                               |
| --------------------------------------------- |
| ![Tableau de Bord](./vignettes/dashboard.png) |

## Installation

L'installation a été conçue pour être aussi simple et flexible que possible, que ce soit sur un serveur local ou en ligne.

### 1. Prérequis

-  Un serveur web (Apache, Nginx, etc.) avec PHP 8 ou supérieur.
-  Un serveur de base de données MySQL ou MariaDB.
-  L'extension PHP `php-mysql` doit être activée pour permettre à PHP de communiquer avec la base de données.

### 2. Structure des Fichiers

Pour une sécurité optimale, il est **fortement recommandé** de placer le dossier de configuration en dehors du répertoire public de votre site web.

Voici la structure de dossiers recommandée :

```
/chemin/vers/votre/hebergement/
├── config_assets_manager/   <-- Le dossier de configuration
└── public_html/             <-- La racine de votre site web (ou www, htdocs...)
    └── assets_manager/      <-- Le dossier de l'application
```

-  **`config_assets_manager/`** : Contient les fichiers de configuration sensibles, les modèles de pages, etc.
-  **`public_html/`** (ou équivalent) : C'est le seul dossier accessible depuis internet.
-  **`assets_manager/`** : C'est le dossier que vous avez cloné depuis ce dépôt. Vous pouvez le renommer comme vous le souhaitez.

### 3. Processus d'Installation

1. **Téléversez les fichiers** : Placez les dossiers `config_assets_manager` et `assets_manager` sur votre serveur en respectant la structure recommandée ci-dessus.

2. **Créez une base de données** : À l'aide d'un outil comme phpMyAdmin ou la ligne de commande, créez une base de données vide pour l'application.

3. **Lancez l'installateur** : Ouvrez votre navigateur et accédez à l'URL de l'application. Par exemple :

   -  **En ligne** : `http://votredomaine.com/assets_manager/public/`
   -  **En local** : `http://localhost/assets_manager/public/`

   Vous serez automatiquement redirigé vers la page d'installation.

4. **Suivez les étapes** :
   -  Le script va **automatiquement détecter** l'emplacement du dossier `config_assets_manager`. Il affichera le chemin trouvé pour confirmation.
   -  Remplissez les informations de connexion à la base de données que vous venez de créer.
   -  Créez le compte administrateur principal.
   -  Cliquez sur "Installer".

Et voilà ! L'application est prête à être utilisée. Le script a automatiquement créé les fichiers de configuration nécessaires (`config.php` et `bootstrap.php`) au bon endroit.

### 4. Installation en local (alternative avec le serveur PHP)

Pour un développement rapide en local, vous pouvez utiliser le serveur web intégré de PHP.

1. Clonez le dépôt.
2. Assurez-vous que la structure des dossiers (`config_assets_manager` et `assets_manager` au même niveau) est respectée.
3. Lancez le serveur depuis le dossier `assets_manager` :
   ```bash
   php -S 127.0.0.1:8080 -t public/
   ```
4. Ouvrez `http://127.0.0.1:8080` dans votre navigateur pour lancer l'installation.

## Schéma de la Base de Données

La base de données est composée de plusieurs tables qui assurent la gestion des utilisateurs, des étudiants, du matériel et des prêts.

-  **`am_users`**: Stocke les informations des utilisateurs de l'application.

   -  `id`: Clé primaire (INT)
   -  `first_name`: Prénom de l'utilisateur (VARCHAR)
   -  `last_name`: Nom de l'utilisateur (VARCHAR)
   -  `email`: Email de l'utilisateur, utilisé pour la connexion (VARCHAR, unique)
   -  `password`: Mot de passe haché (VARCHAR)
   -  `role`: Rôle de l'utilisateur (`agent` ou `admin`) (ENUM)

-  **`am_promos`**: Table des promotions (ex: "25-27").

   -  `id`: Clé primaire (INT)
   -  `title`: Nom de la promotion (VARCHAR)

-  **`am_sections`**: Table des sections (ex: "BTS COM").

   -  `id`: Clé primaire (INT)
   -  `title`: Nom de la section (VARCHAR)

-  **`am_students`**: Stocke les informations des étudiants.

   -  `id`: Clé primaire (INT)
   -  `first_name`: Prénom de l'étudiant (VARCHAR)
   -  `last_name`: Nom de l'étudiant (VARCHAR)
   -  `barcode`: Code-barres unique de l'étudiant (VARCHAR, unique)
   -  `email`: Email de l'étudiant (VARCHAR, unique)
   -  `promo_id`: Clé étrangère référençant `am_promos(id)`
   -  `section_id`: Clé étrangère référençant `am_sections(id)`

-  **`am_materials`**: Stocke les informations sur le matériel.

   -  `id`: Clé primaire (INT)
   -  `name`: Nom du matériel (VARCHAR)
   -  `description`: Description détaillée du matériel (TEXT)
   -  `status`: Statut du matériel (`available`, `loaned`, `maintenance`) (ENUM)
   -  `barcode`: Code-barres unique du matériel (VARCHAR, unique)

-  **`am_loans`**: Table de jonction qui enregistre tous les prêts.
   -  `id`: Clé primaire (INT)
   -  `student_id`: Clé étrangère référençant `am_students(id)`
   -  `material_id`: Clé étrangère référençant `am_materials(id)`
   -  `loan_date`: Date et heure du prêt (DATETIME)
   -  `return_date`: Date et heure du retour (DATETIME, peut être `NULL`)
   -  `loan_user_id`: Clé étrangère référençant `am_users(id)` (l'utilisateur qui a validé le prêt)
   -  `return_user_id`: Clé étrangère référençant `am_users(id)` (l'utilisateur qui a validé le retour, peut être `NULL`)
