<?php
session_start();

// csak bejelentkezve érhető el
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKTP chat</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<!-- ITT VAN A BEJELENTKEZÉS UTÁNI RÉSZ, ezt lehet majd módosítani -->
<div class="chat">

    <!-- bal oldal: a fiókod -->
    <aside class="sidebar">
        <p class="brand">IKTP</p>

        <div class="badge">
            <div class="badge__top"></div>
            <div class="badge__body">
                <p class="badge__name"><?= htmlspecialchars($user['nev']) ?></p>
                <p class="badge__mail"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div class="badge__meta">
                <div>
                    <small>Azonosító</small>
                    <b><?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></b>
                </div>
                <div>
                    <small>Regisztrált</small>
                    <b><?= date('Y.m.d.', strtotime($user['datum'])) ?></b>
                </div>
            </div>
        </div>

        <a class="btn btn--quiet" href="service.php?command=logout">Kijelentkezés</a>
    </aside>

    <!-- jobb oldal: a beszélgetés -->
    <main class="chat__main">
        <div class="chat__fej">
            <h2># általános</h2>
            <p>Itt beszélget mindenki, aki regisztrált.</p>
        </div>

        <div class="chat__uzenetek" id="uzenetek"></div>

        <form class="kuldes" id="kuldes">
            <input id="szoveg" placeholder="Írj egy üzenetet..." maxlength="500" autocomplete="off" autofocus>
            <button class="btn" type="submit">Küldés</button>
        </form>
    </main>

</div>
<!-- ITT VÉGE A BEJELENTKEZÉS UTÁNI RÉSZNEK -->

<script>
    var lista = document.getElementById('uzenetek');
    var urlap = document.getElementById('kuldes');
    var mezo = document.getElementById('szoveg');
    var regi = '';

    // lekéri az üzeneteket a service.php-tól, és kiírja őket
    async function frissit() {
        var valasz = await fetch('service.php?command=messages');
        if (!valasz.ok) { return; }
        var html = await valasz.text();

        if (html !== regi) {
            // csak akkor görget le, ha eddig is az alján volt
            var alul = lista.scrollTop + lista.clientHeight >= lista.scrollHeight - 50;
            lista.innerHTML = html;
            regi = html;
            if (alul) { lista.scrollTop = lista.scrollHeight; }
        }
    }

    // üzenet küldése
    urlap.addEventListener('submit', async function (e) {
        e.preventDefault();
        var szoveg = mezo.value.trim();
        if (szoveg == '') { return; }
        mezo.value = '';

        await fetch('service.php?command=send', {
            method: 'POST',
            body: new URLSearchParams({ szoveg: szoveg })
        });
        await frissit();
        lista.scrollTop = lista.scrollHeight;
    });

    frissit();
    setInterval(frissit, 1500);   // 1,5 másodpercenként újra lekéri
</script>
</body>
</html>
