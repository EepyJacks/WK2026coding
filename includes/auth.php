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

/**
 * Bepaal een vaste avatar-achtergrondkleur op basis van naam.
 */
function avatarColor(string $name): string {
    $colors = [
        '#e74c3c',
        '#3498db',
        '#2ecc71',
        '#9b59b6',
        '#e67e22',
        '#1abc9c',
        '#e91e63',
        '#ff5722',
    ];

    $normalized = trim($name);
    if ($normalized === '') {
        return $colors[0];
    }

    $hash = sprintf('%u', crc32($normalized));
    $index = (int)($hash % count($colors));
    return $colors[$index];
}

/**
 * Kies wit of donker tekst op basis van achtergrondkleur.
 */
function avatarTextColor(string $hexColor): string {
    $hex = ltrim($hexColor, '#');
    if (strlen($hex) !== 6) {
        return '#ffffff';
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $luminance = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
    return $luminance > 186 ? '#0a0e1a' : '#ffffff';
}

/**
 * Inline style voor avatar met leesbaar contrast.
 */
function avatarStyle(string $name): string {
    $background = avatarColor($name);
    $foreground = avatarTextColor($background);
    return "background: {$background}; color: {$foreground};";
}

/**
 * Eerste letter voor avatar.
 */
function avatarInitial(string $name): string {
    $normalized = trim($name);
    if ($normalized === '') {
        return '?';
    }

    if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
        return mb_strtoupper(mb_substr($normalized, 0, 1));
    }

    return strtoupper(substr($normalized, 0, 1));
}
