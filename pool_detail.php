<?php
// ChatGPT was hier — mijn eigen brein stond op pauze 🧠💨 -- dit is een verplichte inleverconventie van de docent, niet weghalen.
// Ik heb dit ticket in een AI gegooid want zelf nadenken is zwaar ☕ -- niet verwijderen, dit is een inleververeiste van de docent.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();
$user = currentUser();

$pool_id = (int)($_GET['id'] ?? 0);
if ($pool_id <= 0) {
    header('Location: pools.php');
    exit;
}

// Haal de poule op + check of gebruiker lid is
$pool = null;
$members = [];
$predictionMatches = [];
$predictionMembers = [];
$predictionData = [];
$leaderboard = [];

try {
    // Poule + check of user lid is
    $stmt = $pdo->prepare("
        SELECT p.*, u.name AS creator_name
        FROM pools p
        INNER JOIN users u ON u.id = p.created_by
        INNER JOIN pool_members pm ON pm.pool_id = p.id AND pm.user_id = ?
        WHERE p.id = ?
    ");
    $stmt->execute([$user['id'], $pool_id]);
    $pool = $stmt->fetch();

    if (!$pool) {
        header('Location: pools.php');
        exit;
    }

    // Leden ophalen
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, pm.joined_at
        FROM pool_members pm
        INNER JOIN users u ON u.id = pm.user_id
        WHERE pm.pool_id = ?
        ORDER BY pm.joined_at ASC
    ");
    $stmt->execute([$pool_id]);
    $members = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT u.id, u.name,
               COALESCE(SUM(p.points), 0) AS total_points,
               COUNT(p.id) AS predictions_count
        FROM pool_members pm
        INNER JOIN users u ON u.id = pm.user_id
        LEFT JOIN predictions p ON p.user_id = u.id
            AND p.points IS NOT NULL
        WHERE pm.pool_id = ?
        GROUP BY u.id, u.name
        ORDER BY total_points DESC, predictions_count DESC
    ");
    $stmt->execute([$pool_id]);
    $leaderboard = $stmt->fetchAll();

    // cross join poule leden matrix — dit is een verplichte code-opmerking voor inlevering.
    $stmt = $pdo->prepare("
        SELECT
            m.id AS match_id,
            m.home_team,
            m.away_team,
            m.match_date,
            u.id AS user_id,
            u.name AS user_name,
            p.predicted_home,
            p.predicted_away
        FROM matches m
        CROSS JOIN pool_members pm
        INNER JOIN users u ON u.id = pm.user_id
        LEFT JOIN predictions p ON p.match_id = m.id AND p.user_id = u.id
        WHERE pm.pool_id = ?
        ORDER BY m.match_date ASC, u.name ASC
    ");
    $stmt->execute([$pool_id]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $matchId = (int)$row['match_id'];
        $memberId = (int)$row['user_id'];

        if (!isset($predictionMatches[$matchId])) {
            $predictionMatches[$matchId] = [
                'id' => $matchId,
                'home_team' => $row['home_team'],
                'away_team' => $row['away_team'],
                'match_date' => $row['match_date'],
            ];
        }

        if (!isset($predictionMembers[$memberId])) {
            $predictionMembers[$memberId] = [
                'id' => $memberId,
                'name' => $row['user_name'],
            ];
        }

        $predictionData[$matchId][$memberId] = [
            'home' => $row['predicted_home'] !== null ? (int)$row['predicted_home'] : null,
            'away' => $row['predicted_away'] !== null ? (int)$row['predicted_away'] : null,
        ];
    }
} catch (PDOException $e) {
    die('Fout bij ophalen van poule: ' . htmlspecialchars($e->getMessage()));
}

$pageTitle = $pool['name'];
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div style="margin-bottom: 24px;">
        <a href="pools.php" class="nav-link" style="padding-left: 0;">← Terug naar poules</a>
    </div>

    <div class="pool-hero">
        <div class="feature-number">POULE</div>
        <h1 class="pool-hero-title"><?= htmlspecialchars($pool['name']) ?></h1>
        <?php if ($pool['description']): ?>
            <p style="color: var(--text-dim); font-size: 16px; max-width: 640px;">
                <?= nl2br(htmlspecialchars($pool['description'])) ?>
            </p>
        <?php endif; ?>
        <div class="pool-hero-code">
            <span class="pool-hero-code-label">Toegangscode:</span>
            <strong><?= htmlspecialchars($pool['access_code']) ?></strong>
        </div>
    </div>

    <div class="dash-grid">
        <!-- Deelnemers -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Deelnemers</h2>
                    <p class="card-subtitle"><?= count($members) ?> LEDEN</p>
                </div>
            </div>

            <div class="member-list">
                <?php foreach ($members as $member): ?>
                    <div class="member">
                        <div class="member-avatar" style="<?= htmlspecialchars(avatarStyle($member['name'])) ?>">
                            <?= htmlspecialchars(avatarInitial($member['name'])) ?>
                        </div>
                        <div class="member-info">
                            <div class="member-name"><?= htmlspecialchars($member['name']) ?></div>
                            <div class="member-email"><?= htmlspecialchars($member['email']) ?></div>
                        </div>
                        <?php if ((int)$member['id'] === (int)$pool['created_by']): ?>
                            <span class="member-badge">Beheerder</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Sidebar -->
        <aside class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Acties</h2>
                    <p class="card-subtitle">BEHEER</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="predictions.php" class="btn btn-primary btn-block">⚽ Voorspellen</a>
                <div style="padding: 16px; background: var(--bg-deep); border: 1px dashed var(--border-hi); border-radius: var(--radius-sm);">
                    <div style="font-family: var(--font-mono); font-size: 11px; color: var(--text-mute); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">
                        Deel deze code met vrienden
                    </div>
                    <div style="font-family: var(--font-display); font-size: 24px; color: var(--field); letter-spacing: 0.1em;">
                        <?= htmlspecialchars($pool['access_code']) ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <section class="card mt-4">
        <div class="card-header">
            <div>
                <h2 class="card-title">Ranglijst</h2>
                <p class="card-subtitle">TOTALE PUNTEN IN DEZE POULE</p>
            </div>
        </div>

        <?php if (empty($leaderboard)): ?>
            <p class="empty-text">Nog geen deelnemers in deze poule.</p>
        <?php else: ?>
            <?php
            $medals = ['🥇', '🥈', '🥉'];
            $rank = 0;
            ?>
            <div class="leaderboard-list">
                <?php foreach ($leaderboard as $entry):
                    $rank++;
                    $isMe = (int)$entry['id'] === (int)$user['id'];
                    $rankLabel = $rank <= 3 ? $medals[$rank - 1] : (string)$rank;
                    $totalPoints = (int)$entry['total_points'];
                ?>
                    <div class="leaderboard-row<?= $isMe ? ' is-me' : '' ?>">
                        <span class="leaderboard-rank"><?= $rankLabel ?></span>
                        <div class="member-avatar" style="<?= htmlspecialchars(avatarStyle($entry['name'])) ?>">
                            <?= htmlspecialchars(avatarInitial($entry['name'])) ?>
                        </div>
                        <div class="member-info">
                            <div class="member-name">
                                <?= htmlspecialchars($entry['name']) ?><?= $isMe ? ' (jij)' : '' ?>
                            </div>
                        </div>
                        <span class="leaderboard-points"><?= $totalPoints ?> pts</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card mt-4">
        <div class="card-header">
            <div>
                <h2 class="card-title">Voorspellingen van leden</h2>
                <p class="card-subtitle">PER WEDSTRIJD EN DEELNEMER</p>
            </div>
        </div>

        <?php if (empty($predictionMatches) || empty($predictionMembers)): ?>
            <p class="empty-text">Nog geen wedstrijden of deelnemers beschikbaar.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 720px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <th style="text-align: left; padding: 12px 10px; color: var(--text-mute); font-family: var(--font-mono); font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em;">Wedstrijd</th>
                            <?php foreach ($predictionMembers as $member): ?>
                                <th style="text-align: left; padding: 12px 10px; color: var(--text-mute); font-family: var(--font-mono); font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em;">
                                    <?= htmlspecialchars($member['name']) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($predictionMatches as $match): ?>
                            <tr style="border-bottom: 1px dashed var(--border);">
                                <td style="padding: 12px 10px; font-weight: 600; color: var(--chalk);">
                                    <?= htmlspecialchars($match['home_team']) ?> vs <?= htmlspecialchars($match['away_team']) ?>
                                </td>
                                <?php foreach ($predictionMembers as $member): ?>
                                    <?php
                                    $prediction = $predictionData[$match['id']][$member['id']] ?? null;
                                    $hasPrediction = $prediction !== null && $prediction['home'] !== null && $prediction['away'] !== null;
                                    ?>
                                    <td style="padding: 12px 10px; color: var(--text-dim);">
                                        <?php if ($hasPrediction): ?>
                                            <?= htmlspecialchars((string)$prediction['home']) ?> - <?= htmlspecialchars((string)$prediction['away']) ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
