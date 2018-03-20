<?php

namespace DEVJS\SwaggerGenerator\src;

class Generator
{
    const FILLABLE = 'fillable';
    const RULES = 'rules';
    const RELATIONS = 'relations';

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

    public function __construct(ClassFinder $classFinder, Property $property, DefinitionParser $definitionParser,
                                RouterParser $routerParser, Parameter $parameter, Response $response,
                                Path $path, Annotation $annotation, BaseInfo $baseInfo)
    {
        $this->classFinder = $classFinder;
        $this->property = $property;
        $this->definitionParser = $definitionParser;
        $this->routerParser = $routerParser;
        $this->parameter = $parameter;
        $this->response = $response;
        $this->path = $path;
        $this->annotation = $annotation;
        $this->baseInfo = $baseInfo;
    }

    public function generate()
    {
        /** Namespaces */
        $modelNamespaces = $this->classFinder->getNamespaces('app/Entities/');
        $repositoryNamespaces = $this->classFinder->getRepositoryNamespaces();
        $controllerNamespaces = $this->classFinder->getControllerNamespaces('app/Http/Controllers');

        /** Property */
        $classesRules = $this->property->fillProperties($modelNamespaces, self::RULES);

        $relations = $this->property->fillProperties($repositoryNamespaces, self::RELATIONS);

        /** Definitions */
        $allModelRequests = $this->definitionParser->parseRequests($classesRules);
        $allModelResponses = $this->definitionParser->parseResponses($classesRules, $relations);

        /** ROUTES $routes */
        $parsedRoutes = $this->routerParser->parseLaravelRouter();

        $responses = $this->response->generateResponses();
        /** Annotations */
        $paths = $this->path->fillPath($parsedRoutes, $controllerNamespaces);

        /** BaseInfo */
        $baseInfoAnnotation = $this->annotation->getFromClass('App\Http\Controllers\Controller');
        $baseInfo = $this->baseInfo->get($baseInfoAnnotation);
        $swagger = json_encode(
            [
                'swagger' => $baseInfo['swagger'],
                'info' => $baseInfo['info'],
                'host' => $baseInfo['host'],
                'basePath' => $baseInfo['basePath'],
                'paths' => $paths,
                'definitions' => $allModelRequests + $allModelResponses,
                'responses' => $responses
            ]);

        return $swagger;
    }
}