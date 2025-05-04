<?php
/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="LifeLogs API",
 *         description="API documentation for LifeLogs application",
 *         @OA\Contact(
 *             email="support@lifelogs.com"
 *         )
 *     ),
 *     @OA\Server(
 *         description="API server",
 *         url="http://localhost/api"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT"
 *         ),
 *         @OA\Schema(
 *             schema="Error",
 *             @OA\Property(property="error", type="string")
 *         ),
 *         @OA\Schema(
 *             schema="Success",
 *             @OA\Property(property="message", type="string")
 *         )
 *     )
 * )
 */