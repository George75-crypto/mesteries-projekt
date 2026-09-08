<?php
$servername = "localhost";
$dbusername = "root";   // Szükség esetén módosítsd
$dbpassword = "";       // Szükség esetén módosítsd
$dbname = "parkside_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Előkészített lekérdezés használata az SQL Injection kivédésére
    $stmt = $conn->prepare("SELECT * FROM felhasznalok WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Sikeres belépés esetén átirányítás a razor.html oldalra
        header('Location: razor.html');
        exit();
    } else {
        echo "<script>
            alert('Hibás felhasználónév vagy jelszó!');
            window.location.href = 'index.html';
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>