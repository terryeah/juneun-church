<?php

namespace App\Services;

/**
 * Converts uploaded raster images to the WebP format using GD.
 *
 * Transparency is preserved. Sources GD cannot decode (HEIC, corrupt
 * files) and animated images simply return null so callers can fall
 * back to storing the original file untouched.
 */
class WebpImageConverter
{
    /**
     * Quality used for the WebP encoder.
     */
    private const QUALITY = 82;

    /**
     * Whether the binary is already a WebP image.
     */
    public function isWebp(string $binary): bool
    {
        return str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP';
    }

    /**
     * Whether the binary looks like a raster image GD may decode.
     */
    public function isConvertibleImage(string $binary): bool
    {
        $info = @getimagesizefromstring($binary);

        return $info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_BMP, IMAGETYPE_WEBP], true);
    }

    /**
     * Convert an image binary to WebP, or null when not possible.
     */
    public function toWebp(string $binary): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $output = ob_get_clean();

        return ($ok && $output !== false && $output !== '') ? $output : null;
    }
}
