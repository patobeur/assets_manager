<?php

// Prevent direct script access.
define('APP_LOADED', true);

// Include the custom barcode generator.
require_once __DIR__ . '/../../config_assets_manager/lib/BarcodeGenerator.php';

// Check if data is provided in the GET request
if (isset($_GET['data']) && !empty(trim($_GET['data']))) {
    $data = trim($_GET['data']);

    try {
        $generator = new BarcodeGenerator($data);
        $barcode = $generator->getBarcode();

        // Set the proper header for SVG output
        header('Content-Type: image/svg+xml');

        // Echo the SVG content
        echo $barcode;

    } catch (\Exception $e) {
        // Handle potential errors
        http_response_code(500);
        header('Content-Type: image/svg+xml');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="40" viewBox="0 0 200 40">
                <text x="10" y="25" font-family="sans-serif" font-size="10" fill="red">Error: ' . htmlspecialchars($e->getMessage()) . '</text>
              </svg>';
    }

} else {
    // If no data is provided, return a Bad Request error.
    http_response_code(400);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="40" viewBox="0 0 200 40">
            <text x="10" y="25" font-family="sans-serif" font-size="10" fill="red">Error: No data provided.</text>
          </svg>';
}

exit;