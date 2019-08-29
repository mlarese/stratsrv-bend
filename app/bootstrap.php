<?php

namespace Console;

/**
 * SPL autoloader
 */
spl_autoload_register(function ($class) {

    $explodedClass = explode('\\', $class);
    if($explodedClass[0] == __NAMESPACE__)
        unset($explodedClass[0]);
    if (!realpath(__DIR__ . '/src/Console/' . implode('/', $explodedClass) . '.php')) {

        require_once realpath(__DIR__ . '/src/Console/Exception/AutoloadClassNotFound.php');

        throw new \Console\Exception\AutoloadClassNotFound(sprintf(
            "The class `%s` was not found from autoloader",
            $class
        ));

    }
    require_once realpath(__DIR__ . '/src/Console/' . implode('/', $explodedClass) . '.php');
});