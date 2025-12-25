<?php

/**
 * PSR-4 autoloader for cpay library
 */

spl_autoload_register(function (string $class) {

    $prefix = 'cpay\\';
    $baseDir = __DIR__ . '/src/';

    // если класс не из нашей библиотеки — пропускаем
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // убираем префикс namespace
    $relativeClass = substr($class, strlen($prefix));

    // namespace → путь к файлу
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
