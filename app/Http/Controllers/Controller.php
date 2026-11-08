<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Perpustakaan API',
    description: 'REST API Laravel 12 untuk login (Sanctum) dan CRUD data buku.',
    contact: new OA\Contact(name: 'Admin Perpustakaan', email: 'admin@example.com')
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'Server API')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum Token',
    description: 'Tempel token dari endpoint POST /api/login tanpa kata "Bearer".'
)]
#[OA\Tag(name: 'Auth', description: 'Registrasi, login, profil, dan logout')]
#[OA\Tag(name: 'Books', description: 'CRUD data buku (wajib login)')]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The title field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
            example: ['title' => ['The title field is required.']]
        ),
    ]
)]
#[OA\Schema(
    schema: 'MessageResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]
#[OA\Schema(
    schema: 'AuthResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Login berhasil'),
        new OA\Property(property: 'token', type: 'string', example: '1|your-sanctum-token'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
    ]
)]
abstract class Controller {}
