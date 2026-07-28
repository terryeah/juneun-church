<?php

namespace Tests\Unit;

use App\Services\WebpImageConverter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the WebP image converter.
 */
class WebpImageConverterTest extends TestCase
{
    /**
     * Build a small PNG binary with GD.
     */
    private function makePng(): string
    {
        $image = imagecreatetruecolor(20, 20);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 40, 40));
        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /**
     * PNG input converts to a valid WebP binary.
     */
    public function test_png_converts_to_webp(): void
    {
        $converter = new WebpImageConverter;
        $webp = $converter->toWebp($this->makePng());

        $this->assertNotNull($webp);
        $this->assertTrue($converter->isWebp($webp));
    }

    /**
     * Non-image binaries are recognised and refused.
     */
    public function test_non_images_are_not_converted(): void
    {
        $converter = new WebpImageConverter;

        $this->assertFalse($converter->isConvertibleImage('%PDF-1.7 not an image'));
        $this->assertNull($converter->toWebp('%PDF-1.7 not an image'));
    }
}
