<?php

namespace Tests\Unit;

use App\Models\Traits\HasJsonAttributes;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class HasJsonAttributesTest extends TestCase
{
    /** @var object */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class
        {
            use HasJsonAttributes;
        };
    }

    public function test_it_returns_null_for_an_empty_value(): void
    {
        $this->assertNull($this->subject->getJsonAttribute('payload', null));
        $this->assertNull($this->subject->getJsonAttribute('payload', ''));
    }

    public function test_it_decodes_a_json_payload(): void
    {
        $decoded = $this->subject->getJsonAttribute('payload', '{"displayName":"App\\\\Jobs\\\\Foo","tries":1}');

        $this->assertSame(['displayName' => 'App\\Jobs\\Foo', 'tries' => 1], $decoded);
    }

    public function test_it_returns_the_raw_value_when_it_is_not_valid_json(): void
    {
        $this->assertSame('not json at all', $this->subject->getJsonAttribute('payload', 'not json at all'));
    }

    public function test_it_unserializes_php_serialized_objects_nested_in_the_json(): void
    {
        $serialized = serialize(new Collection(['a', 'b']));
        $json = json_encode(['data' => ['command' => $serialized]]);

        $decoded = $this->subject->getJsonAttribute('payload', $json);

        $this->assertInstanceOf(Collection::class, $decoded['data']['command']);
        $this->assertSame(['a', 'b'], $decoded['data']['command']->all());
    }

    public function test_it_refuses_to_instantiate_classes_outside_the_allow_list(): void
    {
        $serialized = serialize(new \ArrayObject(['a']));
        $json = json_encode(['data' => ['command' => $serialized]]);

        $decoded = $this->subject->getJsonAttribute('payload', $json);

        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $decoded['data']['command']);
    }

    public function test_it_encodes_a_value_back_to_json(): void
    {
        $this->assertSame('{"a":1}', $this->subject->setJsonAttribute('payload', ['a' => 1]));
    }

    public function test_it_stores_null_for_an_empty_value(): void
    {
        $this->assertNull($this->subject->setJsonAttribute('payload', null));
        $this->assertNull($this->subject->setJsonAttribute('payload', []));
    }
}
