<?php

use Municipio\Schema\BaseType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(BaseType::class)]
class BaseTypeTest extends \PHPUnit\Framework\TestCase
{
    #[TestDox('Class can be instantiated')]
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(\Municipio\Schema\BaseType::class, $this->getBaseTypeInstance());
    }

    #[TestDox('context contains schema and municipio')]
    public function testGetContext()
    {
        $baseType = $this->getBaseTypeInstance();
        $context = $baseType->getContext();

        $this->assertIsArray($context);
        $this->assertArrayHasKey('schema', $context);
        $this->assertArrayHasKey('municipio', $context);
        $this->assertEquals('https://schema.org', $context['schema']);
        $this->assertEquals('https://schema.municipio.tech/schema.jsonld', $context['municipio']);
    }

    private function getBaseTypeInstance():BaseType {
        return new class extends BaseType {
            public function getProperties(): array
            {
                return $this->properties;
            }
        };
    }
}