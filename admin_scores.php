<?php
// Auteur: Poppy Jackson | Dit admin panel is bewust simpel gehouden
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();

if ((int)$_SESSION['user_id'] !== 1) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';
$matches = [];

try {
    $stmt = $pdo->query("SELECT * FROM matches ORDER BY match_date ASC");
    $matches = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Wedstrijden ophalen is mislukt.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matchId = (int)($_POST['match_id'] ?? 0);
    $homeScore = $_POST['home_score'] ?? '';
    $awayScore = $_POST['away_score'] ?? '';

    if ($matchId <= 0) {
        $errors[] = 'Ongeldige wedstrijd.';
    } elseif (
        !ctype_digit((string)$homeScore) ||
        !ctype_digit((string)$awayScore)
    ) {
        $errors[] = 'Scores moeten hele getallen van 0 of hoger zijn.';
    } else {
        $homeScore = (int)$homeScore;
        $awayScore = (int)$awayScore;

        if ($homeScore > 99 || $awayScore > 99) {
            $errors[] = 'Scores mogen niet hoger zijn dan 99.';
        } else {
            try {
                $check = $pdo->prepare("SELECT id FROM matches WHERE id = ?");
                $check->execute([$matchId]);
                if (!$check->fetch()) {
                    throw new PDOException('Wedstrijd niet gevonden.');
                }

                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "UPDATE matches SET home_score = ?, away_score = ? WHERE id = ?"
                );
                $stmt->execute([$homeScore, $awayScore, $matchId]);

                $stmt = $pdo->prepare(
                    "SELECT user_id, predicted_home, predicted_away
                     FROM predictions
                     WHERE match_id = ?"
                );
                $stmt->execute([$matchId]);
                $predictions = $stmt->fetchAll();

                $updateStmt = $pdo->prepare(
                    "UPDATE predictions SET points = ? WHERE match_id = ? AND user_id = ?"
                );

                foreach ($predictions as $prediction) {
                    $points = calculatePoints(
                        $homeScore,
                        $awayScore,
                        (int)$prediction['predicted_home'],
                        (int)$prediction['predicted_away']
                    );
                    $updateStmt->execute([
                        $points,
                        $matchId,
                        (int)$prediction['user_id'],
                    ]);
                }

                $pdo->commit();
                $success = 'Uitslag opgeslagen. Punten voor ' . count($predictions) . ' voorspelling(en) bijgewerkt.';

                $stmt = $pdo->query("SELECT * FROM matches ORDER BY match_date ASC");
                $matches = $stmt->fetchAll();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Opslaan mislukt. Probeer het opnieuw.';
            }
        }
    }
}

$pageTitle = 'Admin · Uitslagen';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Beheer</div>
            <h1 class="page-title">Uitslagen invoeren</h1>
            <p class="page-desc">
                Vul de officiële score per wedstrijd in. Punten worden automatisch
                berekend voor alle voorspellingen van die wedstrijd.
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

    <?php if (empty($matches)): ?>
        <div class="empty">
            <div class="empty-icon">📅</div>
            <h2 class="empty-title">Geen wedstrijden</h2>
            <p class="empty-text">Importeer eerst de wedstrijden via database.sql.</p>
        </div>
    <?php else: ?>
        <div class="match-list">
            <?php foreach ($matches as $match):
                $mid = (int)$match['id'];
                $isPlayed = $match['home_score'] !== null && $match['away_score'] !== null;
                $date = new DateTime($match['match_date']);
            ?>
                <article class="match">
                    <div class="match-meta">
                        <span class="match-stage"><?= htmlspecialchars($match['stage']) ?></span>
                        <span><?= $date->format('d M Y · H:i') ?></span>
                        <?php if ($isPlayed): ?>
                            <span class="member-badge">Gespeeld</span>
                        <?php endif; ?>
                    </div>

                    <div class="match-row">
                        <div class="team team-home">
                            <span class="team-name"><?= htmlspecialchars($match['home_team']) ?></span>
                        </div>

                        <form method="POST" action="admin_scores.php" class="score-input-group">
                            <input type="hidden" name="match_id" value="<?= $mid ?>">
                            <input
                                type="number"
                                name="home_score"
                                class="score-input"
                                min="0"
                                max="99"
                                required
                                value="<?= $isPlayed ? (int)$match['home_score'] : '' ?>"
                                placeholder="-">
                            <span class="score-sep">:</span>
                            <input
                                type="number"
                                name="away_score"
                                class="score-input"
                                min="0"
                                max="99"
                                required
                                value="<?= $isPlayed ? (int)$match['away_score'] : '' ?>"
                                placeholder="-">
                            <button type="submit" class="btn btn-primary btn-sm">Opslaan</button>
                        </form>

                        <div class="team">
                            <span class="team-name"><?= htmlspecialchars($match['away_team']) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
