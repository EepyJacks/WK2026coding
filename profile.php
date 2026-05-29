<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$user = currentUser();

$stats = [
    'pools' => 0,
    'predictions' => 0,
    'matches' => 0,
];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pool_members WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $stats['pools'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM predictions WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $stats['predictions'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches");
    $stmt->execute();
    $stats['matches'] = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    // Stil falen: toon standaard 0-waardes als tabellen nog niet klaar zijn.
}

$pageTitle = 'Profiel';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Account</div>
            <h1 class="page-title">Mijn profiel</h1>
            <p class="page-desc">
                Bekijk je accountgegevens en je huidige WK Poule statistieken.
            </p>
        </div>
    </div>

    <section class="card mb-4">
        <div class="card-header">
            <div>
                <h2 class="card-title"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="card-subtitle"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
    </section>

    <div class="stat-row">
        <div class="stat stat-accent">
            <div class="stat-value"><?= $stats['pools'] ?></div>
            <div class="stat-label">Poules</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?= $stats['predictions'] ?></div>
            <div class="stat-label">Voorspellingen</div>
        </div>
        <div class="stat">
            <div class="stat-value"><?= $stats['matches'] ?></div>
            <div class="stat-label">Wedstrijden</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
