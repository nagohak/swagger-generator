<?php

namespace DEVJS\SwaggerGenerator\src;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ClassFinder
{

    public function __construct()
    {

    }

    public function getNamespaces(string $path)
    {
        $namespaces = [];
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0]) {
                    $index += 2; // Skip class keyword and whitespace
                    $namespaces[$tokens[$index][1]] = $namespace . '\\' . $tokens[$index][1];
//                    $namespaces[$namespace . '\\' . $tokens[$index][1]] = $tokens[$index][1];
                }
            }
        }

        foreach ($namespaces as $class => $namespace) {
            if (strpos($class, PHP_EOL) !== false) {
                unset($namespaces[$class]);
            }
        }

        return $namespaces;
    }

    public function getRepositoryNamespaces()
    {
        $repositoryNamespaces = $this->getNamespaces('app/Repositories');

        $result = [];

        foreach ($repositoryNamespaces as $key => $value) {
            $modelKey = str_replace('Repository', '', $key);
            $result[$modelKey] = $repositoryNamespaces[$key];
        }

        return $result;
    }

    public function getControllerNamespaces(string $path)
    {
        $namespaces = [];
        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0]) {
                    $index += 2; // Skip class keyword and whitespace
                    $namespaces[$namespace . '\\' . $tokens[$index][1]] = $tokens[$index][1];
                }
            }
        }

        foreach ($namespaces as $class => $namespace) {
            if (strpos($class, PHP_EOL) !== false) {
                unset($namespaces[$class]);
            }
        }

        return $namespaces;
    }
}