<?php
session_start();
include 'connect.php';

$command = $_GET['command'] ?? '';

if ($command == 'register') {
    $nev = trim($_POST['nev']);
    $email = trim($_POST['email']);
    $jelszo = $_POST['jelszo'];

    // van már ilyen név vagy e-mail?
    $stmt = $db->prepare("SELECT id FROM users WHERE nev = ? OR email = ?");
    $stmt->bind_param("ss", $nev, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['hiba'] = 'Ez a név vagy e-mail már foglalt!';
        header("Location: index.php?oldal=regisztracio");
    } else {
        $stmt = $db->prepare("INSERT INTO users (nev, email, jelszo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nev, $email, $jelszo);
        $stmt->execute();
        $_SESSION['siker'] = 'A fiókod elkészült, most már bejelentkezhetsz.';
        header("Location: index.php");
    }

} elseif ($command == 'login') {
    $nev = trim($_POST['nev']);
    $jelszo = $_POST['jelszo'];

    // névvel vagy e-maillel is be lehet lépni
    $stmt = $db->prepare("SELECT id, nev, email, jelszo, created_at FROM users WHERE nev = ? OR email = ?");
    $stmt->bind_param("ss", $nev, $nev);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $dbnev, $email, $dbjelszo, $datum);

    if ($stmt->fetch() and $dbjelszo === $jelszo) {
        // ezeket az adatokat használja az index.php belépés után
        $_SESSION['user'] = [
            'id' => $id,
            'nev' => $dbnev,
            'email' => $email,
            'datum' => $datum
        ];

        // a belépés elmentése az adatbázisba (ki, mikor)
        $stmt = $db->prepare("INSERT INTO bejelentkezesek (user_id) VALUES (?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: chat.php");
    } else {
        $_SESSION['hiba'] = 'Hibás név vagy jelszó!';
        header("Location: index.php");
    }

} elseif ($command == 'logout') {
    unset($_SESSION['user']);
    header("Location: index.php");

} elseif ($command == 'send') {
    // üzenet elküldése (csak bejelentkezve)
    if (isset($_SESSION['user'])) {
        $userid = $_SESSION['user']['id'];
        $szoveg = trim($_POST['szoveg']);

        if ($szoveg != '' and strlen($szoveg) <= 2000) {
            $stmt = $db->prepare("INSERT INTO uzenetek (user_id, szoveg) VALUES (?, ?)");
            $stmt->bind_param("is", $userid, $szoveg);
            $stmt->execute();
        }
    }

} elseif ($command == 'messages') {
    // az utolsó 50 üzenet HTML-ben, ezt kéri le a chat.php pár másodpercenként
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        exit;
    }
    $userid = $_SESSION['user']['id'];

    $result = $db->query("SELECT * FROM (
                              SELECT m.id, m.user_id, u.nev, m.szoveg, m.ido
                              FROM uzenetek m JOIN users u ON u.id = m.user_id
                              ORDER BY m.id DESC LIMIT 50
                          ) utolso ORDER BY id");

    if ($result->num_rows == 0) {
        echo "<p class='ures'>Még nincs üzenet. Írj elsőként!</p>";
    }

    while ($sor = $result->fetch_assoc()) {
        $osztaly = ($sor['user_id'] == $userid) ? 'msg msg--sajat' : 'msg';
        echo "<div class='" . $osztaly . "'>";
        echo "<div class='msg__fej'><b>" . htmlspecialchars($sor['nev']) . "</b>";
        echo "<small>" . date('H:i', strtotime($sor['ido'])) . "</small></div>";
        echo "<div class='msg__szoveg'>" . htmlspecialchars($sor['szoveg']) . "</div>";
        echo "</div>";
    }

} else {
    header("Location: index.php");
}
