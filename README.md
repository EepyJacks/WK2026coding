# WK Poule Applicatie — Studentenopdracht ⚽

Welkom bij de WK Poule opdracht! De hele **frontend (HTML + CSS)** en **PHP-structuur** zijn voor jou klaargezet. Jouw taak: de PHP-functies afmaken zodat de applicatie werkt.

In totaal zijn er **16 TODO-blokken** verdeeld over **5 bestanden**. Elk TODO-blok bevat uitleg en voorbeeldcode als hint.

---

## 🔧 Vereisten

- **Webserver met PHP 8.0 of hoger** (XAMPP, WAMP, MAMP, Laragon)
- **MySQL of MariaDB**
- Een code-editor (VS Code, PhpStorm, etc.)

Check je PHP-versie met: `php -v` in de terminal, of open in je browser `http://localhost/dashboard/phpinfo.php` (XAMPP).

---

## 📦 Installatie (stap voor stap)

### Stap 1: Bestanden op de juiste plek
Plaats de volledige `wk-poule` map in je webserver directory:

- **XAMPP Windows:** `C:\xampp\htdocs\wk-poule\`
- **XAMPP Mac:** `/Applications/XAMPP/htdocs/wk-poule/`
- **Laragon:** `C:\laragon\www\wk-poule\`

> ⚠️ Let op: `index.php` moet direct in `htdocs/wk-poule/` staan, niet in een submap zoals `htdocs/wk-poule/wk-poule/`.

### Stap 2: Server starten
Start Apache en MySQL via het control panel van je webserver.

### Stap 3: Database aanmaken
Open **phpMyAdmin** via `http://localhost/phpmyadmin` en importeer het bestand `database.sql`:

1. Klik op het tabblad **Importeren** (Import)
2. Kies het bestand `database.sql`
3. Klik op **Uitvoeren** onderaan

Het SQL-script maakt automatisch:
- De database `wk_poule`
- 5 tabellen: `users`, `pools`, `pool_members`, `matches`, `predictions`
- 8 testwedstrijden

### Stap 4: Database-verbinding controleren
Open `includes/db.php` en controleer dat de inloggegevens kloppen. Voor XAMPP zijn de standaardwaarden al correct:
```php
$db_user = 'root';
$db_pass = '';    // leeg bij XAMPP
```

### Stap 5: Open de applicatie
Ga in je browser naar **`http://localhost/wk-poule/`**

Je zou nu de homepage moeten zien. Als je een **403 Forbidden** foutmelding krijgt: check of `index.php` direct in de `wk-poule` map staat (niet in een submap).

---

## 📊 Hoe de database in elkaar zit

| Tabel | Wat staat erin | Belangrijke kolommen |
|---|---|---|
| `users` | Geregistreerde gebruikers | `id`, `name`, `email`, `password` |
| `pools` | Aangemaakte poules | `id`, `name`, `access_code`, `created_by` |
| `pool_members` | Wie zit in welke poule | `pool_id`, `user_id` |
| `matches` | Wedstrijden (al gevuld) | `id`, `home_team`, `away_team`, `match_date` |
| `predictions` | Voorspellingen van gebruikers | `user_id`, `match_id`, `predicted_home`, `predicted_away` |

**Slimme zaken in de database (die je moet benutten):**
- `users.email` heeft een `UNIQUE` constraint — duplicaten worden automatisch geweigerd
- `pools.access_code` is `UNIQUE` — elke code kan maar 1 keer bestaan
- `pool_members` heeft een `UNIQUE KEY (pool_id, user_id)` — een gebruiker kan maar 1x lid zijn
- `predictions` heeft een `UNIQUE KEY (user_id, match_id)` — maakt `INSERT ... ON DUPLICATE KEY UPDATE` mogelijk!

---

## ✏️ De opdracht: 5 bestanden, 16 TODO's

**Aanbevolen volgorde** (de opdrachten hangen van elkaar af!):

### 1️⃣ Registreren (`register.php`) — 3 TODO's
Begin hier. Zonder accounts kun je niks testen.
- **TODO 1:** Valideer de invoer (naam, email, wachtwoord, bevestiging).
- **TODO 2:** Controleer of het e-mailadres nog niet bestaat.
- **TODO 3:** Hash het wachtwoord met `password_hash()` en sla de gebruiker op.

**Testen:** maak een account aan. Check in phpMyAdmin of de gebruiker in `users` staat. Het wachtwoord hoort eruit te zien als `$2y$10$...`, NIET als leesbare tekst.

### 2️⃣ Inloggen (`login.php`) — 3 TODO's
Pas als dit werkt, kun je alle andere pagina's testen.
- **TODO 1:** Valideer dat de velden niet leeg zijn.
- **TODO 2:** Haal de gebruiker op en verifieer het wachtwoord met `password_verify()`.
- **TODO 3:** Zet `user_id`, `user_name`, `user_email` in de sessie.

**Testen:** log in met het account dat je net gemaakt hebt. Als het werkt, zie je het dashboard met je naam.

### 3️⃣ Poule aanmaken (`create_pool.php`) — 3 TODO's
- **TODO 1:** Valideer de poule-naam (3-100 tekens).
- **TODO 2:** Genereer een unieke toegangscode van 8 karakters.
- **TODO 3:** Sla de poule op EN voeg de maker toe als lid.

**Nieuwe concepten in deze opdracht:**
- `random_bytes()` en `bin2hex()` om veilige willekeurige codes te maken.
- Een **do-while lus** om te controleren of de code al bestaat.
- **Database transactions** (`beginTransaction`, `commit`, `rollBack`) om meerdere queries atomair uit te voeren: óf alles lukt, óf alles wordt teruggedraaid.
- `lastInsertId()` om het ID van een zojuist ingevoegde rij op te halen.

In het bestand zelf staat bovenaan een uitgebreid "WAT MOET DIT BESTAND DOEN?" blok en bij elke TODO concrete voorbeeldcode met uitleg.

**Testen:** maak een poule aan. Je zou automatisch op de detailpagina van de poule moeten belanden. In de database (phpMyAdmin) staat er 1 rij in `pools` én 1 rij in `pool_members`.

### 4️⃣ Poule joinen (`join_pool.php`) — 4 TODO's
- **TODO 1:** Controleer dat de code niet leeg is.
- **TODO 2:** Zoek de poule op basis van de code.
- **TODO 3:** Check of de gebruiker al lid is (idempotent gedrag).
- **TODO 4:** Voeg de gebruiker toe aan `pool_members`.

**Nieuwe concepten in deze opdracht:**
- Data uit de database ophalen en gebruiken over meerdere TODO's heen (tip: declareer `$pool = null;` bovenaan zodat je hem in TODO 3 en 4 kunt gebruiken).
- **Idempotentie**: dezelfde actie meerdere keren uitvoeren moet hetzelfde resultaat opleveren (niet 2x lid worden, geen foutmelding krijgen als je al lid bent).
- Hoofdletter-ongevoelig matchen door alles met `strtoupper()` te uppercasen voor vergelijking.

**Testen:** registreer een tweede account (in incognito of een andere browser). Join de poule met de code uit opdracht 3. Probeer daarna NOG een keer met dezelfde code te joinen — je moet dan gewoon naar de poule worden gestuurd zonder foutmelding, en er mag geen dubbele rij in `pool_members` staan.

### 5️⃣ Voorspellingen (`predictions.php`) — 3 TODO's
De pittigste opdracht vanwege de SQL.
- **TODO 1:** Haal alle wedstrijden op.
- **TODO 2:** Haal bestaande voorspellingen van deze gebruiker op.
- **TODO 3:** Sla voorspellingen op met `INSERT ... ON DUPLICATE KEY UPDATE`.

**Testen:** vul wat scores in en klik op opslaan. Ververs de pagina — je voorspellingen moeten blijven staan. Pas ze aan en sla opnieuw op — ze moeten worden bijgewerkt (géén duplicaten in de database).

---

## 📁 Projectstructuur

```
wk-poule/
├── index.php              # Homepage / Dashboard
├── register.php           # 📝 TODO's: registreren
├── login.php              # 📝 TODO's: inloggen
├── logout.php             # ✓ al compleet
├── pools.php              # ✓ al compleet
├── create_pool.php        # 📝 TODO's: poule aanmaken
├── join_pool.php          # 📝 TODO's: poule joinen
├── pool_detail.php        # ✓ al compleet
├── predictions.php        # 📝 TODO's: voorspellingen
├── database.sql           # Database structuur + testdata
├── css/
│   └── style.css          # Alle styling (niet aanpassen)
├── includes/
│   ├── db.php             # Database connectie (PDO) ✓
│   ├── auth.php           # Sessie-helpers ✓
│   ├── header.php         # Gedeelde header ✓
│   └── footer.php         # Gedeelde footer ✓
└── README.md              # Dit bestand
```

---

## 💡 Tips & veelgemaakte fouten

### Gebruik prepared statements (ALTIJD!)
```php
// ❌ FOUT — kwetsbaar voor SQL-injectie
$pdo->query("SELECT * FROM users WHERE email = '$email'");

// ✅ GOED
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### Hash wachtwoorden NOOIT zelf
```php
// ❌ FOUT
$password = md5($password);

// ✅ GOED
$hashed = password_hash($password, PASSWORD_DEFAULT);
```

### Voorkom XSS bij het tonen van user input
```php
// ❌ FOUT
echo $user['name'];

// ✅ GOED
echo htmlspecialchars($user['name']);
```

### Login-foutmeldingen: geef geen info weg
```php
// ❌ FOUT — onthult dat de email bestaat
if (!$user) $errors[] = 'Deze gebruiker bestaat niet';
if (!password_verify(...)) $errors[] = 'Verkeerd wachtwoord';

// ✅ GOED
$errors[] = 'Ongeldige inloggegevens.';
```

---

## 🐛 Debuggen

### Witte pagina / "Internal Server Error"
Er zit een PHP-fout in je code. Zet bovenaan je PHP-bestand (of beter: in `php.ini`):
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### "Database connectie mislukt"
- Draait MySQL?
- Heb je de `database.sql` geïmporteerd?
- Kloppen de credentials in `includes/db.php`?

### Formulier werkt niet / doet niks
- Controleer dat je formulier `method="POST"` heeft
- Controleer dat je code in het juiste `if ($_SERVER['REQUEST_METHOD'] === 'POST')` blok zit
- Voeg tijdelijk `var_dump($_POST); exit;` toe om te zien wat er binnenkomt

### Je blijft ingelogd ondanks uitlog-poging
- Clear je browser cookies voor `localhost`
- Of gebruik een incognito-venster voor tests

### Sessie werkt niet
- Staat `session_start()` bovenaan? (In deze code gebeurt dat al in `includes/auth.php`)
- Mogen PHP-sessies worden opgeslagen? Check of `/tmp` of de sessie-map schrijfbaar is

---

## ✅ Zelfcheck: werkt alles?

Loop deze checklist af als je denkt klaar te zijn:

- [ ] Ik kan registreren met een nieuw account
- [ ] Registreren met hetzelfde e-mail geeft een foutmelding
- [ ] In de database staat mijn wachtwoord als hash (niet leesbaar)
- [ ] Ik kan inloggen met mijn account
- [ ] Een verkeerd wachtwoord geeft de foutmelding "Ongeldige inloggegevens"
- [ ] Na inloggen zie ik mijn naam in de header
- [ ] Ik kan een poule aanmaken en word direct lid
- [ ] De poule heeft een toegangscode van 8 tekens
- [ ] Met een tweede account kan ik joinen via de code
- [ ] Twee keer dezelfde poule joinen geeft geen duplicaat in `pool_members`
- [ ] Ik kan voorspellingen invullen en opslaan
- [ ] Na pagina-refresh zijn mijn voorspellingen nog ingevuld
- [ ] Als ik een voorspelling wijzig, wordt deze overschreven (geen duplicaat)

---

Veel succes! Bij vragen: eerst zelf zoeken, dan medestudent, dan docent. 💪⚽
