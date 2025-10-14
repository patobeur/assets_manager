<!DOCTYPE html>
<html lang="<?php echo Language::getInstance()->getLang(); ?>">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo t('school_asset_manager', 'Gestionnaire d\'actifs scolaires'); ?></title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="alternate icon" type="image/png" href="assets/assets_manager_logo_64.png">
	<style>
		.navbar {
			backdrop-filter: blur(10px);
		}

		@media print {

			body,
			main {
				margin: 0 !important;
				padding: 0 !important;
				background-color: white !important;
			}

			nav,
			button,
			.no-print {
				display: none !important;
			}

			#barcode-container {
				display: grid;
				grid-template-columns: repeat(3, 1fr);
				gap: 1rem;
			}

			.barcode-label {
				page-break-inside: avoid;
				break-inside: avoid;
				border: 1px solid #ccc;
				padding: 0.5rem;
				border-radius: 0.5rem;
			}
		}

		.break-inside-avoid {
			break-inside: avoid;
		}
	</style>

	<?php
	// Load modules header
	$modules_dir = __DIR__ . '/../modules';
	if (is_dir($modules_dir)) {
		$modules = array_filter(glob($modules_dir . '/*'), 'is_dir');
		foreach ($modules as $module) {
			$header_file = $module . '/header.php';
			if (file_exists($header_file)) {
				include $header_file;
			}
		}
	}
	?>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
	<?php if (!isset($isLoginPage)): ?>
		<nav class="navbar bg-white bg-opacity-75 p-4 fixed w-full top-0 z-10 shadow-md">
			<div class="container mx-auto flex justify-between items-center">
				<a href="?page=dashboard" class="text-xl font-bold text-gray-800 flex items-center">
					<img src="assets/assets_manager_logo_64.png" alt="Logo" class="h-8 mr-2">
					<?php echo t('school_asset_manager', 'Gestionnaire d\'actifs scolaires'); ?>
				</a>
				<div class="hidden md:flex items-center space-x-4">
					<a href="?page=dashboard" class="text-gray-600 hover:text-gray-900"><?php echo t('dashboard', 'Tableau de bord'); ?></a>

					<!-- Dropdown for Gestion -->
					<div class="relative" id="gestion-dropdown-menu">
						<button id="gestion-dropdown-button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
							<?php echo t('management', 'Gestion'); ?>
						</button>
						<div id="gestion-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
							<a href="?page=students" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('students', 'Étudiants'); ?></a>
							<a href="?page=materials" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('materials', 'Matériels'); ?></a>
							<a href="?page=history" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('history', 'Historique'); ?></a>
						</div>
					</div>

					<!-- Dropdown for Actions -->
					<div class="relative" id="actions-dropdown-menu">
						<button id="actions-dropdown-button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
							<?php echo t('actions', 'Actions'); ?>
						</button>
						<div id="actions-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
							<a href="?page=loans" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('loan', 'Emprunt'); ?></a>
							<a href="?page=returns" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('return', 'Retour'); ?></a>
						</div>
					</div>

					<?php if ($_SESSION['user_role'] === 'admin'): ?>
						<!-- Dropdown for Admin -->
						<div class="relative" id="admin-dropdown-menu">
							<button id="admin-dropdown-button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
								<?php echo t('admin', 'Admin'); ?>
							</button>
							<div id="admin-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
								<a href="?page=agents" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('agents', 'Agents'); ?></a>
								<a href="?page=promos" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('promos', 'Promos'); ?></a>
								<a href="?page=sections" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('sections', 'Sections'); ?></a>
								<a href="?page=hydration" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('hydration', 'Hydratation'); ?></a>
								<a href="?page=barecode" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('barcode_page_title', 'Étiquettes de codes-barres') ?></a>

							</div>
						</div>
					<?php endif; ?>


					<?php if (isset($_SESSION['user_id'])): ?>
						<!-- Language Switcher -->
						<div class="text-gray-600">
							<?php
							// Prepare language switcher URLs
							$queryParams = $_GET;
							$queryParams['lang'] = 'fr';
							$fr_url = '?' . http_build_query($queryParams);
							$queryParams['lang'] = 'en';
							$en_url = '?' . http_build_query($queryParams);
							?>
							<a href="<?php echo htmlspecialchars($fr_url); ?>" class="<?php echo Language::getInstance()->getLang() === 'fr' ? 'font-bold' : ''; ?>">FR</a>
							<span>|</span>
							<a href="<?php echo htmlspecialchars($en_url); ?>" class="<?php echo Language::getInstance()->getLang() === 'en' ? 'font-bold' : ''; ?>">EN</a>

						</div>
						<!-- Dropdown for Profil -->
						<div class="relative" id="profil-dropdown-menu">
							<button id="profil-dropdown-button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
								<?php echo htmlspecialchars($_SESSION['user_first_name']); ?>
							</button>
							<div id="profil-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
								<a href="logout.php" class="block px-4 py-2 text-sm text-orange-500 hover:bg-gray-100"><?php echo t('logout', 'Déconnexion'); ?></a>
							</div>
						</div>
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
			<!-- Mobile Menu -->
			<div id="mobile-menu" class="hidden md:hidden">
				<a href="?page=dashboard" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Tableau de bord</a>

				<div class="py-2 px-4 text-sm text-gray-500"><?php echo t('management', 'Gestion'); ?></div>
				<a href="?page=students" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('students', 'Étudiants'); ?></a>
				<a href="?page=materials" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('materials', 'Matériels'); ?></a>
				<a href="?page=history" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('history', 'Historique'); ?></a>

				<div class="py-2 px-4 text-sm text-gray-500"><?php echo t('actions', 'Actions'); ?></div>
				<a href="?page=loans" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('loan', 'Emprunt'); ?></a>
				<a href="?page=returns" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('return', 'Retour'); ?></a>

				<?php if ($_SESSION['user_role'] === 'admin'): ?>
					<div class="py-2 px-4 text-sm text-gray-500">Admin</div>
					<a href="?page=agents" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('agents', 'Agents'); ?></a>
					<a href="?page=promos" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('promos', 'Promos'); ?></a>
					<a href="?page=sections" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('sections', 'Sections'); ?></a>
					<a href="?page=hydration" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('hydration', 'Hydratation'); ?></a>
					<a href="?page=barecode" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('barcode_page_title', 'Étiquettes de codes-barres') ?></a>

				<?php endif; ?>

				<?php if (isset($_SESSION['user_id'])): ?>
					<div class="py-2 px-4 text-sm text-gray-500"><?php echo htmlspecialchars($_SESSION['user_first_name']); ?></div>
					<a href="logout.php" class="block py-2 pl-8 pr-4 text-sm text-red-500 hover:bg-gray-200"><?php echo t('logout', 'Déconnexion'); ?></a>
				<?php endif; ?>
			</div>
		</nav>
		<script>
			// Mobile menu toggle
			const mobileMenuButton = document.getElementById('mobile-menu-button');
			const mobileMenu = document.getElementById('mobile-menu');
			mobileMenuButton.addEventListener('click', () => {
				mobileMenu.classList.toggle('hidden');
			});

			// Dropdown toggle function
			function setupDropdown(buttonId, dropdownId) {
				const button = document.getElementById(buttonId);
				const dropdown = document.getElementById(dropdownId);
				if (button) {
					button.addEventListener('click', (event) => {
						event.stopPropagation();
						// Close other dropdowns
						document.querySelectorAll('.relative > div[id$="-dropdown"]').forEach(d => {
							if (d.id !== dropdownId) {
								d.classList.add('hidden');
							}
						});
						dropdown.classList.toggle('hidden');
					});
				}
			}

			setupDropdown('gestion-dropdown-button', 'gestion-dropdown');
			setupDropdown('actions-dropdown-button', 'actions-dropdown');
			setupDropdown('admin-dropdown-button', 'admin-dropdown');
			setupDropdown('profil-dropdown-button', 'profil-dropdown');


			// Close dropdowns when clicking outside
			document.addEventListener('click', (event) => {
				if (!document.getElementById('admin-dropdown-menu').contains(event.target)) {
					document.getElementById('admin-dropdown').classList.add('hidden');
				}
				if (!document.getElementById('gestion-dropdown-menu').contains(event.target)) {
					document.getElementById('gestion-dropdown').classList.add('hidden');
				}
				if (!document.getElementById('actions-dropdown-menu').contains(event.target)) {
					document.getElementById('actions-dropdown').classList.add('hidden');
				}
				if (!document.getElementById('profil-dropdown-menu').contains(event.target)) {
					document.getElementById('profil-dropdown').classList.add('hidden');
				}
			});
		</script>
		<main class="flex-grow container mx-auto mt-24 p-4">
		<?php else: ?>
			<main class="flex-grow container mx-auto p-4">
			<?php endif; ?>