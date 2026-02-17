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
    $first_name = trim($_POST['first_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['password_confirm']);

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/", $password)) {
        header("Location: manage_users.php?error=weak_password");
        exit;
    }

    if ($password !== $confirm) {
        header("Location: manage_users.php?error=password_mismatch");
        exit;
    }

    if (usernameExiste($pdo, $username)) {
        header("Location: manage_users.php?error=username_exists");
        exit;
    }

    if (emailExiste($pdo, $email)) {
        header("Location: manage_users.php?error=email_exists");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    creerUtilisateur($pdo, $last_name, $first_name, $username, $email, $password_hash, 'admin', 1);

    header("Location: manage_users.php?success=admin_added");
    exit;
}

header("Location: manage_users.php");
exit;
