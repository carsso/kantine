<?php

namespace Tests\Unit;

use App\Lib\ParticleThemes;
use PHPUnit\Framework\TestCase;

class ParticleThemesTest extends TestCase
{
    public function test_every_theme_is_named_and_categorized(): void
    {
        foreach (ParticleThemes::all() as $key => $theme) {
            $this->assertMatchesRegularExpression('/^[a-z_]+$/', $key);
            $this->assertNotEmpty($theme['name'], "Theme {$key} has no name");
            $this->assertContains($theme['category'], ParticleThemes::CATEGORIES, "Theme {$key} has no known category");
            $this->assertFalse($theme['hdr'], "Theme {$key} must disable HDR");
        }
    }

    public function test_the_base_particles_only_count_the_layers(): void
    {
        foreach (ParticleThemes::all() as $key => $theme) {
            if (! isset($theme['particles']['groups'])) {
                continue;
            }

            $layersCount = array_sum(array_map(fn (array $layer): int => $layer['number']['value'], $theme['particles']['groups']));

            $this->assertSame($layersCount, $theme['particles']['number']['value'], "Theme {$key} would spawn unstyled particles");
        }
    }

    public function test_big_particles_stay_in_the_background(): void
    {
        foreach (ParticleThemes::all() as $key => $theme) {
            foreach ($theme['particles']['groups'] ?? [] as $layer) {
                $size = $layer['size']['value'];

                if ((is_array($size) ? $size['max'] : $size) <= 5) {
                    continue;
                }

                foreach (['fill', 'stroke'] as $paint) {
                    $opacity = $layer['paint'][$paint]['opacity'];

                    $this->assertLessThanOrEqual(0.5, is_array($opacity) ? $opacity['max'] : $opacity, "Theme {$key} draws big particles over the menu ({$paint})");
                }

                if ($layer['shape']['type'] === 'image') {
                    // Baked into the image, then applied again: the brightest frame is the square of the max.
                    $opacity = $layer['opacity']['value'];

                    $this->assertLessThanOrEqual(0.501, (is_array($opacity) ? $opacity['max'] : $opacity) ** 2, "Theme {$key} draws big images over the menu");
                }
            }
        }
    }

    public function test_every_theme_has_normalized_keywords_of_its_own(): void
    {
        $owners = [];
        foreach (ParticleThemes::all() as $key => $theme) {
            $this->assertNotEmpty($theme['keywords'], "Theme {$key} has no keyword");
            foreach ($theme['keywords'] as $keyword) {
                $this->assertMatchesRegularExpression('/^[a-z0-9]+( [a-z0-9]+)*$/', $keyword, "Keyword {$keyword} of {$key} is not normalized");
                $this->assertArrayNotHasKey($keyword, $owners, "Keyword {$keyword} is used by {$key} and ".($owners[$keyword] ?? ''));
                $owners[$keyword] = $key;
            }
        }
    }

    public function test_no_theme_draws_emojis(): void
    {
        $json = json_encode(ParticleThemes::all(), JSON_UNESCAPED_UNICODE);

        $this->assertDoesNotMatchRegularExpression('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $json);
    }

    public function test_every_theme_caps_the_rendering_for_the_screens(): void
    {
        foreach (ParticleThemes::all() as $key => $theme) {
            $this->assertLessThanOrEqual(30, $theme['fpsLimit'], "Theme {$key} runs too fast for the screens");
            $this->assertFalse($theme['detectRetina'], "Theme {$key} paints at the device pixel ratio");
        }
    }

    public function test_animated_images_start_at_their_brightest(): void
    {
        foreach (ParticleThemes::all() as $key => $theme) {
            foreach ($theme['particles']['groups'] ?? [] as $layer) {
                if ($layer['shape']['type'] === 'image' && isset($layer['opacity']['animation'])) {
                    $this->assertSame('max', $layer['opacity']['animation']['startValue'], "Theme {$key} bakes a random opacity into its images");
                }
            }
        }
    }

    public function test_the_icons_are_drawn_as_bundled_images(): void
    {
        $json = json_encode(ParticleThemes::all());

        $this->assertStringNotContainsString('Font Awesome', $json, 'The text shape rasterizes every glyph on every frame');

        preg_match_all('/"src":"([^"]*)"/', $json, $sources);
        $this->assertNotEmpty($sources[1]);
        foreach (array_unique($sources[1]) as $source) {
            $this->assertMatchesRegularExpression('/^icon:[a-z0-9-]+$/', $source);
            $this->assertArrayHasKey(substr($source, 5), ParticleThemes::icons(), "{$source} is not a known icon");
        }
        $this->assertStringNotContainsString('"replaceColor":false', $json);
    }

    public function test_every_icon_matches_its_solid_svg(): void
    {
        $directory = __DIR__.'/../../node_modules/@fortawesome/fontawesome-pro/svgs/solid';
        if (! is_dir($directory)) {
            $this->markTestSkipped('Font Awesome Pro is not installed.');
        }

        foreach (ParticleThemes::icons() as $name => [$width, $height]) {
            $this->assertFileExists("{$directory}/{$name}.svg", "Icon {$name} has no solid SVG");
            $this->assertStringContainsString("viewBox=\"0 0 {$width} {$height}\"", file_get_contents("{$directory}/{$name}.svg"), "Icon {$name} has the wrong size");
        }
    }
}
