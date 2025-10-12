    </main>
    <footer class="bg-white p-4 mt-auto">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date('Y'); ?> Gestionnaire d'actifs scolaires. Tous droits réservés.</p>
            <p class="text-sm text-gray-500">Site de démonstration, Version 1.0.0</p>
            <div class="mt-2">

                <a href="?page=terms" class="text-blue-500 hover:underline mx-2">Conditions d'utilisation</a>
                <a href="?page=privacy" class="text-blue-500 hover:underline mx-2">Politique de confidentialité</a>


            </div>
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