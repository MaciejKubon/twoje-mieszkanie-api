<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Tytuł Twojego API - Twoje Mieszkanie",
 * description="Dokumentacja API dla serwisu Twoje Mieszkanie"
 * )
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * in="header",
 * name="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Uwierzytelnienie za pomocą tokena Bearer"
 * )
 */
abstract class Controller
{
    //
}
