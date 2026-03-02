<?php

function sponsorsDataFilePath(): string
{
    return __DIR__ . '/config/sponsors.json';
}

function sponsorsLegacyConfigPath(): string
{
    return __DIR__ . '/config/sponsors.php';
}

function sponsorsImagesDirAbsolute(): string
{
    return __DIR__ . '/assets/img/Logo_partenaires';
}

function sponsorsImagesDirRelative(): string
{
    return 'assets/img/Logo_partenaires';
}

function normalizeSponsorUrl(string $url): string
{
    $url = trim($url);
    if ($url === '' || $url === '#') {
        return '';
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    return $url;
}

function bootstrapSponsorsStorage(): void
{
    $dataFile = sponsorsDataFilePath();
    if (file_exists($dataFile)) {
        return;
    }

    $legacy = [];
    $legacyFile = sponsorsLegacyConfigPath();
    if (file_exists($legacyFile)) {
        include $legacyFile;
        if (isset($sponsors) && is_array($sponsors)) {
            $legacy = $sponsors;
        }
    }

    $items = [];
    foreach ($legacy as $image => $url) {
        $items[] = [
            'id' => bin2hex(random_bytes(8)),
            'image' => (string)$image,
            'url' => normalizeSponsorUrl((string)$url),
            'created_at' => date('c'),
        ];
    }

    file_put_contents($dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function loadSponsors(): array
{
    bootstrapSponsorsStorage();

    $dataFile = sponsorsDataFilePath();
    $json = @file_get_contents($dataFile);
    if ($json === false || trim($json) === '') {
        return [];
    }

    $items = json_decode($json, true);
    if (!is_array($items)) {
        return [];
    }

    $normalized = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = isset($item['id']) ? (string)$item['id'] : '';
        $image = isset($item['image']) ? (string)$item['image'] : '';
        $url = isset($item['url']) ? normalizeSponsorUrl((string)$item['url']) : '';
        $createdAt = isset($item['created_at']) ? (string)$item['created_at'] : date('c');

        if ($id === '' || $image === '') {
            continue;
        }

        $normalized[] = [
            'id' => $id,
            'image' => $image,
            'url' => $url,
            'created_at' => $createdAt,
        ];
    }

    return $normalized;
}

function saveSponsors(array $items): bool
{
    $dataFile = sponsorsDataFilePath();
    return (bool)file_put_contents($dataFile, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function convertUploadedImageToWebp(string $sourceTmpPath, string $destinationPath): bool
{
    if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
        return false;
    }

    $binary = @file_get_contents($sourceTmpPath);
    if ($binary === false) {
        return false;
    }

    $image = @imagecreatefromstring($binary);
    if ($image === false) {
        return false;
    }

    if (function_exists('imagepalettetotruecolor')) {
        @imagepalettetotruecolor($image);
    }

    imagealphablending($image, true);
    imagesavealpha($image, true);

    $ok = @imagewebp($image, $destinationPath, 85);
    imagedestroy($image);

    return (bool)$ok;
}

function addSponsor(array $uploadedFile, string $url = ''): array
{
    if (!isset($uploadedFile['error']) || (int)$uploadedFile['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'Upload image invalide.'];
    }

    if (!isset($uploadedFile['tmp_name']) || !is_uploaded_file($uploadedFile['tmp_name'])) {
        return ['ok' => false, 'message' => 'Fichier temporaire introuvable.'];
    }

    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($uploadedFile['tmp_name']) ?: '';
    if (!in_array($mime, $allowedMimes, true)) {
        return ['ok' => false, 'message' => 'Format non supporté (jpg, png, webp, gif).'];
    }

    $size = isset($uploadedFile['size']) ? (int)$uploadedFile['size'] : 0;
    if ($size <= 0 || $size > 5 * 1024 * 1024) {
        return ['ok' => false, 'message' => 'Image invalide (max 5 Mo).'];
    }

    $dirAbs = sponsorsImagesDirAbsolute();
    if (!is_dir($dirAbs)) {
        mkdir($dirAbs, 0775, true);
    }

    $filename = 'sponsor_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.webp';
    $destAbs = $dirAbs . '/' . $filename;
    $destRel = sponsorsImagesDirRelative() . '/' . $filename;

    if (!convertUploadedImageToWebp($uploadedFile['tmp_name'], $destAbs)) {
        return ['ok' => false, 'message' => 'Conversion WebP impossible (vérifie l\'extension GD/WebP du serveur).'];
    }

    $items = loadSponsors();
    array_unshift($items, [
        'id' => bin2hex(random_bytes(8)),
        'image' => $destRel,
        'url' => normalizeSponsorUrl($url),
        'created_at' => date('c'),
    ]);

    if (!saveSponsors($items)) {
        @unlink($destAbs);
        return ['ok' => false, 'message' => 'Erreur lors de la sauvegarde des sponsors.'];
    }

    return ['ok' => true, 'message' => 'Sponsor ajouté avec succès.'];
}

function deleteSponsor(string $id): array
{
    $id = trim($id);
    if ($id === '') {
        return ['ok' => false, 'message' => 'ID sponsor invalide.'];
    }

    $items = loadSponsors();
    $kept = [];
    $toDelete = null;

    foreach ($items as $item) {
        if (($item['id'] ?? '') === $id) {
            $toDelete = $item;
            continue;
        }
        $kept[] = $item;
    }

    if ($toDelete === null) {
        return ['ok' => false, 'message' => 'Sponsor introuvable.'];
    }

    if (!saveSponsors($kept)) {
        return ['ok' => false, 'message' => 'Erreur lors de la suppression.'];
    }

    $imagePath = (string)($toDelete['image'] ?? '');
    $abs = __DIR__ . '/' . ltrim(str_replace('\\', '/', $imagePath), '/');
    $imagesDir = realpath(sponsorsImagesDirAbsolute());
    $imageReal = realpath($abs);

    if ($imagesDir !== false && $imageReal !== false && strpos($imageReal, $imagesDir) === 0) {
        @unlink($imageReal);
    }

    return ['ok' => true, 'message' => 'Sponsor supprimé avec succès.'];
}

function migrateSponsorsToWebp(): array
{
    $items = loadSponsors();
    $converted = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($items as $index => $item) {
        $imagePath = (string)($item['image'] ?? '');
        if ($imagePath === '') {
            $skipped++;
            continue;
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if ($ext === 'webp') {
            $skipped++;
            continue;
        }

        $sourceAbs = __DIR__ . '/' . ltrim(str_replace('\\', '/', $imagePath), '/');
        if (!file_exists($sourceAbs)) {
            $failed++;
            continue;
        }

        $baseName = pathinfo($sourceAbs, PATHINFO_FILENAME);
        $targetAbs = sponsorsImagesDirAbsolute() . '/' . $baseName . '.webp';
        $suffix = 1;
        while (file_exists($targetAbs)) {
            $targetAbs = sponsorsImagesDirAbsolute() . '/' . $baseName . '_' . $suffix . '.webp';
            $suffix++;
        }

        if (!convertUploadedImageToWebp($sourceAbs, $targetAbs)) {
            $failed++;
            continue;
        }

        $items[$index]['image'] = sponsorsImagesDirRelative() . '/' . basename($targetAbs);
        $converted++;

        $imagesDir = realpath(sponsorsImagesDirAbsolute());
        $sourceReal = realpath($sourceAbs);
        if ($imagesDir !== false && $sourceReal !== false && strpos($sourceReal, $imagesDir) === 0) {
            @unlink($sourceReal);
        }
    }

    if (!saveSponsors($items)) {
        return [
            'ok' => false,
            'message' => 'Conversion effectuée mais sauvegarde JSON impossible.',
            'converted' => $converted,
            'skipped' => $skipped,
            'failed' => $failed,
        ];
    }

    return [
        'ok' => true,
        'message' => 'Migration WebP terminée.',
        'converted' => $converted,
        'skipped' => $skipped,
        'failed' => $failed,
    ];
}
