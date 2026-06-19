<?php

/**
 * Роутер для встроенного веб-сервера PHP.
 * Нужен как обходной путь: «php artisan serve» падает, когда путь к проекту
 * содержит кириллицу (ломается кодировка аргумента роутера).
 * Запуск:  php -S 127.0.0.1:8000 server.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Отдаём существующие статические файлы как есть.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
