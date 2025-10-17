<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
	http_response_code(403);
	die('Accès non autorisé.');
}
?>
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

		#mobile-menu {
			max-height: calc(100vh - 80px);
			/* Adjust 80px to match your navbar height */
			overflow-y: auto;
			position: absolute;
			top: 100%;
			left: 0;
			right: 0;
		}

		.last-menu-item .relative>div[id$="-dropdown"] {
			right: 100%;
			left: auto;
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

		.toggle-checkbox:checked {
			right: 0;
			border-color: #48bb78;
		}

		.toggle-checkbox:checked+.toggle-label {
			background-color: #48bb78;
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

				<?php if (isset($_SESSION['user_id'])): ?>
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

						<?php if (isset($_SESSION['user_id'])): ?>
							<!-- Dropdown for Profil -->
							<div class="relative last-menu-item" id="profil-dropdown-menu">
								<button id="profil-dropdown-button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
									<?php echo htmlspecialchars($_SESSION['user_first_name']); ?>
								</button>
								<div id="profil-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
									<?php if ($_SESSION['user_role'] === 'admin'): ?>
										<div class="relative" id="admin-submenu">
											<button id="admin-submenu-button" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
												<?php echo t('admin', 'Admin'); ?>
											</button>
											<div id="admin-submenu-dropdown" class="hidden absolute top-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 z-30" style="right: 100%;">
												<a href="?page=agents" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('agents', 'Agents'); ?></a>
												<a href="?page=promos" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('promos', 'Promos'); ?></a>
												<a href="?page=sections" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('sections', 'Sections'); ?></a>
												<a href="?page=hydration" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('hydration', 'Hydratation'); ?></a>
												<a href="?page=barecode" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><?php echo t('barcode_page_title', 'Étiquettes de codes-barres') ?></a>
											</div>
										</div>
										<div class="border-t border-gray-200 my-1"></div>
									<?php endif; ?>
									<div class="relative" id="language-submenu">
										<button id="language-submenu-button" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
											<?php echo t('language', 'Langue'); ?>
										</button>
										<div id="language-submenu-dropdown" class="hidden absolute top-0 mt-0 w-48 bg-white rounded-md shadow-lg py-1 z-40" style="right: 100%;">
											<?php
											// Prepare language switcher URLs
											$queryParams = $_GET;
											$queryParams['lang'] = 'fr';
											$fr_url = '?' . http_build_query($queryParams);
											$queryParams['lang'] = 'en';
											$en_url = '?' . http_build_query($queryParams);
											$queryParams['lang'] = 'es';
											$es_url = '?' . http_build_query($queryParams);
											?>
											<a href="<?php echo htmlspecialchars($fr_url); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo Language::getInstance()->getLang() === 'fr' ? 'font-bold' : ''; ?>">Français</a>
											<a href="<?php echo htmlspecialchars($en_url); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo Language::getInstance()->getLang() === 'en' ? 'font-bold' : ''; ?>">English</a>
											<a href="<?php echo htmlspecialchars($es_url); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?php echo Language::getInstance()->getLang() === 'es' ? 'font-bold' : ''; ?>">Español</a>
										</div>
									</div>
									<div class="border-t border-gray-200 my-1"></div>
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
				<?php endif; ?>
			</div>

			<!-- Mobile Menu -->
			<div id="mobile-menu" class="hidden md:hidden bg-white">

				<a href="?page=dashboard" class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-200">Tableau de bord</a>
				<?php if (isset($_SESSION['user_id'])): ?>
					<div class="py-2 px-4 text-sm text-gray-500"><?php echo t('management', 'Gestion'); ?></div>
					<a href="?page=students" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('students', 'Étudiants'); ?></a>
					<a href="?page=materials" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('materials', 'Matériels'); ?></a>
					<a href="?page=history" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('history', 'Historique'); ?></a>

					<div class="py-2 px-4 text-sm text-gray-500"><?php echo t('actions', 'Actions'); ?></div>
					<a href="?page=loans" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('loan', 'Emprunt'); ?></a>
					<a href="?page=returns" class="block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('return', 'Retour'); ?></a>


					<div class="border-t border-gray-200 my-1"></div>
					<div class="py-2 px-4 text-sm text-gray-500"><?php echo htmlspecialchars($_SESSION['user_first_name']); ?></div>

					<?php if ($_SESSION['user_role'] === 'admin'): ?>
						<button id="mobile-admin-submenu-button" class="w-full text-left block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200">
							<?php echo t('admin', 'Admin'); ?>
						</button>
						<div id="mobile-admin-submenu" class="hidden pl-4">
							<a href="?page=agents" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('agents', 'Agents'); ?></a>
							<a href="?page=promos" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('promos', 'Promos'); ?></a>
							<a href="?page=sections" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('sections', 'Sections'); ?></a>
							<a href="?page=hydration" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('hydration', 'Hydratation'); ?></a>
							<a href="?page=barecode" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200"><?php echo t('barcode_page_title', 'Étiquettes de codes-barres') ?></a>
						</div>
					<?php endif; ?>
				<?php endif; ?>



				<button id="mobile-language-submenu-button" class="w-full text-left block py-2 pl-8 pr-4 text-sm text-gray-600 hover:bg-gray-200">
					<?php echo t('language', 'Langue'); ?>
				</button>
				<div id="mobile-language-submenu" class="hidden pl-4">
					<?php
					// Prepare language switcher URLs
					$queryParams = $_GET;
					$queryParams['lang'] = 'fr';
					$fr_url = '?' . http_build_query($queryParams);
					$queryParams['lang'] = 'en';
					$en_url = '?' . http_build_query($queryParams);
					$queryParams['lang'] = 'es';
					$es_url = '?' . http_build_query($queryParams);
					?>
					<a href="<?php echo htmlspecialchars($fr_url); ?>" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200 <?php echo Language::getInstance()->getLang() === 'fr' ? 'font-bold' : ''; ?>">Français</a>
					<a href="<?php echo htmlspecialchars($en_url); ?>" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200 <?php echo Language::getInstance()->getLang() === 'en' ? 'font-bold' : ''; ?>">English</a>
					<a href="<?php echo htmlspecialchars($es_url); ?>" class="block py-2 pl-12 pr-4 text-sm text-gray-600 hover:bg-gray-200 <?php echo Language::getInstance()->getLang() === 'es' ? 'font-bold' : ''; ?>">Español</a>
				</div>

				<?php if (isset($_SESSION['user_id'])): ?>
					<a href="logout.php" class="block py-2 pl-8 pr-4 text-sm text-red-500 hover:bg-gray-200"><?php echo t('logout', 'Déconnexion'); ?></a>
				<?php endif; ?>

			</div>
		</nav>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const mainDropdownButtons = [
					'gestion-dropdown-button',
					'actions-dropdown-button',
					'profil-dropdown-button',
				];
				const subMenuButtons = [
					'admin-submenu-button',
					'language-submenu-button',
				];
				const mobileMenuButtons = [
					'mobile-menu-button',
					'mobile-admin-submenu-button',
					'mobile-language-submenu-button',
				];

				function closeAllMainDropdowns() {
					mainDropdownButtons.forEach(btnId => {
						const dropdownId = btnId.replace('-button', '');
						document.getElementById(dropdownId)?.classList.add('hidden');
					});
				}

				function closeAllSubMenus() {
					subMenuButtons.forEach(btnId => {
						const dropdownId = btnId.replace('-button', '-dropdown');
						document.getElementById(dropdownId)?.classList.add('hidden');
					});
				}

				mainDropdownButtons.forEach(btnId => {
					document.getElementById(btnId)?.addEventListener('click', (e) => {
						e.stopPropagation();
						const dropdownId = btnId.replace('-button', '');
						const dropdown = document.getElementById(dropdownId);
						const isHidden = dropdown.classList.contains('hidden');
						closeAllMainDropdowns();
						if (isHidden) dropdown.classList.remove('hidden');
					});
				});

				subMenuButtons.forEach(btnId => {
					document.getElementById(btnId)?.addEventListener('click', (e) => {
						e.stopPropagation();
						const dropdownId = btnId.replace('-button', '-dropdown');
						const dropdown = document.getElementById(dropdownId);
						const isHidden = dropdown.classList.contains('hidden');
						closeAllSubMenus();
						if (isHidden) dropdown.classList.remove('hidden');
					});
				});

				mobileMenuButtons.forEach(btnId => {
					document.getElementById(btnId)?.addEventListener('click', (e) => {
						e.stopPropagation();
						const dropdownId = btnId.replace('-button', '');
						const dropdown = document.getElementById(dropdownId) || document.getElementById(dropdownId + '-submenu');
						dropdown?.classList.toggle('hidden');
					});
				});

				document.addEventListener('click', () => {
					closeAllMainDropdowns();
				});
			});
		</script>
		<main class="flex-grow container mx-auto mt-24 p-4">
		<?php else: ?>
			<main class="flex-grow container mx-auto p-4">
			<?php endif; ?>