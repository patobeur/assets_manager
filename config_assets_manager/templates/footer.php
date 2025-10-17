<?php
// Prevent direct script access.
if (!defined('APP_LOADED')) {
	http_response_code(403);
	die('Accès non autorisé.');
}
?> </main>
<footer class="bg-white p-4 mt-auto">
	<div class="container mx-auto text-center">
		<p><?php echo t('copyright', ['year' => date('Y')]); ?></p>
		<p class="text-sm text-gray-500"><?php echo t('demo_site_version', ['version' => '1.0.0']); ?></p>

		<?php if (isset($_SESSION['user_id'])): ?>
			<div class="mt-2">
				<a href="?page=terms" class="text-blue-500 hover:underline mx-2">Conditions d'utilisation</a>
				<a href="?page=privacy" class="text-blue-500 hover:underline mx-2">Politique de confidentialité</a>
			</div>
		<?php endif; ?>

	</div>
</footer>

<?php
// Load modules footer
$modules_dir = __DIR__ . '/../modules';
if (is_dir($modules_dir)) {
	$modules = array_filter(glob($modules_dir . '/*'), 'is_dir');
	foreach ($modules as $module) {
		$footer_file = $module . '/footer.php';
		if (file_exists($footer_file)) {
			include $footer_file;
		}
	}
}
?>
</body>

</html>