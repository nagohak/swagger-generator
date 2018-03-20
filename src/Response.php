<?php

namespace DEVJS\SwaggerGenerator;

class Response
{
    public function generateResponses(): array
    {
        $responses = [
            '200' => ['description' => 'OK'],
            '201' => ['description' => 'Created'],
            '202' => ['description' => 'Accepted'],
            '204' => ['description' => 'No content'],
            '400' => ['description' => 'Bad request'],
            '403' => ['description' => 'Restrict'],
            '404' => ['description' => 'Not found'],
            '422' => ['description' => 'Unprocessable Entity'],
        ];

        return $responses;
    }
}