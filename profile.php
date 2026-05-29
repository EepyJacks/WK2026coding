<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$user = currentUser();

$errors = [];
$success = '';
$nameInput = $user['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_name') {
    $nameInput = trim((string)($_POST['name'] ?? ''));
    $nameLength = function_exists('mb_strlen') ? mb_strlen($nameInput) : strlen($nameInput);

    if ($nameLength < 2) {
        $errors[] = 'Naam moet minimaal 2 tekens lang zijn.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$nameInput, $user['id']]);

            $_SESSION['user_name'] = $nameInput;
            $user['name'] = $nameInput;
            $success = 'Je naam is succesvol bijgewerkt.';
        } catch (PDOException $e) {
            $errors[] = 'Naam opslaan is mislukt. Probeer het opnieuw.';
        }
    }
}

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

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error">
            ⚠ <?= htmlspecialchars($error) ?>
        </div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            ✓ <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <section class="card mb-4">
        <div class="card-header">
            <div>
                <h2 class="card-title"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="card-subtitle"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <form method="POST" action="profile.php">
            <input type="hidden" name="action" value="update_name">
            <div class="form-group">
                <label for="name" class="form-label">Weergavenaam</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-input"
                    minlength="2"
                    required
                    value="<?= htmlspecialchars($nameInput) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Naam opslaan</button>
        </form>
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
