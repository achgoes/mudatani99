<?php
require_once __DIR__ . '/config/app.php';

$reason = $_GET['reason'] ?? '';

destroyAuthSession();

$redirectPath = ($reason === 'timeout') ? 'index.php?timeout=1' : 'index.php';
header('Location: ' . appUrl($redirectPath));
exit;
