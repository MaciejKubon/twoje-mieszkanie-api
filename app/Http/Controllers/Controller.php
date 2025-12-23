<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Dokumentacja API dla serwisu Twoje Mieszkanie',
    title: 'Tytuł Twojego API - Twoje Mieszkanie'
)]

#[OA\Tag(
    name: 'Użytkownik / Autoryzacja',
    description: 'Operacje związane z kontem użytkownika i logowaniem.'
)]

#[OA\Tag(
    name: 'Obiekty',
    description: 'Operacje związane z zarządzaniem nieruchomościami.'
)]

#[OA\Tag(
    name: 'Wiadomości',
    description: 'Operacje związane z wiadomościami i konwersacjami.'
)]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Uwierzytelnienie za pomocą tokena Bearer',
    name: 'bearerAuth',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]


abstract class Controller
{
    //
}
