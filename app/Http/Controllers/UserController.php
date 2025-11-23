<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * Registration and creation of a new user
     */
    public function register(Request $request){
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
            'password.rquired' => 'Pole hasło jest wymagany.',
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
        }
        catch (\Exception $e) {
            return response()->json(['
                message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Login user
     */
    public function login(Request $request){
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
                    'token' => $token], 200);
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
    /**
     * Logout user
     */
    public function logout(Request $request){
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
    /**
     * Change user password
     */
    public function changePassword(Request $request){
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
