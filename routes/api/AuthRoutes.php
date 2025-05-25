<?php
/**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Login user",
 *     tags={"auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string", format="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */
Flight::route('POST /auth/login', function() {
    $data = json_decode(Flight::request()->getBody(), true);
    
    try {
        $result = Flight::auth_service()->login($data['email'], $data['password']);
        Flight::json($result);
    } catch (Exception $e) {
        Flight::halt(401, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/auth/register",
 *     summary="Register new user",
 *     tags={"auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username","email","password"},
 *             @OA\Property(property="username", type="string"),
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string", format="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Registration successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or user already exists"
 *     )
 * )
 */
Flight::route('POST /auth/register', function() {
    $data = json_decode(Flight::request()->getBody(), true);
    
    try {
        $result = Flight::auth_service()->register(
            $data['username'],
            $data['email'],
            $data['password']
        );
        Flight::json($result);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
}); 