<?php
require __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors   = [];
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Az űrlap lejárt. Töltsd újra az oldalt, és próbáld meg még egyszer.';
    } else {
        $username = trim(post('username'));
        $email    = mb_strtolower(trim(post('email')));
        $password = post('password');
        $confirm  = post('password_confirm');

        // 1) Ellenőrzés
        if (!preg_match('/^[\p{L}\p{N}_]{3,30}$/u', $username)) {
            $errors[] = 'A felhasználónév 3–30 karakter hosszú lehet, és csak betűt, számot vagy aláhúzást (_) tartalmazhat.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 191) {
            $errors[] = 'Adj meg érvényes e-mail-címet, például nev@pelda.hu.';
        }
        if (mb_strlen($password) < 8) {
            $errors[] = 'A jelszó legalább 8 karakter legyen.';
        } elseif (mb_strlen($password) > 100) {
            $errors[] = 'A jelszó legfeljebb 100 karakter lehet.';
        } elseif ($password !== $confirm) {
            $errors[] = 'A két jelszó nem egyezik. Írd be mindkettőt ugyanúgy.';
        }

        // 2) Mentés az adatbázisba
        if (!$errors) {
            try {
                $stmt = db()->prepare(
                    'INSERT INTO users (username, email, password) VALUES (?, ?, ?)'
                );
                // A jelszót sima szövegként mentjük az adatbázisba (nincs hash-elés)
                $stmt->execute([$username, $email, $password]);
            } catch (PDOException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                    // 1062 = duplikált érték: a felhasználónév vagy az e-mail már szerepel a táblában
                    if (str_contains($e->getMessage(), 'uq_users_email')) {
                        $errors[] = 'Ezzel az e-mail-címmel már van fiók. Jelentkezz be, vagy adj meg másik címet.';
                    } elseif (str_contains($e->getMessage(), 'uq_users_username')) {
                        $errors[] = 'Ez a felhasználónév már foglalt. Válassz másikat.';
                    } else {
                        $errors[] = 'A felhasználónév vagy az e-mail-cím már foglalt.';
                    }
                } else {
                    error_log('Regisztrációs hiba: ' . $e->getMessage());
                    $errors[] = 'Az adatbázis most nem válaszol, a fiók nem készült el. Próbáld újra később.';
                }
            }

            if (!$errors) {
                $_SESSION['flash'] = 'A fiókod elkészült. Most már bejelentkezhetsz.';
                redirect('login.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Regisztráció – <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">

  <div class="intro">
    <p class="brand"><?= e(APP_NAME) ?></p>
    <h1>Új fiók</h1>
    <p class="lead">Pár adat, és kész a fiókod.</p>
  </div>

  <div class="panel">
    <nav class="switch" aria-label="Bejelentkezés vagy regisztráció">
      <a href="login.php">Bejelentkezés</a>
      <a href="register.php" aria-current="page">Regisztráció</a>
    </nav>

    <?php if ($errors): ?>
      <div class="alert alert--error" role="alert">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="register.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="username">Felhasználónév</label>
      <input id="username" name="username" type="text" value="<?= e($username) ?>"
             autocomplete="username" required minlength="3" maxlength="30"
             aria-describedby="username-hint">
      <p class="hint" id="username-hint">3–30 karakter: betű, szám vagy aláhúzás.</p>

      <label for="email">E-mail-cím</label>
      <input id="email" name="email" type="email" value="<?= e($email) ?>"
             autocomplete="email" required maxlength="191">

      <label for="password">Jelszó</label>
      <input id="password" name="password" type="password"
             autocomplete="new-password" required minlength="8" maxlength="100"
             aria-describedby="password-hint">
      <p class="hint" id="password-hint">Legalább 8 karakter.</p>

      <label for="password_confirm">Jelszó még egyszer</label>
      <input id="password_confirm" name="password_confirm" type="password"
             autocomplete="new-password" required minlength="8" maxlength="100">

      <button type="submit" class="btn">Fiók létrehozása</button>
    </form>
  </div>

</main>
</body>
</html>
