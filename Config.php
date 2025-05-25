<?php

// Set the reporting - može se staviti i u index.php ako se Config.php koristi samo za podatke
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED)); // E_ALL za razvoj je često bolje

class Config {

    private static function get_env($name, $default){
        // Provjerava da li je environment varijabla postavljena i nije prazan string
        // U XAMPP-u, environment varijable se mogu postaviti preko Apache konfiguracije (.htaccess ili httpd.conf)
        // ili ponekad preko PHP-FPM-a, ali za jednostavnost, često se oslanjamo na default vrijednosti ovdje.
        return isset($_ENV[$name]) && trim($_ENV[$name]) !== "" ? $_ENV[$name] : $default;
    }

    public static function DB_NAME() {
        return self::get_env("DB_NAME", "lifelogs"); // Vaše ime baze
    }
    public static function DB_PORT() {
        return self::get_env("DB_PORT", "3306"); // Default MySQL port
    }
    public static function DB_USER() {
        return self::get_env("DB_USER", "root"); // Vaš DB user
    }
    public static function DB_PASSWORD() {
        return self::get_env("DB_PASSWORD", ""); // Vaša DB lozinka
    }
    public static function DB_HOST() {
        return self::get_env("DB_HOST", "localhost"); // Vaš DB host
    }
    public static function JWT_SECRET() {
        // VAŽNO: Koristite jaku i jedinstvenu tajnu!
        // Ovo je samo primjer, zamijenite ga vašom tajnom koju ste već koristili ili novom.
        return self::get_env("JWT_SECRET", "cKX9gN@zL2mP7qR#sVbYwE&hJ1aD*oF"); 
    }
}

?> 