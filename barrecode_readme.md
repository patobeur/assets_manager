# Custom Barcode Generator

This directory contains a custom-built PHP library for generating barcodes.

## Functionality

The main entry point for the user is the page located at `/public/barcode/index.html`. This page provides a simple interface with:

-   An input field to enter the data to be encoded.
-   A "Generate" button.

When the user enters text into the field and clicks the button, the page sends a request to a backend script (`/public/barcode/generator.php`). This script then uses the `BarcodeGenerator.php` class in this directory to create an SVG image of the corresponding barcode, which is then displayed on the page.

## Supported Barcode Type

Currently, this generator is implemented to produce **Code 128** barcodes. The implementation specifically handles characters within the **Code Set B** range (ASCII characters 32 to 127), which includes uppercase letters, lowercase letters, numbers, and common symbols.