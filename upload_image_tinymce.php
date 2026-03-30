<?php
/**
 * Endpoint d'upload d'images pour TinyMCE
 * Reçoit une image via POST, la valide et retourne l'URL en JSON.
 */
session_start();
require_once "fonctions.php";

header('Content-Type: application/json; charset=utf-8');

// ── Vérification authentification admin ─────────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé.']);
    exit;
}

// ── Constantes ──────────────────────────────────────────────────────────────
$uploadDir   = __DIR__ . '/uploads/pef_images/';
$uploadUrl   = 'uploads/pef_images/';
$maxSize     = 5 * 1024 * 1024; // 5 Mo
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

// ── Vérification du fichier ─────────────────────────────────────────────────
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun fichier reçu ou erreur d\'upload.']);
    exit;
}

$file = $_FILES['file'];
$mime = mime_content_type($file['tmp_name']);

if (!in_array($mime, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Type de fichier non autorisé (JPEG, PNG, WEBP, GIF uniquement).']);
    exit;
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Le fichier ne doit pas dépasser 5 Mo.']);
    exit;
}

// ── Sauvegarde ──────────────────────────────────────────────────────────────
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$safeName = uniqid('pef_img_', true) . '.' . $ext;

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible de sauvegarder le fichier.']);
    exit;
}

// ── Réponse JSON (TinyMCE attend "location") ────────────────────────────────
echo json_encode(['location' => $uploadUrl . $safeName]);
