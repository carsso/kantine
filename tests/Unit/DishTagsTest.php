<?php

namespace Tests\Unit;

use App\Models\Dish;
use PHPUnit\Framework\TestCase;

class DishTagsTest extends TestCase
{
    public function test_every_tag_definition_is_complete(): void
    {
        foreach (Dish::getTagsDefinitions() as $tag => $definition) {
            $this->assertArrayHasKey('name', $definition, "Tag {$tag} has no name");
            $this->assertArrayHasKey('name_short', $definition, "Tag {$tag} has no short name");
            $this->assertArrayHasKey('icon', $definition, "Tag {$tag} has no icon");
            $this->assertArrayHasKey('emoji', $definition, "Tag {$tag} has no emoji");
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $definition['color'], "Tag {$tag} has an invalid color");
        }
    }

    public function test_it_resolves_the_labels_of_a_known_tag(): void
    {
        $this->assertSame('Végétarien', Dish::getTagName('vegetarian'));
        $this->assertSame('Végé.', Dish::getTagShortName('vegetarian'));
        $this->assertSame('fa-seedling', Dish::getTagIcon('vegetarian'));
        $this->assertSame('🌱', Dish::getTagEmoji('vegetarian'));
        $this->assertSame('#A6D64D', Dish::getTagColor('vegetarian'));
    }

    public function test_an_unknown_tag_falls_back_to_neutral_values(): void
    {
        $this->assertSame('inconnu', Dish::getTagName('inconnu'));
        $this->assertSame('inconnu', Dish::getTagShortName('inconnu'));
        $this->assertSame('fa-question', Dish::getTagIcon('inconnu'));
        $this->assertSame('❓', Dish::getTagEmoji('inconnu'));
        $this->assertSame('#000000', Dish::getTagColor('inconnu'));
    }
}
