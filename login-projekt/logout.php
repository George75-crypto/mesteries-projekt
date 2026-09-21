<?php
require __DIR__ . '/config.php';

// Kijelentkezés csak POST-tal és érvényes tokennel (így egy idegen oldal nem tud kilépeltetni)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    logout_user();
    redirect('login.php');
}

redirect('index.php');
