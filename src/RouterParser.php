<?php

namespace DEVJS\SwaggerGenerator;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

class RouterParser
{
    const ROUTER_PREFIX = '/api/v1';

    public function getRoutes()
    {
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
        $routes = [];
        /** @var Route $value */
        foreach ($routeCollection as $key => $value) {
            $routes[] = [
                'http_methods' => $value->methods()[0],
                'uri' => $value->uri,
                'parameters' => $value->parameterNames(),
                'controller' => $value->getAction()['uses'],
                'method' => $value->getActionMethod()
            ];
        }

        return $routes;
    }

    public function parseRouter(): array
    {
        $routes = $this->getRoutes();

        $parsedRoutes = [];

        foreach ($routes as $route) {
            if (isset($route['action']['uses'])) {
                $controllerString = explode('@', $route['action']['uses']);
                $controllerName = array_last(explode('\\', $controllerString[0]));

                $uri = str_replace(self::ROUTER_PREFIX, '', $route['uri']);

                $parsedRoutes[] = [
                    'http_method' => strtolower($route['method']),
                    'uri' => $uri,
                    'parameters' => $this->getRouteParameters($route['uri']),
                    'controller' => str_replace('Interface', 'Controller', $controllerName),
                    'method' => $controllerString[1]
                ];
            }
        }

        return $parsedRoutes;
    }

    public function parseLaravelRouter()
    {
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();
        $routes = [];
        /** @var Route $value */
        foreach ($routeCollection as $key => $value) {

            $routeAction = $value->getAction()['uses'];
            if (gettype($routeAction) === 'string') {
                $actionSplice = explode('@', $routeAction);
            }

            $routes[] = [
                'http_method' => strtolower($value->methods()[0]),
                'uri' => str_replace($value->getPrefix(), '', '/' . $value->uri),
                'parameters' => $value->parameterNames(),
                'controller' => str_replace('Interface', 'Controller', $actionSplice[0]),
                'method' => $value->getActionMethod()
            ];
        }

        return $routes;
    }

    public function getPathKeys(array $parsedRoutes): array
    {
        $pathKeys = [];

        foreach ($parsedRoutes as $parsedRoute) {
            $pathKeys[] = $parsedRoute['uri'];
        }
        $pathKeys = array_unique($pathKeys);

        return $pathKeys;
    }

    private function getRouteParameters(string $uri): array
    {
        $parameters = [];

        $routeParts = explode('/', $uri);
        foreach ($routeParts as $part) {
            if (strstr($part, '{') !== false) {
                $parameters[] = trim($part, '\{\}');
            }
        }

        return $parameters;
    }

    public function inPath(): array
    {
        $parsedRoutes = $this->parseRouter();

        $inPath = [];
        $controllers = [];
        foreach ($parsedRoutes as $parsedRoute) {
            $controllers[$parsedRoute['controller']] = [];
        }

        foreach ($controllers as $controller => $value) {
            $inPath[$controller] = $this->controllerMethodParameters($controller);
        }

        return $inPath;
    }

    public function controllerMethodParameters(string $controller)
    {
        $parsedRoutes = $this->parseLaravelRouter();

        $methods = [];

        foreach ($parsedRoutes as $parsedRoute) {
            if (strstr($parsedRoute['controller'], $controller)) {

                $methods[$parsedRoute['method']] = $parsedRoute['parameters'];
            }
        }

        return $methods;
    }
}