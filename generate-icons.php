<?php
/**
 * Générateur d'icônes PWA
 * 
 * Usage: php generate-icons.php public/images/icons/original.png
 * 
 * Place ton icône originale (au moins 512x512) dans public/images/icons/
 * puis lance ce script pour générer toutes les tailles.
 */

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];
$inputDir = __DIR__ . '/public/images/icons/';

// Trouver l'image source
$source = null;
foreach (['original.png', 'icon.png', 'original.jpg', 'icon.jpg', 'original.jpeg', 'icon.jpeg'] as $name) {
    if (file_exists($inputDir . $name)) {
        $source = $inputDir . $name;
        break;
    }
}

if (!$source) {
    echo "❌ Place ton icône dans public/images/icons/ avec le nom 'original.png' ou 'icon.png'\n";
    exit(1);
}

echo "📱 Génération des icônes PWA depuis: {$source}\n";

$ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
if ($ext === 'png') {
    $img = imagecreatefrompng($source);
} elseif ($ext === 'jpg' || $ext === 'jpeg') {
    $img = imagecreatefromjpeg($source);
} else {
    echo "❌ Format non supporté. Utilise PNG ou JPG.\n";
    exit(1);
}

if (!$img) {
    echo "❌ Impossible de lire l'image.\n";
    exit(1);
}

$origW = imagesx($img);
$origH = imagesy($img);

foreach ($sizes as $size) {
    $resized = imagecreatetruecolor($size, $size);
    
    // Préserver la transparence pour PNG
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
    imagefill($resized, 0, 0, $transparent);
    
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $size, $size, $origW, $origH);
    
    $output = $inputDir . "icon-{$size}x{$size}.png";
    imagepng($resized, $output);
    imagedestroy($resized);
    
    echo "  ✅ icon-{$size}x{$size}.png\n";
}

imagedestroy($img);
echo "\n🎉 Toutes les icônes ont été générées !\n";
echo "📂 Dossier: public/images/icons/\n";
