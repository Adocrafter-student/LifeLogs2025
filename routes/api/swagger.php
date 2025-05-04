<?php
/**
 * @OA\Info(
 *   title="LifeLogs API",
 *   version="1.0.0",
 *   description="API documentation for LifeLogs",
 *   @OA\Contact(name="API Support", email="admin@lifelogs.com")
 * )
 *
 * @OA\Server(
 *   url="http://localhost/LifeLogs2025/api",
 *   description="Local development server"
 * )
 *
 * # For each path your routes expose, add a PathItem:
 * @OA\PathItem(path="/blogs")
 * @OA\PathItem(path="/blogs/{id}")
 * @OA\PathItem(path="/blogs/user/{user_id}")
 * @OA\PathItem(path="/blogs/tag/{tag_id}")
 */