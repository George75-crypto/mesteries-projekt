<?php
require __DIR__ . '/config.php';

// Csak bejelentkezve érhető el
if (!is_logged_in()) {
    redirect('login.php');
}

$stmt = db()->prepare('SELECT id, username, email, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // A fiókot időközben törölték az adatbázisból
    logout_user();
    redirect('login.php');
}

$badgeId    = str_pad((string) $user['id'], 4, '0', STR_PAD_LEFT);
$registered = (new DateTime($user['created_at']))->format('Y.m.d.');
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fiókom – <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="shell">

  <div class="intro">
    <p class="brand"><?= e(APP_NAME) ?></p>
    <h1>Belépve</h1>
    <p class="lead">
      Szia, <?= e($user['username']) ?>! Ezt az oldalt csak bejelentkezés után látod.
      A kártyán az adatbázisban tárolt adataid szerepelnek.
    </p>
  </div>

  <div class="panel">
    <article class="badge" aria-label="Fiókadatok">
      <div class="badge__top"><span class="badge__slot"></span></div>

      <div class="badge__body">
        <p class="badge__name"><?= e($user['username']) ?></p>
        <p class="badge__mail"><?= e($user['email']) ?></p>
      </div>

      <dl class="badge__meta">
        <div>
          <dt>Azonosító</dt>
          <dd><?= e($badgeId) ?></dd>
        </div>
        <div>
          <dt>Regisztrált</dt>
          <dd><?= e($registered) ?></dd>
        </div>
      </dl>
    </article>

    <form method="post" action="logout.php" class="logout">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button type="submit" class="btn btn--quiet">Kijelentkezés</button>
    </form>
  </div>

</main>
</body>
</html>
