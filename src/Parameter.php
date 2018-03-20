<?php

namespace DEVJS\SwaggerGenerator\src;

class Parameter
{
    public function requestsInBody(array $allModelRequests): array
    {
        $requestsInBody = [];

        foreach ($allModelRequests as $name => $request) {
            $requestsInBody[$name] = [
                'in' => 'body',
                'name' => 'body',
                'description' => '',
                'required' => true,
                'schema' => ['$ref' => '#/definitions/' . $name]
            ];
        }

        return $requestsInBody;
    }
}