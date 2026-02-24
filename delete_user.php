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

// si le compte est admin il redirige vers l'encre admin, sinon si le compte est coach il redirige vers l'encre coach, sinon redirige vers l'encre équipe
$user = getUserById($pdo, $id);
if ($user) {
    if ($user['role_slug'] === 'admin') {
        header("Location: manage_users.php?success=1#liste-admins");
        exit;
    } elseif ($user['role_slug'] === 'coach') {
        header("Location: manage_users.php?success=1#liste-coachs");
        exit;
    }
}   else {
    header("Location: manage_users.php?success=1#liste-equipes");
    exit;
}