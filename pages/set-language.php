<?php
session_start();

// Validate and set the language
$availableLanguages = ['nl', 'en', 'de', 'fr', 'tr'];
$lang = isset($_GET['lang']) && in_array($_GET['lang'], $availableLanguages) ? $_GET['lang'] : 'nl';

$_SESSION['language'] = $lang;

// Redirect back to the referring page or home
$referer = $_SERVER['HTTP_REFERER'] ?? $_SERVER['SCRIPT_NAME'];
header('Location: ' . $referer);
exit;
?>