<?php

namespace DEVJS\SwaggerGenerator\src;

class DefinitionParser
{
    private $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    public function parseRequests(array $classesRules)
    {
        $requests = [];
        foreach ($classesRules as $class => $classRules) {
            if (!empty($classRules)) {
                $requests[snake_case($class) . '_request'] = $this->parseRequest($classRules);
            }
        }

        return $requests;
    }

    public function parseRequest(array $classRules): array
    {
        $properties = [];
        $required = [];

        $typesAttributes = $this->property->parseAttributeTypesFromRules($classRules);
        $minimumAttributes = $this->property->parseAttributeMinimumFromRules($classRules);
        $maximumAttributes = $this->property->parseAttributeMaximumFromRules($classRules);
        $requiredAttributes = $this->property->parseAttributeRequiredFromRules($classRules);
        $enumAttributes = $this->property->parseAttributeEnumFromRules($classRules);

        foreach ($classRules as $attribute => $ruleString) {
            if (isset($typesAttributes[$attribute])) {
                $type = ['type' => $typesAttributes[$attribute]];

                $properties[$attribute] = $type;
            }

            if (isset($enumAttributes[$attribute])) {
                $enum = ['enum' => $enumAttributes[$attribute]];
                $properties[$attribute] = array_merge($properties[$attribute], $enum);
            }

            if (isset($minimumAttributes[$attribute])) {
                $min = ['minimum' => $minimumAttributes[$attribute]];
                $properties[$attribute] = array_merge($properties[$attribute], $min);
            }

            if (isset($maximumAttributes[$attribute])) {
                $max = ['maximum' => $maximumAttributes[$attribute]];
                $properties[$attribute] = array_merge($properties[$attribute], $max);
            }

            $required = array_keys($requiredAttributes);
        }

        if (!empty($required)) {
            $request = ['properties' => $properties, 'required' => $required, 'type' => 'object'];
        } else {
            $request = ['properties' => $properties, 'type' => 'object'];
        }

        return $request;
    }

    public function parseResponses(array $classesRules, array $relations): array
    {
        $responses = [];

        foreach ($classesRules as $class => $classRules) {
            if (!empty($relations[$class])) {
                $responses[snake_case($class) . '_response'] = $this->parseResponse($classRules, $relations[$class]);
            }
        }

        return $responses;
    }

    public function parseResponse(array $classRules, array $relations): array
    {
        $properties = [];
        $required = [];

        $typesAttributes = $this->property->parseAttributeTypesFromRules($classRules);
        $minimumAttributes = $this->property->parseAttributeMinimumFromRules($classRules);
        $maximumAttributes = $this->property->parseAttributeMaximumFromRules($classRules);
        $requiredAttributes = $this->property->parseAttributeRequiredFromRules($classRules);
        $enumAttributes = $this->property->parseAttributeEnumFromRules($classRules);

        foreach ($classRules as $attribute => $ruleString) {
            if (in_array(rtrim($attribute, '_id'), $relations)) {
                foreach ($relations as $relation) {
                    if (rtrim($attribute, '_id') === $relation) {
                        $properties[rtrim($attribute, '_id')] = ['$ref' => '#/definitions/' . $relation . '_response'];
                    }
                }
            } else {
                if (isset($typesAttributes[$attribute])) {
                    $type = ['type' => $typesAttributes[$attribute]];

                    $properties[$attribute] = $type;
                }

                if (isset($enumAttributes[$attribute])) {
                    $enum = ['enum' => $enumAttributes[$attribute]];
                    $properties[$attribute] = array_merge($properties[$attribute], $enum);
                }

                if (isset($minimumAttributes[$attribute])) {
                    $min = ['minimum' => $minimumAttributes[$attribute]];
                    $properties[$attribute] = array_merge($properties[$attribute], $min);
                }

                if (isset($maximumAttributes[$attribute])) {
                    $max = ['maximum' => $maximumAttributes[$attribute]];
                    $properties[$attribute] = array_merge($properties[$attribute], $max);
                }
            }

        }
        foreach (array_keys($requiredAttributes) as $key) {
            $required[] = rtrim($key, '_id');
        }

        if (!empty($required)) {
            $request = ['properties' => $properties, 'required' => $required, 'type' => 'object'];
        } else {
            $request = ['properties' => $properties, 'type' => 'object'];
        }

        return $request;
    }
}