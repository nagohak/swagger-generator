<?php

namespace DEVJS\SwaggerGenerator\tests;

use DEVJS\SwaggerGenerator\src\Annotation;
use DEVJS\SwaggerGenerator\src\ClassFinder;
use DEVJS\SwaggerGenerator\src\DefinitionParser;
use DEVJS\SwaggerGenerator\src\BaseInfo;
use DEVJS\SwaggerGenerator\src\Generator;
use DEVJS\SwaggerGenerator\src\Parameter;
use DEVJS\SwaggerGenerator\src\Path;
use DEVJS\SwaggerGenerator\src\Property;
use DEVJS\SwaggerGenerator\src\Response;
use DEVJS\SwaggerGenerator\src\RouterParser;
use SwaggerGenerator\SwaggerGenerator;
use Tests\TestCase;

class GenerateTest extends TestCase
{
    /** @var Generator */
    private $generator;

    /** @var ClassFinder */
    private $classFinder;

    /** @var Property */
    private $property;

    /** @var DefinitionParser */
    private $definitionParser;

    /** @var RouterParser */
    private $routerParser;

    /** @var Parameter */
    private $parameter;

    /** @var Response */
    protected $response;

    /** @var Path */
    protected $path;

    /** @var Annotation */
    protected $annotation;

    /** @var BaseInfo */
    protected $baseInfo;
    public function setUp()
    {
        parent::setUp();

        $this->classFinder = new ClassFinder();
        $this->property = New Property();
        $this->definitionParser = New DefinitionParser($this->property);
        $this->routerParser = New RouterParser();
        $this->parameter = New Parameter();
        $this->response = New Response();
        $this->annotation = New Annotation($this->routerParser);
        $this->path = New Path($this->annotation, $this->routerParser);
        $this->baseInfo = New BaseInfo();

        $this->generator = new Generator(
            $this->classFinder, $this->property, $this->definitionParser, $this->routerParser,
            $this->parameter, $this->response, $this->path, $this->annotation, $this->baseInfo
        );

    }
    public function testGenerate()
    {
        dd($this->generator->generate());
    }

}