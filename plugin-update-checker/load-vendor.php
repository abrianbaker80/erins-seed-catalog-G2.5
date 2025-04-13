<?php
/**
 * Load vendor files for the plugin update checker
 */

// Define the vendor directory with absolute path
$vendorDir = dirname(__FILE__) . '/vendor';

// Load PucReadmeParser
if (!class_exists('PucReadmeParser', false)) {
    $pucReadmeParserFile = $vendorDir . '/PucReadmeParser.php';
    if (file_exists($pucReadmeParserFile)) {
        require_once $pucReadmeParserFile;
    } else {
        error_log('PucReadmeParser.php not found at: ' . $pucReadmeParserFile);
    }
}

// Load Parsedown
if (!class_exists('Parsedown', false)) {
    $parsedownFile = $vendorDir . '/Parsedown.php';
    if (file_exists($parsedownFile)) {
        require_once $parsedownFile;
    } else {
        error_log('Parsedown.php not found at: ' . $parsedownFile);
    }
}
