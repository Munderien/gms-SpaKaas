<?php
/**
 * Language Loader Helper
 * Loads language files from the vertaling folder
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$availableLanguages = ['nl', 'en'];
$currentLang = $_SESSION['language'] ?? 'nl';

// Validate language
if (!in_array($currentLang, $availableLanguages)) {
    $currentLang = 'nl';
    $_SESSION['language'] = $currentLang;
}

// Load language file
$langFile = __DIR__ . "/vertaling/{$currentLang}.php";

if (!file_exists($langFile)) {
    die("Error: Language file not found at {$langFile}");
}

$lang = require_once($langFile);

// Ensure $lang is an array
if (!is_array($lang)) {
    die("Error: Language file did not return an array");
}
?>