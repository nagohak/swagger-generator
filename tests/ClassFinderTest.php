<?php

namespace DEVJS\SwaggerGenerator\tests;

use DEVJS\SwaggerGenerator\ClassFinder;
use Tests\TestCase;

class classFinderTest extends TestCase
{
    const MODEL_PATH = 'app/Entities/';

    /** @var ClassFinder */
    private $classFinder;

    public function setUp()
    {
        parent::setUp();

        $this->classFinder = new ClassFinder();
    }

    public function testGetNamespaces()
    {
        $modelNamespaces = $this->classFinder->getNamespaces(self::MODEL_PATH);

        $this->assertArrayHasKey('Account', $modelNamespaces);
        $this->assertArrayHasKey('User', $modelNamespaces);
    }

    public function testGetRepositoryNamespaces()
    {
        $repositoryNamespaces = $this->classFinder->getRepositoryNamespaces();

        $this->assertArrayHasKey('Account', $repositoryNamespaces);
        $this->assertArrayHasKey('User', $repositoryNamespaces);
    }
}