<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class AuthMiddleware {
   public function verifyToken($token_string_bez_bearer){
       error_log("[AuthMiddleware] verifyToken CALLED. Primljeni token (bez Bearer): '" . $token_string_bez_bearer . "'");

       if(empty($token_string_bez_bearer)) {
           error_log("[AuthMiddleware] Greška: Token je PRAZAN nakon skidanja Bearer prefiksa.");
           Flight::halt(401, json_encode(["message" => "Token is missing or empty."]));
           return FALSE; 
       }

       try {
           $jwt_secret = Config::JWT_SECRET();
           error_log("[AuthMiddleware] Koristi se JWT_SECRET: '" . $jwt_secret . "'");

           // Pokušaj dekodiranja
           $decoded_token = JWT::decode($token_string_bez_bearer, new Key($jwt_secret, 'HS256'));

           error_log("[AuthMiddleware] Token USPJEŠNO dekodiran. Dekodirani podaci: " . json_encode($decoded_token));

           if (!isset($decoded_token->user) || !is_object($decoded_token->user)) {
                error_log("[AuthMiddleware] Greška: Dekodirani token ne sadrži validan 'user' objekat/claim. Sadržaj tokena: " . json_encode($decoded_token));
                Flight::halt(401, json_encode(["message" => "Invalid token structure: missing user data."]));
                return FALSE;
           }

           Flight::set('user', $decoded_token->user); 
           Flight::set('jwt_token', $token_string_bez_bearer);
           error_log("[AuthMiddleware] User i jwt_token USPJEŠNO postavljeni u Flight. User ID: " . ($decoded_token->user->id ?? 'N/A'));
           return TRUE;

       } catch (ExpiredException $e) {
           error_log("[AuthMiddleware] ExpiredException: " . $e->getMessage() . " | Token: " . $token_string_bez_bearer);
           Flight::halt(401, json_encode(["message" => "Token has expired: " . $e->getMessage()]));
           return FALSE;
       } catch (SignatureInvalidException $e) {
           error_log("[AuthMiddleware] SignatureInvalidException: " . $e->getMessage() . " | Token: " . $token_string_bez_bearer);
           Flight::halt(401, json_encode(["message" => "Token signature verification failed: " . $e->getMessage()]));
           return FALSE;
       } catch (BeforeValidException $e) {
           error_log("[AuthMiddleware] BeforeValidException: " . $e->getMessage() . " | Token: " . $token_string_bez_bearer);
           Flight::halt(401, json_encode(["message" => "Token is not yet valid: " . $e->getMessage()]));
           return FALSE;
       } catch (\Exception $e) {
           error_log("[AuthMiddleware] Izuzetak prilikom dekodiranja tokena (Tip: " . get_class($e) . "): " . $e->getMessage() . " | Token: " . $token_string_bez_bearer);
           Flight::halt(401, json_encode(["message" => "Invalid token processing error: " . $e->getMessage()]));
           return FALSE;
       }
   }
   public function authorizeRole($requiredRole) {
       $user = Flight::get('user');
       if (!$user || !isset($user->role)) {
           error_log("[AuthMiddleware] authorizeRole: Korisnik nije postavljen ili nema 'role' property.");
           Flight::halt(403, 'Access denied: User data incomplete or not authenticated.');
           return FALSE;
       }
       if ($user->role !== $requiredRole) {
           error_log("[AuthMiddleware] authorizeRole: Odbijen pristup. Tražena uloga: {$requiredRole}, Korisnikova uloga: {$user->role}");
           Flight::halt(403, 'Access denied: insufficient privileges');
           return FALSE;
       }
       return TRUE;
   }
   public function authorizeRoles($roles) {
       $user = Flight::get('user');
       if (!$user || !isset($user->role)) {
        error_log("[AuthMiddleware] authorizeRoles: Korisnik nije postavljen ili nema 'role' property.");
        Flight::halt(403, 'Access denied: User data incomplete or not authenticated.');
        return FALSE;
       }
       if (!in_array($user->role, $roles)) {
        error_log("[AuthMiddleware] authorizeRoles: Odbijen pristup. Dozvoljene uloge: " . implode(",", $roles) . ", Korisnikova uloga: {$user->role}");
        Flight::halt(403, 'Forbidden: role not allowed');
        return FALSE;
       }
       return TRUE;
   }
   function authorizePermission($permission) {
       $user = Flight::get('user');
       if (!$user || !isset($user->permissions) || !is_array($user->permissions)) {
        error_log("[AuthMiddleware] authorizePermission: Korisnik nije postavljen ili nema validan 'permissions' niz.");
        Flight::halt(403, 'Access denied: User permissions data incomplete or not authenticated.');
        return FALSE;
       }
       if (!in_array($permission, $user->permissions)) {
        error_log("[AuthMiddleware] authorizePermission: Odbijen pristup. Tražena permisija: {$permission}, Korisnikove permisije: " . implode(",", $user->permissions));
        Flight::halt(403, 'Access denied: permission missing');
        return FALSE;
       }
       return TRUE;
   }   
}
