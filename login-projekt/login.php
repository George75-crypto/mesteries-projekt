<?php
require __DIR__ . '/config.php';

if (is_logged_in()) {
    redirect('index.php');
}

// Egyszeri üzenet (például sikeres regisztráció után)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$errors = [];
$login  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Az űrlap lejárt. Töltsd újra az oldalt, és próbáld meg még egyszer.';
    } else {
        $login    = trim(post('login'));
        $password = post('password');

        // Felhasználónév VAGY e-mail-cím alapján keresünk
        $stmt = db()->prepare(
            'SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1'
        );
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        // A jelszót PHP-ban hasonlítjuk össze a tárolt szöveggel (kis- és nagybetű számít)
        if ($user && $user['password'] === $password) {
            session_regenerate_id(true); // munkamenet-rögzítés elleni védelem
            $_SESSION['user_id'] = (int) $user['id'];
            redirect('index.php');
        }

        $errors[] = 'Hibás felhasználónév, e-mail-cím vagy jelszó. Ellenőrizd az adatokat, és próbáld újra.';
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bejelentkezés – <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">

  <div class="intro">
    <p class="brand"><?= e(APP_NAME) ?></p>
    <h1>Belépés</h1>
    <p class="lead">Jelentkezz be a felhasználóneveddel vagy az e-mail-címeddel.</p>
  </div>

  <div class="panel">
    <nav class="switch" aria-label="Bejelentkezés vagy regisztráció">
      <a href="login.php" aria-current="page">Bejelentkezés</a>
      <a href="register.php">Regisztráció</a>
    </nav>

    <?php if ($flash): ?>
      <p class="alert alert--ok" role="status"><?= e($flash) ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert alert--error" role="alert">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label for="login">Felhasználónév vagy e-mail-cím</label>
      <input id="login" name="login" type="text" value="<?= e($login) ?>"
             autocomplete="username" required maxlength="191">

      <label for="password">Jelszó</label>
      <input id="password" name="password" type="password"
             autocomplete="current-password" required maxlength="100">

      <button type="submit" class="btn">Bejelentkezés</button>
    </form>
  </div>

</main>
</body>
</html>
