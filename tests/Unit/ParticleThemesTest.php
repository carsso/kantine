<?php

namespace Tests\Unit;

use App\Lib\ParticleThemes;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;

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

    public function test_the_icons_match_the_font_awesome_stylesheet(): void
    {
        $stylesheet = __DIR__.'/../../node_modules/@fortawesome/fontawesome-pro/css/all.css';
        if (! is_file($stylesheet)) {
            $this->markTestSkipped('Font Awesome Pro is not installed.');
        }
        $css = file_get_contents($stylesheet);

        foreach ((new ReflectionClassConstant(ParticleThemes::class, 'ICONS'))->getValue() as $name => $glyph) {
            $codepoint = sprintf('%x', mb_ord($glyph));

            $this->assertMatchesRegularExpression("/\\.fa-{$name} \\{\\s*--fa: \"\\\\{$codepoint}\";/", $css, "Icon {$name} does not match Font Awesome");
        }
    }
}
