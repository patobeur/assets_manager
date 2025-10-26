# assets_manager

**assets_manager** est une application web conçue pour gérer les prêts de matériel au sein d'une école ou d'une organisation. Elle offre une interface conviviale aux administrateurs et aux agents pour suivre le matériel, gérer les informations sur les prêts de matériels. L'application est développée en PHP et utilise une base de données MySQL pour stocker les données.

## Fonctionnalités

L'application offre une gamme complète d'outils pour une gestion simple et efficace du matériel.

### Gestion des Entités

Le système permet une gestion complète (CRUD : Créer, Lire, Mettre à jour, Supprimer) des différentes entités de l'application :

-  **Utilisateurs** : Création, modification et suppression des comptes utilisateurs, avec attribution des rôles `administrateur` ou `agent`.
-  **Étudiants** : Gestion des profils étudiants, incluant les informations nom, prénom, email ainsi que la promo et la section.
-  **Matériels** : Suivi du parc de matériel, avec nom, description, catégorie, statut (`disponible`, `prêté`, `en maintenance`) et code-barres unique.
-  **Promotions et Sections** : Organisation des étudiants par promotions (ex: "2025-2027") et sections (ex: "BTS SIO"), entièrement configurables.

### Processus de Prêt et Retour

-  **Interface Simplifiée** : Des pages dédiées pour enregistrer les prêts et les retours en scannant simplement le code-barres de l'étudiant et du matériel.
-  **Mise à jour Automatique** : Le statut du matériel est automatiquement mis à jour à `prêté` lors d'un emprunt et à `disponible` lors d'un retour.
-  **Confirmation Visuelle** : Après chaque opération, un résumé s'affiche, confirmant l'action et montrant les détails du prêt ou du retour, ainsi que l'historique récent de l'étudiant.

### Suivi et Historique

-  **Tableau de Bord** : Affiche en temps réel les prêts en cours, avec une mise en évidence visuelle des prêts de longue durée.
-  **Historique Global** : Une vue complète de tous les prêts (passés et en cours), avec des informations détaillées sur l'étudiant, le matériel, les dates et les agents ayant traité les opérations.
-  **Pages de Détails** : Chaque étudiant et chaque matériel dispose de sa propre page de détails, affichant ses informations complètes ainsi que son historique de prêts personnel.

### Import et Export de Données

-  **Import CSV** : Importez en masse des listes d'étudiants et de matériel. Le système associe automatiquement les étudiants à leur promotion et section en se basant sur leur nom, simplifiant la préparation des données.
-  **Export CSV** : Exportez facilement les listes complètes des étudiants, du matériel, des agents et même l'historique complet des prêts pour une analyse externe ou des archives.
-  **Fichiers d'Exemple** : Des modèles de fichiers CSV sont fournis pour garantir un formatage correct des données à importer.

### Fonctionnalités Administratives

-  **Hydratation des Données** : Une fonctionnalité réservée aux administrateurs pour peupler la base de données avec un jeu de données de démonstration (étudiants, matériels, prêts) et pour la nettoyer en un clic. Idéal pour les démonstrations ou les tests.
-  **Gestion des Rôles** : Seuls les administrateurs peuvent créer, modifier ou supprimer d'autres utilisateurs, ainsi que gérer les promotions et les sections.

### Internationalisation

-  **Support Multilingue** : L'interface est disponible en français et en anglais. Le système de traduction est conçu pour être facilement extensible à d'autres langues via de simples fichiers de configuration (JSON).
-  **Sélecteur de Langue** : Un sélecteur de langue discret (`FR | EN`) est présent dans le menu principal pour permettre aux utilisateurs de changer de langue à tout moment, tout en conservant le contexte de la page actuelle.

### Génération de Codes-barres

-  **Générateur Intégré** : L'application inclut un outil de génération de codes-barres au format **Code 128**, créant des images SVG pour une qualité optimale.
-  **Page d'Étiquettes** : Une page dédiée, accessible depuis le menu d'administration, permet de générer et d'imprimer des étiquettes de codes-barres pour les étudiants et le matériel, facilitant ainsi le déploiement physique du système.

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
   7. `Status`

-  **Format pour le matériel :**
   1. `Nom`
   2. `Description`
   3. `Statut` (Le **nom** du statut, ex: "available", "loaned", "maintenance". Le système le convertira en ID correspondant.)
   4. `Code-barres`
   5. `Catégorie` (L'**ID** de la catégorie. Si l'ID n'existe pas, la catégorie par défaut (ID 1) sera assignée.)

Des fichiers d'exemple sont disponibles au téléchargement directement depuis l'interface d'importation pour vous aider à préparer vos données.

## Sécurité

L'application a été développée avec la sécurité comme priorité :

-  **Prévention des Injections SQL** : Utilisation de requêtes préparées avec PDO pour toutes les interactions avec la base de données.
-  **Protection contre XSS** : Échappement systématique des données affichées dans l'interface utilisateur.
-  **Mots de Passe Sécurisés** : Hachage des mots de passe avec l'algorithme `PASSWORD_DEFAULT` de PHP.
-  **Protection contre le Brute-force** : Le script de connexion intègre une protection qui verrouille temporairement un compte après plusieurs tentatives de connexion échouées.
-  **Configuration des Cookies de Session** : Les cookies sont configurés avec les attributs `HttpOnly`, `Secure` (si HTTPS est activé) et `SameSite=Strict` pour renforcer la sécurité des sessions.
-  **Accès Direct aux Fichiers Empêché** : Une constante `APP_LOADED` est utilisée pour s'assurer que les fichiers PHP ne peuvent pas être exécutés directement via leur URL.

## Stack Technologique

-  **Backend**: PHP 8+
-  **Base de données**: MySQL / MariaDB
-  **Frontend**:
   -  **HTML / JavaScript**: Pour la structure et l'interactivité.
   -  **Tailwind CSS**: Utilisé via un CDN pour un design moderne et réactif sans nécessiter de build local (pas de `npm` ou `node.js` requis).
-  **Serveur web**: Apache ou Nginx. Peut également être exécuté avec le serveur web intégré de PHP pour le développement.

## Architecture

Quelques concepts clés de l'architecture de l'application :

-  **Hiérarchie des Rôles** : Le système de permissions repose sur trois rôles :

   -  `agent` : Peut gérer les prêts et retours de matériel.
   -  `admin` : A les droits de l'`agent`, et peut en plus gérer les utilisateurs (`agent` uniquement), les étudiants et le matériel.
   -  `adminsys` : Super-administrateur qui a tous les droits, y compris la gestion des `admin`, l'accès aux promotions, sections, et à la page d'hydratation.

-  **Système de Modules** : Le dossier `config_assets_manager/modules` permet d'ajouter des fonctionnalités de manière modulaire. Chaque sous-dossier peut contenir des fichiers `header.php` et `footer.php` qui sont automatiquement inclus dans les pages, permettant d'étendre l'application sans modifier le cœur du code.

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

   Si vous creez un virtual host en local, faites comme si vous étiez en ligne

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

-  **`am_users`**:

   -  `id`: INT (PK)
   -  `first_name`, `last_name`, `email` (UNIQUE), `password`: VARCHAR
   -  `role`: ENUM('agent', 'admin', 'adminsys')
   -  `status`: INT (1 pour actif, 0 pour inactif)

-  **`am_promos`** & **`am_sections`**:

   -  `id`: INT (PK)
   -  `title`: VARCHAR

-  **`am_students`**:

   -  `id`: INT (PK)
   -  `first_name`, `last_name`, `barcode` (UNIQUE), `email` (UNIQUE): VARCHAR
   -  `promo_id`: INT (FK vers `am_promos`)
   -  `section_id`: INT (FK vers `am_sections`)
   -  `status`: INT (1 pour actif, 0 pour inactif)

-  **`am_materials_categories`** & **`am_materials_status`**:

   -  `id`: INT (PK)
   -  `title`: VARCHAR

-  **`am_materials`**:

   -  `id`: INT (PK)
   -  `name`, `barcode` (UNIQUE): VARCHAR
   -  `description`: TEXT
   -  `material_categories_id`: INT (FK vers `am_materials_categories`)
   -  `material_status_id`: INT (FK vers `am_materials_status`, défaut: 1)

-  **`am_loans`**:

   -  `id`: INT (PK)
   -  `student_id`: INT (FK vers `am_students`)
   -  `material_id`: INT (FK vers `am_materials`)
   -  `loan_date`: DATETIME
   -  `return_date`: DATETIME (NULLable)
   -  `loan_user_id`: INT (FK vers `am_users`)
   -  `return_user_id`: INT (FK vers `am_users`, NULLable)

-  **`am_options`**:
   -  `id`: INT (PK)
   -  `title`: VARCHAR (utilisé pour stocker diverses options de l'application)
