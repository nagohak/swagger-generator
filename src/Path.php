<?php

namespace DEVJS\SwaggerGenerator\src;

class Path
{
    private $annotation;
    private $routerParser;

    public function __construct(Annotation $annotation, RouterParser $routerParser)
    {
        $this->annotation = $annotation;
        $this->routerParser = $routerParser;
    }

    public function fillPath($parsedRoutes, $controllerNamespaces): array
    {
        $methods = [];
        $paths = [];
        $httpMethods = [];

        foreach ($parsedRoutes as $route) {
            if (array_key_exists($route['controller'], $controllerNamespaces)) {
                $methods = $this->annotation->parse($route['controller']);
            }

            foreach ($methods as $method => $value) {

                if (isset($method)) {
                    if ($route['method'] === $method) {
                        $httpMethods[$route['http_method']] = $value;
                    }
                }
            }
            foreach ($httpMethods as $method => $data) {
                $paths[$route['uri']][$method] = $data;
            }
            $httpMethods = [];
        }

        return $paths;
    }
}