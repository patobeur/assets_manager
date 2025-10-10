# assets_manager

**assets_manager** est une application web conçue pour gérer les prêts de matériel au sein d'une école ou d'une organisation. Elle offre une interface conviviale aux administrateurs et aux agents pour suivre le matériel, gérer les informations sur les étudiants et enregistrer les prêts. L'application est développée en PHP et utilise une base de données MySQL pour stocker les données.

## Fonctionnalités

-  **Gestion des utilisateurs**: Créez, modifiez et supprimez des utilisateurs avec différents rôles (administrateur ou agent).
-  **Gestion des étudiants**: Gérez les informations des étudiants, y compris leur prénom, leur nom et un code-barres unique.
-  **Gestion du matériel**: Suivez tous les matériaux disponibles, leurs descriptions et leur statut (disponible, prêté ou en maintenance).
-  **Gestion des prêts**: Enregistrez les prêts, y compris l'étudiant qui a emprunté le matériel, la date du prêt et la date de retour.
-  **Hydratation des données**: Une fonctionnalité permettant aux administrateurs de peupler la base de données avec des données de démonstration et de la nettoyer lorsqu'elle n'est plus nécessaire.
-  **Interface conviviale**: Une interface simple et intuitive créée avec Tailwind CSS.

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

1. **Clonez le dépôt :**

   ```bash
   git clone https://github.com/ProfLambda/assets_manager.git
   cd assets_manager
   ```

2. **Configurez la base de données :**

   -  Assurez-vous d'avoir un serveur MySQL en cours d'exécution.
   -  Vous pouvez utiliser un outil comme phpMyAdmin ou la ligne de commande MySQL pour créer une nouvelle base de données.

3. **Configurez l'application :**

   -  Accédez au répertoire `public` et ouvrez votre navigateur web à l'adresse `http://localhost/assets_manager/public/install.php` (ou l'URL équivalente pour votre configuration).
   -  Remplissez le formulaire d'installation avec vos informations de connexion à la base de données et créez un compte administrateur.
   -  Cela créera un fichier `config.php` dans le répertoire `config_assets_manager` avec vos paramètres de connexion à la base de données.

4. **Exécutez l'application :**

   -  Pour le développement, vous pouvez utiliser le serveur web intégré de PHP :

      ```bash
      php -S 127.0.0.1:8080 -t public/
      ```

   -  Vous pouvez maintenant accéder à l'application à l'adresse `http://127.0.0.1:8080`.

## Schéma de la Base de Données

La base de données se compose de quatre tables :

-  **am_users**: Stocke les informations des utilisateurs, y compris leurs rôles.

   -  `id`: Clé primaire
   -  `first_name`: Prénom de l'utilisateur
   -  `last_name`: Nom de l'utilisateur
   -  `email`: Email de l'utilisateur (unique)
   -  `password`: Mot de passe haché
   -  `role`: Rôle de l'utilisateur ('agent' ou 'admin')

-  **am_students**: Stocke les informations des étudiants.

   -  `id`: Clé primaire
   -  `first_name`: Prénom de l'étudiant
   -  `last_name`: Nom de l'étudiant
   -  `barcode`: Code-barres unique de l'étudiant

-  **am_materials**: Stocke les informations sur le matériel.

   -  `id`: Clé primaire
   -  `name`: Nom du matériel
   -  `description`: Description du matériel
   -  `status`: Statut du matériel ('available', 'loaned', 'maintenance')
   -  `barcode`: Code-barres unique du matériel

-  **am_loans**: Stocke les enregistrements des prêts.
   -  `id`: Clé primaire
   -  `student_id`: Clé étrangère référençant `am_students(id)`
   -  `material_id`: Clé étrangère référençant `am_materials(id)`
   -  `loan_date`: Date et heure du prêt
   -  `return_date`: Date et heure du retour (peut être nulle)
   -  `loan_user_id`: Clé étrangère référençant `am_users(id)` (l'utilisateur qui a effectué le prêt)
   -  `return_user_id`: Clé étrangère référençant `am_users(id)` (l'utilisateur qui a traité le retour, peut être nulle)
