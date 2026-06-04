<?php
// ============================================
// Puntentelling WK Poule
// ============================================
//
// | Regel                                      | Punten |
// |--------------------------------------------|--------|
// | Exacte uitslag geraden                     | 3      |
// | Goede winnaar + juist doelsaldo            | 2      |
// | Alleen goede winnaar / gelijkspel          | 1      |
// | Geen van bovenstaande                      | 0      |
//
// Doelsaldo = thuisscore − uitscore (bijv. 3–1 → +2, 1–1 → 0).
// Gelijkspel telt als juiste winnaar wanneer beide voorspellingen gelijkspel zijn.

/**
 * Bereken punten voor een voorspelling.
 *
 * @param int $realHome Echte thuisscore
 * @param int $realAway Echte uitscore
 * @param int $predHome Voorspelde thuisscore
 * @param int $predAway Voorspelde uitscore
 * @return int 0, 1, 2 of 3 punten
 */
function calculatePoints(
    int $realHome,
    int $realAway,
    int $predHome,
    int $predAway
): int {
    if ($realHome === $predHome && $realAway === $predAway) {
        return 3;
    }

    $realDiff = $realHome - $realAway;
    $predDiff = $predHome - $predAway;

    $winnerCorrect = ($realDiff > 0 && $predDiff > 0)
        || ($realDiff < 0 && $predDiff < 0)
        || ($realDiff === 0 && $predDiff === 0);

    if (!$winnerCorrect) {
        return 0;
    }

    if ($realDiff === $predDiff) {
        return 2;
    }

    return 1;
}
