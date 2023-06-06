# GFonts-Downloader

PHP class to download google fonts in woff2 format

## Installation

```sh
composer require gfontsdownloader/gfontsdownloader
```

## Example usage

```php
<?php
// Import GFontsDownloader class into the global namespace
use GFontsDownloader\GFontsDownloader\GFontsDownloader;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Create an instance 
// 1st param : Google Fonts URL
// 2nd param : Fonts directory name (default : fonts)
$gfd = new GFontsDownloader('https://fonts.googleapis.com/css2?family=Langar&family=Niramit:wght@300;700&display=swap','fontDirectory');

// Download all fonts, create directory and create font-face.css file
$gfd->download();
```