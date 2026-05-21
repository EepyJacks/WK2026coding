<?php
// ============================================
// Authenticatie helper functies
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Controleer of de gebruiker is ingelogd.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Stuur de gebruiker door naar de login pagina als deze niet is ingelogd.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Haal de huidige ingelogde gebruiker op (id, name, email).
 */
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}
