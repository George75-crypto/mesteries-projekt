<?php
declare(strict_types=1);

/*
 * Közös beállítások – minden oldal ezt tölti be először.
 * Ha a MySQL-ednek van jelszava (vagy más a felhasználó), itt írd át.
 */

const DB_HOST = 'localhost';
const DB_NAME = 'login_projekt';
const DB_USER = 'root';   // XAMPP alapértelmezés
const DB_PASS = '';       // XAMPP alapértelmezés: nincs jelszó

const APP_NAME = 'IKTP';  // az oldalakon megjelenő név

// ---------- Munkamenet (session) ----------
session_name('iktp_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ---------- Adatbázis-kapcsolat (PDO) ----------
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Adatbázis-kapcsolódási hiba: ' . $e->getMessage());
            http_response_code(500);
            exit('Nem sikerült csatlakozni az adatbázishoz. Fut a MySQL? Importáltad a database.sql fájlt? Stimmelnek a beállítások a config.php-ban?');
        }
    }

    return $pdo;
}

// ---------- Segédfüggvények ----------

/** Szöveg biztonságos kiírása HTML-be (XSS ellen). */
function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/** Egy POST-mező értéke szövegként (tömböt vagy hiányzó mezőt üres szövegre cserél). */
function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** CSRF-védelem: minden űrlap kap egy titkos, a munkamenethez kötött tokent. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool
{
    return isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], post('csrf'));
}

/** Kijelentkeztetés: a munkamenet és a süti törlése. */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
