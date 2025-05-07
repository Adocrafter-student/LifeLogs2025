<?php
require_once __DIR__ . '/vendor/autoload.php';

$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/docs/swagger-annotations.php',
    __DIR__ . '/routes/api/blogs.php',
    __DIR__ . '/routes/api/comments.php',
    __DIR__ . '/routes/api/likes.php',
    __DIR__ . '/routes/api/tags.php',
    __DIR__ . '/routes/api/users.php',
    __DIR__ . '/routes/api/auth.php',
    __DIR__ . '/routes/api/search.php'
]);

header('Content-Type: application/json');
echo $openapi->toJson();
