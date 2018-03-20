<?php

namespace DEVJS\SwaggerGenerator\tests;

use App\Entities\User;
use DEVJS\SwaggerGenerator\src\ClassFinder;
use DEVJS\SwaggerGenerator\src\Property;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    const FILLABLE = 'fillable';
    const RULES = 'rules';
    const RELATIONS = 'relations';

    /** @var ClassFinder */
    private $classFinder;

    /** @var Property */
    private $property;

    private $modelNamespaces;

    public function setUp()
    {
        parent::setUp();

        $this->classFinder = new ClassFinder();
        $this->property = new Property();

        $this->modelNamespaces = $this->classFinder->getNamespaces('app/Entities/');
    }

    public function testFillProperties()
    {
        $fillable = $this->property->fillProperties($this->modelNamespaces, self::FILLABLE);

        $user = new User();

        $this->assertArraySubset($user->getFillable(), $fillable['User']);
    }

    public function testFillPropertyTypes()
    {
        $propertyTypes = $this->property->fillPropertyTypes($this->modelNamespaces, self::RULES);

        dd();
    }
}