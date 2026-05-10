<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Compression et redimensionnement d'images avec GD (natif PHP).
 *
 * Objectif : limiter chaque photo uploadée à MAX_W × MAX_H pixels
 * et la recompresser en JPEG à la qualité cible, quel que soit le
 * format source (jpg, png, webp).
 *
 * Pourquoi GD et pas Intervention Image ?
 *   → GD est inclus dans PHP. Pas de dépendance Composer à ajouter,
 *     fonctionne sur Render en plan free sans configuration.
 *
 * Gains typiques (photo iPhone) :
 *   Avant : 3–5 Mo, 4032×3024px
 *   Après : 80–150 Ko, 800×800px max
 *   Réduction : ~95 % du poids
 */
class ImageCompressor
{
    // Dimensions max pour les photos de profil
    private const PROFILE_MAX_W = 800;
    private const PROFILE_MAX_H = 800;
    private const PROFILE_QUALITY = 82;   // JPEG 0–100 — bon équilibre qualité/poids

    // Dimensions max pour les pièces jointes dans le chat
    private const ATTACH_MAX_W = 1200;
    private const ATTACH_MAX_H = 1200;
    private const ATTACH_QUALITY = 78;

    /**
     * Compresse et stocke une photo de profil.
     *
     * @param  UploadedFile $file   Fichier uploadé depuis le formulaire
     * @param  string       $disk   'public' ou 's3'
     * @return string               Chemin relatif stocké en DB (ex: profiles/abc123.jpg)
     */
    public function storeProfilePhoto(UploadedFile $file, string $disk): string
    {
        return $this->processAndStore(
            $file,
            'profiles',
            $disk,
            self::PROFILE_MAX_W,
            self::PROFILE_MAX_H,
            self::PROFILE_QUALITY
        );
    }

    /**
     * Compresse et stocke une pièce jointe de chat.
     *
     * @param  UploadedFile $file
     * @param  string       $disk
     * @return string
     */
    public function storeAttachment(UploadedFile $file, string $disk): string
    {
        return $this->processAndStore(
            $file,
            'attachments',
            $disk,
            self::ATTACH_MAX_W,
            self::ATTACH_MAX_H,
            self::ATTACH_QUALITY
        );
    }

    /**
     * Cœur du traitement : redimensionne et compresse via GD.
     *
     * Si GD n'est pas disponible ou si le traitement échoue,
     * on stocke le fichier original sans compression (fallback sûr).
     */
    private function processAndStore(
        UploadedFile $file,
        string $folder,
        string $disk,
        int $maxW,
        int $maxH,
        int $quality
    ): string {
        // Nom de fichier unique, toujours en .jpg après compression
        $filename = $folder . '/' . Str::uuid() . '.jpg';
        $fallbackFilename = $folder . '/' . Str::uuid() . '.' . $this->safeOriginalExtension($file);

        // Fallback si GD absent
        if (!extension_loaded('gd')) {
            return $this->storeOriginal($file, $fallbackFilename, $disk);
        }

        try {
            $compressed = $this->compress($file->getPathname(), $maxW, $maxH, $quality);
            $stored = Storage::disk($disk)->put($filename, $compressed);
            if (!$stored) {
                throw new \RuntimeException("Impossible d'enregistrer l'image sur le disque [$disk].");
            }
            return $filename;
        } catch (\Throwable $e) {
            // En cas d'erreur GD inattendue, stocker le fichier original
            report($e);
            return $this->storeOriginal($file, $fallbackFilename, $disk);
        }
    }

    private function storeOriginal(UploadedFile $file, string $filename, string $disk): string
    {
        $path = $file->storeAs('', $filename, $disk);

        if (!$path) {
            throw new \RuntimeException("Impossible d'enregistrer l'image originale sur le disque [$disk].");
        }

        return $path;
    }

    private function safeOriginalExtension(UploadedFile $file): string
    {
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
    }

    /**
     * Lit l'image source, la redimensionne en conservant les proportions,
     * et retourne les bytes JPEG compressés.
     *
     * @throws \RuntimeException si le format n'est pas supporté
     */
    private function compress(string $sourcePath, int $maxW, int $maxH, int $quality): string
    {
        // Lire les dimensions et le type MIME source
        $info = getimagesize($sourcePath);
        if (!$info) {
            throw new \RuntimeException("Impossible de lire l'image : $sourcePath");
        }

        [$srcW, $srcH, $type] = $info;

        // Charger en mémoire GD selon le format source
        $srcImg = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => $this->createFromPng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
            default        => throw new \RuntimeException("Format non supporté : type $type"),
        };

        if (!$srcImg) {
            throw new \RuntimeException("GD n'a pas pu charger l'image.");
        }

        // Corriger l'orientation EXIF (photos prises en portrait sur iPhone)
        $srcImg = $this->fixOrientation($srcImg, $sourcePath, $type);

        // Recalculer les dimensions après correction d'orientation
        $srcW = imagesx($srcImg);
        $srcH = imagesy($srcImg);

        // Calculer les dimensions cibles en conservant le ratio
        [$dstW, $dstH] = $this->calcDimensions($srcW, $srcH, $maxW, $maxH);

        // Créer l'image destination (fond blanc pour les PNG transparents)
        $dstImg = imagecreatetruecolor($dstW, $dstH);
        $white  = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $white);

        // Redimensionner avec rééchantillonnage bicubique (meilleure qualité)
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        // Capturer l'output JPEG dans un buffer
        ob_start();
        imagejpeg($dstImg, null, $quality);
        $bytes = ob_get_clean();

        // Libérer la mémoire GD
        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return $bytes;
    }

    /**
     * Calcule les dimensions de destination en respectant le ratio
     * et sans jamais agrandir une image plus petite que la cible.
     */
    private function calcDimensions(int $srcW, int $srcH, int $maxW, int $maxH): array
    {
        // Si l'image est déjà dans les limites, pas besoin de redimensionner
        if ($srcW <= $maxW && $srcH <= $maxH) {
            return [$srcW, $srcH];
        }

        $ratio  = min($maxW / $srcW, $maxH / $srcH);
        $dstW   = (int) round($srcW * $ratio);
        $dstH   = (int) round($srcH * $ratio);

        return [$dstW, $dstH];
    }

    /**
     * Corrige l'orientation EXIF des photos JPEG prises sur smartphone.
     * Sans ça, les photos portrait apparaissent couchées sur le côté.
     */
    private function fixOrientation(\GdImage $img, string $path, int $type): \GdImage
    {
        // EXIF uniquement disponible pour JPEG
        if ($type !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
            return $img;
        }

        $exif = @exif_read_data($path);
        if (!$exif || empty($exif['Orientation'])) {
            return $img;
        }

        return match ((int) $exif['Orientation']) {
            3 => imagerotate($img, 180, 0)  ?: $img,
            6 => imagerotate($img, -90, 0)  ?: $img,
            8 => imagerotate($img, 90, 0)   ?: $img,
            default => $img,
        };
    }
    /**
     * Charge un PNG en préservant la transparence correctement.
     */
    private function createFromPng(string $path): \GdImage|false
    {
        $src = imagecreatefrompng($path);
        if (!$src) return false;

        $w   = imagesx($src);
        $h   = imagesy($src);
        $dst = imagecreatetruecolor($w, $h);

        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $w - 1, $h - 1, $transparent);

        imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
        imagedestroy($src);

        return $dst;
    }
}
