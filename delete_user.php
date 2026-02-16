<?php
session_start();
require "fonctions.php";

$pdo = getDB();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? null) !== 'admin') {
    header("Location: login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_users.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header("Location: manage_users.php");
    exit;
}

if ($id === (int)$_SESSION['user_id']) {
    header("Location: manage_users.php");
    exit;
}

deleteAccount($pdo, $id);

header("Location: manage_users.php");
exit;