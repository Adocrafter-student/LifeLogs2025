<?php
/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="LifeLogs API",
 *   description="API documentation for LifeLogs",
 *   @OA\Contact(name="API Support", email="admin@lifelogs.com")
 * )
 * @OA\Server(
 *   url="http://localhost/LifeLogs2025",
 *   description="Local development server"
 * )
 */

// Osnovno podešavanje za prikazivanje grešaka - dobro za razvoj
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Učitavanje ključnih fajlova
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Config.php'; // Naš prilagođeni Config

// Učitavanje Middleware-a
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Učitavanje DAO i Servisa
// FlightPHP će ih instancirati kada se pozovu preko Flight::register()
// require_once __DIR__ . '/dao/BaseDao.php';      // Komentarisano - biće uključeno po potrebi unutar AuthDao ili drugih DAO-a
// require_once __DIR__ . '/dao/AuthDao.php';      // Komentarisano - biće uključeno po potrebi unutar AuthService-a

// require_once __DIR__ . '/services/BaseService.php'; // Komentarisano - biće uključeno po potrebi unutar AuthService-a (ako ga nasljeđuje)
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/UserService.php'; // UKLJUČUJEMO UserService.php
require_once __DIR__ . '/services/BlogService.php'; // Dodajemo BlogService

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ob_start(); // Pokreni output buffering

// Registracija servisa i middleware-a
Flight::register('auth_dao', 'AuthDao');
Flight::register('auth_service', 'AuthService');
Flight::register('auth_middleware', 'AuthMiddleware');

if (class_exists('UserService')) {
    Flight::register('userService', 'UserService');
}
if (class_exists('BlogService')) {
    Flight::register('blogService', 'BlogService');
}


// Globalni middleware za autentifikaciju (profesoricin stil)
Flight::route('/*', function() {
    $url = Flight::request()->url;
    $is_api_call = (strpos($url, '/api/') === 0);

    $excluded_paths = [
        '/api/auth/login',
        '/api/auth/register',
        // Možete dodati još javnih API putanja ako ih imate, npr.
        // '/api/blogs/public', // Ako imate javno listanje blogova
        // '/api/openapi' // Ako je OpenAPI specifikacija javna
    ];

    $is_excluded = false;
    foreach ($excluded_paths as $path) {

        if (strpos($url, $path) !== false && strpos($url, $path) === (strlen($url) - strlen($path) - (substr($url, -1) === '/' ? 1:0) ) ) {
             // Ovo je kompleksnija provjera, možda jednostavnije:
        } 
        // Jednostavnija provjera ako $url od Flighta daje relativnu putanju (npr. /api/auth/login)
        // I $excluded_paths su također relativne na app root
        if (strpos($url, $path) === 0) { // Vratimo se na originalnu jednostavnu provjeru
            $is_excluded = true;
            break;
        }
    }

    if ($is_api_call && !$is_excluded) {
        error_log("INDEX.PHP MIDDLEWARE: Pokušaj validacije tokena za URL: " . $url);
        try {
            $auth_header = Flight::request()->getHeader("Authorization");
            
            // FALLBACK: Pokušaj čitanja direktno iz $_SERVER ako Flight ne vrati header
            if (!$auth_header && isset($_SERVER['HTTP_AUTHORIZATION'])) {
                error_log("INDEX.PHP MIDDLEWARE: Flight::request()->getHeader('Authorization') je bio prazan. Koristim \$_SERVER['HTTP_AUTHORIZATION'].");
                $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
            }

            error_log("INDEX.PHP MIDDLEWARE: Dobijen Authorization header (nakon fallbacka): '" . ($auth_header ? $auth_header : 'STVARNO NIJE POSTAVLJEN ILI PRAZAN') . "'");

            if (!$auth_header) {
                 error_log("INDEX.PHP MIDDLEWARE: Greška - Missing Authorization header (čak i nakon fallbacka).");
                 Flight::halt(401, json_encode(['message' => "Missing Authorization header."]));
                 return FALSE; // Zaustavi dalje izvršavanje
            }
            
            $token = $auth_header; // Inicijalno postavi token na cijeli header
            if (stripos($auth_header, 'Bearer ') === 0) {
                $token = substr($auth_header, 7);
                error_log("INDEX.PHP MIDDLEWARE: Skinut 'Bearer ' prefiks. Token sada: '" . $token . "'");
            } else {
                error_log("INDEX.PHP MIDDLEWARE: Upozorenje - Authorization header NE POČINJE sa 'Bearer '. Header: '" . $auth_header . "'");
                // Ovdje biste mogli odlučiti da li da prekinete ili da tretirate cijeli header kao token
                // Flight::halt(401, json_encode(['message' => "Invalid Authorization header format. Missing Bearer prefix."]));
                // return FALSE;
            }

            if (empty($token)){
                error_log("INDEX.PHP MIDDLEWARE: Greška - Token je PRAZAN nakon obrade. Originalni header: '" . $auth_header . "'");
                Flight::halt(401, json_encode(['message' => "Token is empty after processing Authorization header."]));
                return FALSE;
            }
            error_log("INDEX.PHP MIDDLEWARE: Token za slanje u AuthMiddleware: '" . $token . "'");

            // TEST: Da li Flight može da instancira auth_middleware?
            $authMiddlewareInstance = null;
            try {
                $authMiddlewareInstance = Flight::auth_middleware();
                if ($authMiddlewareInstance instanceof AuthMiddleware) {
                    error_log("INDEX.PHP MIDDLEWARE: Flight::auth_middleware() USPJEŠNO vratio instancu AuthMiddleware.");
                } else {
                    error_log("INDEX.PHP MIDDLEWARE: Flight::auth_middleware() NIJE vratio instancu AuthMiddleware. Vratio je: " . gettype($authMiddlewareInstance));
                    Flight::halt(500, json_encode(['message' => 'Auth middleware service not correctly registered or loaded.']));
                    return FALSE;
                }
            } catch (\Exception $e) {
                error_log("INDEX.PHP MIDDLEWARE: GREŠKA prilikom Flight::auth_middleware(): " . $e->getMessage());
                Flight::halt(500, json_encode(['message' => 'Error accessing auth middleware service: ' . $e->getMessage()]));
                return FALSE;
            }

            // Sada pozovi metodu na instanci
            if ($authMiddlewareInstance->verifyToken($token)) {
                error_log("INDEX.PHP MIDDLEWARE: AuthMiddleware->verifyToken vratio TRUE za URL: " . $url);
                return TRUE;
            } else {
                // verifyToken bi trebao sam uraditi halt ako nije validan.
                // Ovaj halt je fallback ako verifyToken vrati false umjesto da haltuje ili ako je greška u komunikaciji.
                error_log("INDEX.PHP MIDDLEWARE: AuthMiddleware->verifyToken vratio FALSE ili nije uradio halt za URL: " . $url);
                Flight::halt(401, json_encode(['message' => "Token validation failed (returned false from middleware or error in middleware execution)."]));
                return FALSE;
            }
        } catch (\Exception $e) {
            error_log("INDEX.PHP MIDDLEWARE: Opšta Greška u middleware bloku za URL: " . $url . " - Error: " . $e->getMessage());
            Flight::halt(401, json_encode(['message' => "Token validation error (general): " . $e->getMessage()]));
            return FALSE;
        }
    } 
    return TRUE;
});

require_once __DIR__ .'/routes/api/auth.php'; // Auth rute (login, register, me)
require_once __DIR__ . '/routes/api/blogs.php'; // Odkomentarisano
// require_once __DIR__ . '/routes/api/users.php'; // Primjer za druge rute


// Serviranje frontend-a (index.html) za sve ostale GET zahtjeve koji nisu API
Flight::route('GET /*', function(){
    // Osiguraj da ovo ne presretne API pozive ako globalni '/*' middleware nije dobro podešen
    if (strpos(Flight::request()->url, '/api/') !== 0) {
        // clearAllOutputBuffers(); // Možda nije potrebno ako se baferiranje radi ispravno
        ob_clean(); // Očisti sve prethodne buffere
        header('Content-Type: text/html; charset=UTF-8');
        readfile(__DIR__ . '/index.html');
        exit(); // Važno da se prekine izvršavanje da ne bi došlo do 404 ili drugih problema
    }
    // Ako je API poziv stigao do ovdje, znači da nije uhvaćen ni od jedne API rute -> 404
    // To će Flight::notFound obraditi
});

// CORS Handling - ako već nije globalno postavljeno ili ako treba detaljnije
Flight::route('OPTIONS *', function(){
    ob_clean();
    header("Access-Control-Allow-Origin: *", true);
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS", true);
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Authentication, X-Requested-With", true);
    Flight::halt(200);
});


Flight::map('notFound', function(){
    ob_clean();
    header('Content-Type: application/json; charset=UTF-8');
    Flight::json(['error' => 'Endpoint not found', 'requested_url' => Flight::request()->url], 404);
});

Flight::start();