<?php
session_start();
require "fonctions.php";
$pdo = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['role_slug'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $last_name = trim($_POST['last_name']);
    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $confirm   = trim($_POST['password_confirm']);

    $email = strtolower($username) . "@team.local";

    $first_name = "";

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/", $password)) {
        header("Location: manage_users.php?error=weak_password");
        exit;
    }

    if ($password !== $confirm) {
        header("Location: manage_users.php?error=password_mismatch");
        exit;
    }

    if (usernameExiste($pdo, $username)) {
        header("Location: manage_users.php?error=user_exists");
        exit;
    }

    if (emailExiste($pdo, $email)) {
        $email = strtolower($username) . uniqid() . "@team.local";
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    creerUtilisateur($pdo, $last_name, $first_name, $username, $email, $password_hash, 'user', 1);

    header("Location: manage_users.php?success=1");
    exit;
}

header("Location: manage_users.php");
exit;