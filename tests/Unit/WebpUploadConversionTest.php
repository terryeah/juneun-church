<?php

namespace Tests\Unit;

use App\Filament\Support\SaveUploadsAsWebp;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the WebP upload conversion.
 */
class WebpUploadConversionTest extends TestCase
{
    /**
     * Whether a binary is a WebP image.
     */
    private function isWebp(string $binary): bool
    {
        return str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP';
    }

    /**
     * PNG input converts to a valid WebP binary.
     */
    public function test_png_converts_to_webp(): void
    {
        $image = imagecreatetruecolor(20, 20);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 40, 40));
        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();

        $webp = SaveUploadsAsWebp::toWebp($png);

        $this->assertNotNull($webp);
        $this->assertTrue($this->isWebp($webp));
    }

    /**
     * Oversized images are scaled down during conversion.
     */
    public function test_large_images_are_scaled_down(): void
    {
        $image = imagecreatetruecolor(3000, 1500);
        imagefill($image, 0, 0, imagecolorallocate($image, 10, 60, 120));
        ob_start();
        imagejpeg($image);
        $jpeg = (string) ob_get_clean();

        $webp = SaveUploadsAsWebp::toWebp($jpeg);

        $this->assertNotNull($webp);
        [$width] = getimagesizefromstring($webp);
        $this->assertSame(2560, $width);
    }

    /**
     * GIF files are deliberately left unconverted.
     */
    public function test_gifs_are_not_converted(): void
    {
        $image = imagecreatetruecolor(10, 10);
        ob_start();
        imagegif($image);
        $gif = (string) ob_get_clean();

        $this->assertNull(SaveUploadsAsWebp::toWebp($gif));
    }

    /**
     * iPhone HEIC photos convert when Imagick with HEIC support exists.
     */
    public function test_heic_converts_to_webp_with_imagick(): void
    {
        if (! extension_loaded('imagick') || ! in_array('HEIC', \Imagick::queryFormats('HEIC'), true)) {
            $this->markTestSkipped('Imagick with HEIC support is not available.');
        }

        $heic = (string) file_get_contents(__DIR__.'/../fixtures/sample.heic');
        $webp = SaveUploadsAsWebp::toWebp($heic);

        $this->assertNotNull($webp);
        $this->assertTrue($this->isWebp($webp));
    }

    /**
     * Non-image binaries are refused.
     */
    public function test_non_images_are_not_converted(): void
    {
        $this->assertNull(SaveUploadsAsWebp::toWebp('%PDF-1.7 not an image'));
    }
}
