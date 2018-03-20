<?php

namespace DEVJS\SwaggerGenerator\src;

class BaseInfo
{
    const baseInfo = [
        'swagger' => '2.0',
        'info' => [
            'description' => 'This is a simple api documentation',
            'title' => 'Swagger',
            'termsOfService' => 'http://swagger.io/terms/',
            'contact' => [
                'name' => 'name',
                'url' => 'http://www.swagger.io/support',
                'email' => 'support@swagger.io',
            ],
            'license' => [
                'name' => 'Apache 2.0',
                'url' => 'http://www.apache.org/licenses/LICENSE-2.0.html',
            ],
            'version' => '1.0.0'
        ],
        'host' => 'petstore.swagger.io',
        'basePath' => '/v1',
    ];

    public function get(array $annotation)
    {
        $info = $this->parseArray($annotation, '@info');
        $host = $this->parseString($annotation, '@host');
        $basePath = $this->parseString($annotation, '@basePath');
        $baseInfo = [
            'swagger' => '2.0',
            'info' => $info,
            'host' => $host,
            'basePath' => $basePath,
        ];

        return $baseInfo;
    }

    private function parseArray(array $annotation, string $needle): array
    {
        $parsedArray = [];
        $attributeArray = [];

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '/(\)');
                $value = explode(',', $value);
                foreach ($value as $attribute) {
                    $attribute = explode('=', $attribute);
                    $attributeArray[trim($attribute[0])] = $attribute[1];
                }
                $parsedArray = $attributeArray;
            }
        }

        return $parsedArray;

    }

    private function parseString(array $annotation, string $needle)
    {
        $parsedString = '';

        foreach ($annotation as $line) {
            if (strstr($line, $needle)) {
                $value = str_replace($needle, '', $line);
                $value = trim($value, '()');
                $parsedString = $value;
            }
        }

        return $parsedString;
    }
}