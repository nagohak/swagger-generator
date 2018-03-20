<?php

namespace DEVJS\SwaggerGenerator;

use ReflectionClass;

class Property
{
    public function fillProperties(array $namespaces, string $property): array
    {
        $properties = [];
        foreach ($namespaces as $class => $namespace) {
            $properties[$class] = $this->getClassPropertyValues($namespace, $property);
        }

        return $properties;
    }

    private function getClassPropertyValues(string $className, string $propertyName): array
    {
        $class = new ReflectionClass($className);

        if (!$class->hasProperty($propertyName)) {
            return [null];
        }
        $properties = $class->getDefaultProperties();
        $property = $properties[$propertyName];

        return $property;
    }

    public function parseAttributeTypesFromRules(array $classRules): array
    {
        $attributeTypes = [];

        foreach ($classRules as $attribute => $ruleString) {
            if (isset($attribute)) {
                if (strstr($ruleString, 'numeric')) {
                    $attributeTypes[$attribute] = 'integer';
                } elseif (strstr($ruleString, 'bool')) {
                    $attributeTypes[$attribute] = 'boolean';
                } elseif (strstr($ruleString, 'string')) {
                    $attributeTypes[$attribute] = 'string';
                } else {
                    $attributeTypes[$attribute] = 'string';
                }
            }
        }

        return $attributeTypes;
    }

    public function parseAttributeEnumFromRules(array $classRules): array
    {
        $enums = [];
        $tokens = [];
        $needle = 'in:';

        foreach ($classRules as $attribute => $ruleString) {
            if (isset($attribute)) {
                $tokens[$attribute] = explode('|', $ruleString);
                foreach ($tokens[$attribute] as $token) {
                    if (preg_match("/\b$needle\b/i", $token)) {
                        $enums[$attribute] = explode(',', ltrim($token, 'in:'));
                    }
                }
            }
        }

        return $enums;
    }

    public function parseAttributeRequiredFromRules(array $classRules): array
    {
        $required = [];

        foreach ($classRules as $attribute => $ruleString) {
            if (isset($attribute)) {
                if (strstr($ruleString, 'required')) {
                    $required[$attribute] = 'required';
                }
            }
        }

        return $required;
    }

    public function parseAttributeMinimumFromRules(array $classRules)
    {
        $tokens = [];
        $minimum = [];

        foreach ($classRules as $attribute => $ruleString) {
            if (isset($attribute)) {
                $tokens[$attribute] = explode('|', $ruleString);
                foreach ($tokens[$attribute] as $token) {
                        if (strstr($token, 'min:')) {
                            $minimum[$attribute] = (int)ltrim($token, 'min:');
                        }
                    }
                }
            }

        return $minimum;
    }

    public function parseAttributeMaximumFromRules(array $classRules)
    {
        $tokens = [];
        $maximum = [];

        foreach ($classRules as $attribute => $ruleString) {
            if (isset($attribute)) {
                $tokens[$attribute] = explode('|', $ruleString);
                foreach ($tokens[$attribute] as $token) {
                    if (strstr($token, 'max:')) {
                        $maximum[$attribute] = (int)ltrim($token, 'max:');
                    }
                }
            }
        }

        return $maximum;
    }
}