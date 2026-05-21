<?php
// ============================================
// Uitloggen
// ============================================
// Dit bestand is al compleet; studenten hoeven hier niets aan te doen.

session_start();

// Vernietig alle sessiedata
$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
