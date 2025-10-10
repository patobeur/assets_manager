<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire d'actifs scolaires</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="alternate icon" type="image/png" href="/assets/assets_manager_logo_64.png">
    <style>
        .navbar {
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <nav class="navbar bg-white bg-opacity-75 p-4 fixed w-full top-0 z-10 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-gray-800">Gestionnaire d'actifs scolaires</a>
            <div class="hidden md:flex items-center space-x-4">
                <a href="?page=dashboard" class="text-gray-600 hover:text-gray-900">Tableau de bord</a>

                <a href="?page=students" class="text-gray-600 hover:text-gray-900">Étudiants</a>
                <a href="?page=materials" class="text-gray-600 hover:text-gray-900">Matériels</a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="?page=agents" class="text-gray-600 hover:text-gray-900">Agents</a>
                <?php endif; ?>

                <a href="?page=loans" class="text-gray-600 hover:text-gray-900">Emprunt</a>
                <a href="?page=returns" class="text-gray-600 hover:text-gray-900">Retour</a>
                <a href="?page=history" class="text-gray-600 hover:text-gray-900">Historique</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Déconnexion</a>
                <?php endif; ?>
            </div>
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden">
            <a href="?page=dashboard" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Tableau de bord</a>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="?page=students" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Étudiants</a>
                <a href="?page=materials" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Matériels</a>
                <a href="?page=agents" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Agents</a>
            <?php endif; ?>
            <a href="?page=loans" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Emprunt</a>
            <a href="?page=returns" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Retour</a>
            <a href="?page=history" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Historique</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="block py-2 px-4 text-sm text-red-500 hover:bg-gray-200">Déconnexion</a>
            <?php endif; ?>
        </div>
    </nav>
    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
    <main class="flex-grow container mx-auto mt-24 p-4">