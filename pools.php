<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$user = currentUser();

// Haal alle poules op waar deze gebruiker lid van is
$pools = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*,
               (SELECT COUNT(*) FROM pool_members WHERE pool_id = p.id) AS member_count,
               u.name AS creator_name
        FROM pools p
        INNER JOIN pool_members pm ON pm.pool_id = p.id
        INNER JOIN users u ON u.id = p.created_by
        WHERE pm.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $pools = $stmt->fetchAll();
} catch (PDOException $e) {
    // Stil: als tabel nog leeg is blijft $pools leeg
}

$pageTitle = 'Poules';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Jouw poules</div>
            <h1 class="page-title">Mijn Poules</h1>
            <p class="page-desc">Overzicht van alle poules waar je aan deelneemt. Maak een nieuwe poule aan of sluit aan met een toegangscode.</p>
        </div>
        <div class="flex gap-2">
            <a href="join_pool.php"   class="btn btn-ghost">+ Join met code</a>
            <a href="create_pool.php" class="btn btn-primary">+ Nieuwe poule</a>
        </div>
    </div>

    <?php if (empty($pools)): ?>
        <div class="empty">
            <div class="empty-icon">🏟️</div>
            <h2 class="empty-title">Nog geen poules</h2>
            <p class="empty-text">Start je eigen poule of sluit je aan bij een bestaande poule via een toegangscode.</p>
            <div class="hero-actions">
                <a href="create_pool.php" class="btn btn-primary">Poule aanmaken</a>
                <a href="join_pool.php"   class="btn btn-ghost">Poule joinen</a>
            </div>
        </div>
    <?php else: ?>
        <div class="pool-grid">
            <?php foreach ($pools as $pool): ?>
                <a href="pool_detail.php?id=<?= (int)$pool['id'] ?>" class="pool-card">
                    <div class="pool-card-header">
                        <div>
                            <div class="feature-number"><?= (int)$pool['member_count'] ?> DEELNEMERS</div>
                            <h3 class="pool-name"><?= htmlspecialchars($pool['name']) ?></h3>
                        </div>
                        <?php if ((int)$pool['created_by'] === (int)$user['id']): ?>
                            <span class="member-badge">Beheerder</span>
                        <?php endif; ?>
                    </div>
                    <p class="pool-desc">
                        <?= $pool['description']
                            ? htmlspecialchars($pool['description'])
                            : '<em style="color:var(--text-mute)">Geen beschrijving</em>' ?>
                    </p>
                    <div class="pool-meta">
                        <span>Door <?= htmlspecialchars($pool['creator_name']) ?></span>
                        <span class="pool-code">🔑 <?= htmlspecialchars($pool['access_code']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
