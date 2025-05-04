<?php
require __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Load & register every service in /services
foreach (glob(__DIR__ . '/services/*.php') as $file) {
    require $file;
    $class = pathinfo($file, PATHINFO_FILENAME);
    // e.g. UserService → userService
    Flight::register(lcfirst($class), $class);
}

// 2) Load all API route definitions
foreach (glob(__DIR__ . '/routes/api/*.php') as $route) {
    require $route;
}

// 3) Single catch-all for SPA vs API/auth
Flight::route('*', function() {
    $url = Flight::request()->url;
    // if URL starts with /api/ or /auth/login or /auth/register, let Flight handle it
    if (preg_match('#^/(api/|auth/(login|register))#', $url)) {
        return true;
    }

    // otherwise serve our index.html shell
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/index.html');
    return false;
});

Flight::start();
