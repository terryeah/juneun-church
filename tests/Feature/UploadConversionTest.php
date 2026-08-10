<?php

namespace Tests\Feature;

use App\Filament\Support\SaveUploadsAsWebp;
use Tests\TestCase;

/**
 * Covers what the upload pipeline agrees to store.
 *
 * A photograph that will not convert must be refused rather than kept
 * in its original form: a camera RAW or an unreadable HEIC on the CDN
 * is a file most browsers cannot draw, and the gallery would show a
 * hole. A 주보 PDF and a GIF are stored untouched by design.
 */
class UploadConversionTest extends TestCase
{
    /**
     * A JPEG is a photograph, so it has to become WebP.
     */
    public function test_a_photograph_must_convert(): void
    {
        $jpeg = imagecreatetruecolor(8, 8);
        ob_start();
        imagejpeg($jpeg);
        $binary = (string) ob_get_clean();

        $this->assertTrue(SaveUploadsAsWebp::mustConvert($binary));
        $this->assertNotNull(SaveUploadsAsWebp::toWebp($binary));
    }

    /**
     * A PDF is the document itself, not a photograph, so it is stored
     * as it arrived. This is what keeps 주보 uploads working.
     */
    public function test_a_pdf_is_left_alone(): void
    {
        $pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";

        $this->assertFalse(SaveUploadsAsWebp::mustConvert($pdf));
    }

    /**
     * A GIF keeps its frames; every browser draws one anyway.
     */
    public function test_a_gif_is_left_alone(): void
    {
        $gif = imagecreatetruecolor(8, 8);
        ob_start();
        imagegif($gif);
        $binary = (string) ob_get_clean();

        $this->assertStringStartsWith('GIF8', $binary);
        $this->assertFalse(SaveUploadsAsWebp::mustConvert($binary));
    }

    /**
     * A file the server cannot even name still has to convert, so it is
     * refused rather than stored. This is the case that matters: a
     * camera RAW with no delegate is unreadable AND unnameable, and an
     * "is this an image?" test would wave it through.
     */
    public function test_an_unrecognisable_file_must_still_convert(): void
    {
        $unknown = "\x89PNG\r\n\x1a\n".str_repeat("\x00", 64);

        $this->assertTrue(SaveUploadsAsWebp::mustConvert($unknown));
        $this->assertNull(SaveUploadsAsWebp::toWebp($unknown));

        $nonsense = str_repeat("\x17\x42\xff", 40);

        $this->assertTrue(SaveUploadsAsWebp::mustConvert($nonsense));
        $this->assertNull(SaveUploadsAsWebp::toWebp($nonsense));
    }
}
