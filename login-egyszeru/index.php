<?php
session_start();

// ha már be van jelentkezve, megy a beszélgetős oldalra
if (isset($_SESSION['user'])) {
    header("Location: chat.php");
    exit;
}

// melyik űrlap látszik: belepes vagy regisztracio
$oldal = $_GET['oldal'] ?? 'belepes';

// a bal oldali nagy cím és leírás
if ($oldal == 'regisztracio') {
    $cim = 'Új fiók';
    $leiras = 'Pár adat, és kész a fiókod.';
} else {
    $cim = 'Belépés';
    $leiras = 'Jelentkezz be a felhasználóneveddel vagy az e-mail-címeddel.';
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKTP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<main class="shell">

    <div class="intro">
        <p class="brand">IKTP</p>
        <h1><?= $cim ?></h1>
        <p class="lead"><?= $leiras ?></p>
    </div>

    <div class="panel">

        <?php if (isset($_SESSION['hiba'])): ?>
            <p class="alert alert--error"><?= $_SESSION['hiba'] ?></p>
            <?php unset($_SESSION['hiba']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['siker'])): ?>
            <p class="alert alert--ok"><?= $_SESSION['siker'] ?></p>
            <?php unset($_SESSION['siker']); ?>
        <?php endif; ?>

        <div class="switch">
            <a href="index.php" <?= $oldal == 'belepes' ? 'class="aktiv"' : '' ?>>Bejelentkezés</a>
            <a href="index.php?oldal=regisztracio" <?= $oldal == 'regisztracio' ? 'class="aktiv"' : '' ?>>Regisztráció</a>
        </div>

        <?php if ($oldal == 'regisztracio'): ?>

            <form action="service.php?command=register" method="POST">
                <label>Felhasználónév</label>
                <input name="nev" maxlength="30" required>
                <label>E-mail-cím</label>
                <input name="email" type="email" required>
                <label>Jelszó</label>
                <input name="jelszo" type="password" required>
                <button class="btn" type="submit">Fiók létrehozása</button>
            </form>

        <?php else: ?>

            <form action="service.php?command=login" method="POST">
                <label>Felhasználónév vagy e-mail-cím</label>
                <input name="nev" required>
                <label>Jelszó</label>
                <input name="jelszo" type="password" required>
                <button class="btn" type="submit">Bejelentkezés</button>
            </form>

        <?php endif; ?>

    </div>
</main>
</body>
</html>
