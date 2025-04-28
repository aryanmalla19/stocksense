<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Stock Trading API",
 *     description="API documentation for Stock Trading App"
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api/v1",
 *     description="Local server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerDoc
{
    //
}