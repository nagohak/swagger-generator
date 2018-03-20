<?php

namespace DEVJS\SwaggerGenerator;

use function Couchbase\defaultDecoder;
use ReflectionClass;

class Annotation
{
    private $routerParser;

    public function __construct(RouterParser $routerParser)
    {
        $this->routerParser = $routerParser;
    }

    public function parse(string $controllerNamespace): array
    {
        $classAnnotation = $this->getFromClass($controllerNamespace);
        $methodsAnnotation = $this->getFromMethods($controllerNamespace);

        $controller = array_last(explode('\\', $controllerNamespace));
        $model = str_replace('Controller', '', $controller);

        $controllerMethodParameters = $this->routerParser->controllerMethodParameters($controller);

        $result = [];

        foreach ($methodsAnnotation as $method => $annotation) {
            $tags = $this->parseTags($classAnnotation);
            $summary = $this->parseSummary($annotation, $model);
            $description = $this->parseDescription($annotation);
            $consumes = $this->parseConsumes($annotation);
            $produces = $this->parseProduces($annotation);
            $responses = $this->parseResponses($annotation, $model);

            if (isset($controllerMethodParameters[$method])) {
                $parameters = $this->parseParameters($classAnnotation, $annotation, $model, $controllerMethodParameters[$method]);
            } else {
                $parameters = [];
            }
            $result[$method] = [
                'tags' => $tags,
                'summary' => $summary,
                'description' => $description,
                'consumes' => $consumes ?: ['application/json'],
                'produces' => $produces ?: ['application/json'],
                'parameters' => $parameters,
                'responses' => $responses
            ];
        }

        return $result;
    }

    public function getFromClass(string $controllerNamespace)
    {
        $controller = New ReflectionClass($controllerNamespace);
        $doc = $controller->getDocComment();
        $annotation = $this->docToArray($doc);

        return $annotation;
    }

    public function getFromMethods(string $classNamespace): array
    {
        $annotations = [];

        $class = new ReflectionClass($classNamespace);
        $methods = $class->getMethods();

        foreach ($methods as $method) {
            if ($method->hasReturnType()) {
                $annotations[$method->getName()] = $this->docToArray($method->getDocComment());
            }
        }

        return $annotations;
    }

    private function docToArray(string $docComment): array
    {
        $comment = preg_split('/(\n|\r\n)/', $docComment);
        $comment[0] = preg_replace('/[ \t]*\\/\*\*/', '', $comment[0]); // strip '/**'

        $i = count($comment) - 1;
        $comment[$i] = preg_replace('/\*\/[ \t]*$/', '', $comment[$i]); // strip '*/'
        $lines = [];

        foreach ($comment as $line) {
            if (!empty($line)) {
                $line = ltrim($line, "\"\t *");
                $lines[] = $line;
            }
        }

        return $lines;
    }

    private function parseSummary(array $annotation, string $model): string
    {
        $summary = '';
        $needle = '@summary';

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '/(\)');

                if (strstr($value, '%model%')) {
                    $summary = str_replace('%model%', snake_case($model), $value);
                } else {
                    $summary = $value;
                }
            }
        }

        return $summary;
    }

    private function parseDescription(array $annotation): string
    {
        $description = '';
        $needle = '@description';

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '/(\)');

                $description = $value;

            }
        }

        return $description;
    }

    private function parseConsumes(array $annotation): array
    {
        $consumes = [];
        $needle = '@consumes';

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '/(\)');
                $consumes = explode(',', $value);
            }
        }

        return $consumes;
    }

    private function parseProduces(array $annotation): array
    {
        $produces = [];
        $needle = '@produces';

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '/(\)');
                $produces = explode(',', $value);
            }
        }

        return $produces;
    }

    private function parseResponses(array $annotation, string $model): array
    {
        $responses = [];
        $needle = '@response';

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '\(\)');
                if (strstr($value, '%model%')) {
                    $value = str_replace('%model%', snake_case($model), $value);
                }
                $value = json_decode($value, true);
                foreach ($value as $name => $content) {
                    $responses[$name] = $content;
                }

            }
        }

        return $responses;
    }

    private function parseParameters(array $classAnnotation, array $methodAnnotation, string $model, array $inPath): array
    {
        $commonParameters = $this->parseCommonParameters($methodAnnotation, $model);
        $customParameters = $this->parsePathParameters($classAnnotation, $inPath);

        $parameters = array_merge($customParameters, $commonParameters);

        return $parameters;
    }

    public function parseCommonParameters(array $methodAnnotation, string $model): array
    {
        $parameters = [];
        $needle = '@parameter';

        foreach ($methodAnnotation as $annotationLine) {
            if (strstr($annotationLine, $needle)) {
                $annotationLine = str_replace($needle, '', $annotationLine);
                $parameterJson = trim($annotationLine, '\(\)');
                if (strstr($parameterJson, '%model%')) {
                    $parameterJson = str_replace('%model%', snake_case($model), $parameterJson);
                }
                $parameterArray = json_decode($parameterJson, true);
                $parameters[] = $parameterArray;
            }
        }

        return $parameters;
    }

    public function parsePathParameters(array $classAnnotation, array $pathParameter): array
    {
        $parameters = [];
        $needle = '@parameter';

        foreach ($classAnnotation as $annotationLine) {
            if (strstr($annotationLine, $needle)) {
                foreach ($pathParameter as $item) {
                    if (strstr($annotationLine, $item)) {
                        $annotationLine = str_replace($needle, '', $annotationLine);
                        $parameterJson = trim($annotationLine, '/(/)');
                        $parameterArray = json_decode($parameterJson, true);
                            $parameters[] = $parameterArray;
                    }
                }

            }
        }

        return array_unique($parameters, SORT_REGULAR);
    }

    private function parseTags(array $classAnnotation): array
    {
        $tags = [];
        $needle = '@tags';

        foreach ($classAnnotation as $annotationLine) {
            if (strstr($annotationLine, $needle)) {
                $annotationLine = str_replace($needle, '', $annotationLine);
                $annotationLine = trim($annotationLine, '()');
                $annotationArray = explode(',', $annotationLine);
                foreach ($annotationArray as $tag) {
                    $tags[] = trim($tag);
                }
            }
        }

        return $tags;
    }
}