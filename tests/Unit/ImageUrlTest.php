<?php

namespace Tests\Unit;

use App\Support\ImageUrl;
use Tests\TestCase;

class ImageUrlTest extends TestCase
{
    public function test_stored_image_points_to_its_file(): void
    {
        $this->assertSame(asset('images/flag.png'), ImageUrl::for('flag.png'));
    }

    public function test_missing_image_uses_the_placeholder(): void
    {
        foreach ([null, '', '  '] as $imagem) {
            $this->assertSame(ImageUrl::PLACEHOLDER, ImageUrl::for($imagem));
        }
    }
}
