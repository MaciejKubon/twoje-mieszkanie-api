<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
class UserController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Rejestracja i utworzenie nowego użytkownika',
        tags: ['Użytkownik / Autoryzacja']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['first_name', 'last_name', 'email', 'password', 'confirm_password', 'role'],
            properties: [
                new OA\Property(property: 'first_name', type: 'string', example: 'Jan'),
                new OA\Property(property: 'last_name', type: 'string', example: 'Kowalski'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jan.kowalski@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Haslo123!'),
                new OA\Property(property: 'confirm_password', type: 'string', format: 'password', example: 'Haslo123!'),
                new OA\Property(property: 'role', type: 'string', enum: ['admin', 'owner', 'rentier'], example: 'owner')
                ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Użytkownik zarejestrowany pomyślnie',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Użytkownik zarejestrowany pomyślnie!'),
                new OA\Property(property: 'user', ref: '#/components/schemas/User')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Nieprawidłowe dane rejestracji',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Nieprawidłowe dane rejestracji.'),
                new OA\Property(property: 'error', type: 'object')
            ]
        )
    )]
    public function register(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(),[
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:admin,owner,rentier'
        ],
        [
            'email.required' => 'Pole email jest wymagany.',
            'email.email' => 'Pole email jest niepoprawny.',
            'email.unique'=> 'Email już istnieje',
            'first_name.required' => 'Pole imie jest wymagany.',
            'last_name.required' => 'Pole nazwisko jest wymagany.',
            'password.required' => 'Pole hasło jest wymagany.',
            'password.min' => 'Hasło musi mieć co najmniej :min znaków.',
            'password.letters' => 'Hasło musi zawierać przynajmniej jedną literę.',
            'password.mixed' => 'Hasło musi zawierać małe i duże litery.',
            'password.numbers' => 'Hasło musi zawierać przynajmniej jedną cyfrę.',
            'password.symbols' => 'Hasło musi zawierać przynajmniej jeden znak specjalny.',
            'confirm_password.required' => 'Pole password jest wymagany.',
            'confirm_password.same' => 'Hasła muszą być takie same',
            'role.required' => 'Pole role jest wymagany.',
            'role.in' => 'Pole role jest niepoprawny.',
        ]);

        if($validation->fails()){
            return response()->json([
                'message' => 'Nieprawodłowe dane rejestracji',
                'error'  => $validation->errors(),
            ], 400);
        }
        try {
            User::create([
                'first_name'=>$request->first_name,
                'last_name'=>$request->last_name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'role'=>$request->role
            ]);
            return response()->json([
                'message' => 'Użytkownik zarejestrowany pomyślnie',
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json(['
                message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Logowanie użytkownika',
        tags: ['Użytkownik / Autoryzacja']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jan.kowalski@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Haslo123!')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Logowanie powiodło się',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Logowanie powiodło się.'),
                new OA\Property(property: 'token', type: 'string', example: '1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
                new OA\Property(property: 'role', type: 'string', example: 'owner'),
                new OA\Property(property: 'user', ref: '#/components/schemas/User')

            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Nieprawidłowe dane logowania',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Nieprawidłowe dane logowania.'),
                new OA\Property(property: 'error', type: 'string', example: 'Wprowadzony email lub hasło jest niepoprawne.')
            ]
        )
    )]
    public function login(Request $request): JsonResponse
    {

        $validation = Validator::make($request->all(),[
            'email' => ['required', 'email'],
            'password' => ['required'],
        ],
        [
            'email.required' => 'Email jest wymagany.',
            'email.email' => 'Email jest niepoprawny.',
            'password.required' => 'Hasło jest wymagane'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Nieprawodłowe dane logowania',
                'error'  => $validation->errors(),
            ], 400);
        }
        $credentials = $request->only('email', 'password');

        try{
            if(Auth::attempt($credentials)) {
                $user = Auth::User();
                $token = $user->createToken('authToken')->plainTextToken;
                return response()->json([
                    'message' => 'Logowanie powiodło się',
                    'token' => $token,
                    'role'=> $user->role,
                    'fiest_name'=> $user->first_name,
                    'last_name'=> $user->last_name
                ], 200);
            }
            return response()->json([
                'message' => 'Nieprawidłowe dane logowania',
                'error' => 'Nieprawidłowe dane logowania'], 401);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Wylogowanie użytkownika (wymaga tokenu)',
        security: [['bearerAuth' => []]],
        tags: ['Użytkownik / Autoryzacja']
    )]
    #[OA\Response(
        response: 200,
        description: 'Wylogowano pomyślnie',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Wylogowano pomyślnie.')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Brak autoryzacji (nie podano tokenu)'
    )]
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Wylgowano pomyślnie']);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/change-password',
        summary: 'Zmiana hasła użytkownika (wymaga tokenu)',
        security: [['bearerAuth' => []]],
        tags: ['Użytkownik / Autoryzacja']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['current_password', 'password', 'confirm_password'],
            properties: [
                new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'StareHaslo123!'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NoweHaslo456!'),
                new OA\Property(property: 'confirm_password', type: 'string', format: 'password', example: 'NoweHaslo456!')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Hasło zostało pomyślnie zaktualizowane',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Hasło zostało pomyślnie zaktualizowane!')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Nieprawidłowe dane (walidacja)'
    )]
    public function changePassword(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(),[
            'current_password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'confirm_password' => 'required|same:password',
        ],[
            'current_password.required' => 'Pole password jest wymagany.',
            'current_password.password' => 'Pole password jest niepoprawny.',
            'password.rquired' => 'Pole hasło jest wymagany.',
            'password.min' => 'Hasło musi mieć co najmniej :min znaków.',
            'password.letters' => 'Hasło musi zawierać przynajmniej jedną literę.',
            'password.mixed' => 'Hasło musi zawierać małe i duże litery.',
            'password.numbers' => 'Hasło musi zawierać przynajmniej jedną cyfrę.',
            'password.symbols' => 'Hasło musi zawierać przynajmniej jeden znak specjalny.',
            'confirm_password.required' => 'Pole password jest wymagany.',
            'confirm_password.same' => 'Hasła muszą być takie same',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Nieprawodłowe dane podczas zmiany hasła',
                'error'  => $validation->errors(),
            ], 400);
        }
        try{
            $user = auth()->user();
            User::where('id',$user->id)->update([
                'password'=>Hash::make($request->password),
            ]);
            return response()->json([
                'message' => 'Profil został zaktualizowany!',
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['
                message' => 'Nie udało się zaktualizować hasła',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
