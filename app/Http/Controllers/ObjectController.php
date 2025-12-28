<?php

namespace App\Http\Controllers;

use App\Models\Objects;
use App\Models\User;
use App\Rules\PostalCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ObjectController extends Controller
{
    #[OA\Get(
        path: '/api/objects',
        summary: 'Pobiera listę obiektów użytkownika',
        security: [['bearerAuth' => []]],
        tags: ['Obiekty']
    )]
    #[OA\Response(
        response: 200,
        description: 'Zwraca listę obiektów',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'objects',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Objects')
                )
            ]
        )
    )]
    #[OA\Response(response: 500, description: 'Błąd serwera.')]
    public function index(): JsonResponse
    {
        try{
            $user = auth()->user();
            $objects = Objects::where('id_owner', $user->id)->get();
            return response()->json([
                'objects' => $objects,
            ]);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/objects',
        summary: 'Tworzy nowy obiekt',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type_of_building', 'country', 'voivodeship', 'city', 'zip_code', 'street', 'house_number'],
                properties: [
                    new OA\Property(property: 'type_of_building', type: 'string', enum: ['house', 'apartment', 'room']),
                    new OA\Property(property: 'country', type: 'string', example: 'Polska'),
                    new OA\Property(property: 'voivodeship', type: 'string', example: 'Mazowieckie'),
                    new OA\Property(property: 'city', type: 'string', example: 'Warszawa'),
                    new OA\Property(property: 'zip_code', type: 'string', example: '00-001'),
                    new OA\Property(property: 'street', type: 'string', example: 'Wiejska'),
                    new OA\Property(property: 'house_number', type: 'string', example: '10'),
                    new OA\Property(property: 'apartment_number', type: 'string', example: '5', nullable: true),
                ]
            )
        ),
        tags: ['Obiekty']
    )]
    #[OA\Response(response: 200, description: 'Obiekt został dodany')]
    #[OA\Response(response: 400, description: 'Błąd walidacji')]
    #[OA\Response(response: 403, description: 'Brak uprawnień')]
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->can('create-object')){
            abort(403);
        }
        try{
            $validation = Validator::make($request->all(), [
                'type_of_building' => 'required|in:house,apartment,room',
                'country'          => 'required|string|max:255',
                'voivodeship'      => 'required|string|max:255',
                'city'             => 'required|string|max:255',
                'zip_code'         => ['required', new PostalCode()],
                'street'           => 'required|string|max:255',
                'house_number'     => 'required|string|max:5',
                'apartment_number' => 'nullable|string|max:5',

            ], [
                'type_of_building.required' => 'Typ budynku jest wymagany.',
                'type_of_building.in'       => 'Typ budynku musi być jednym z: dom, mieszkanie lub pokój.',
                'country.required' => 'Pole kraj jest wymagane.',
                'country.string'   => 'Pole kraj musi być tekstem.',
                'country.max'      => 'Pole kraj może mieć maksymalnie :max znaków.',
                'voivodeship.required' => 'Pole województwo jest wymagane.',
                'voivodeship.string'   => 'Pole województwo musi być tekstem.',
                'voivodeship.max'      => 'Pole województwo może mieć maksymalnie :max znaków.',
                'city.required' => 'Pole miasto jest wymagane.',
                'city.string'   => 'Pole miasto musi być tekstem.',
                'city.max'      => 'Pole miasto może mieć maksymalnie :max znaków.',
                'zip_code.required' => 'Pole kod pocztowy jest wymagane.',
                'street.required' => 'Pole ulica jest wymagane.',
                'street.max'      => 'Pole ulica może mieć maksymalnie :max znaków.',
                'house_number.required' => 'Pole numer domu jest wymagane.',
                'house_number.string'   => 'Pole numer domu musi być tekstem.',
                'house_number.max'      => 'Pole numer domu może mieć maksymalnie :max znaków.',
                'apartment_number.string' => 'Pole numer mieszkania musi być tekstem.',
                'apartment_number.max'    => 'Pole numer mieszkania może mieć maksymalnie :max znaków.',
            ]);
            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Nie udało się utworzyć obiektu',
                    'error'  => $validation->errors(),
                ], 400);
            }
            $data = $request->only([
                'type_of_building', 'country', 'voivodeship', 'city', 'zip_code',
                'street', 'house_number', 'apartment_number'
            ]);
            $data['id_owner'] = $user->id;
            if (!$request->filled('apartment_number')) {
                $data['apartment_number'] = null;
            }
            Objects::create($data);
            return response()->json([
                'message' => 'Obiekt został dodany',
            ], 200);

        }catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ]);
        }
    }

    #[OA\Get(
        path: '/api/objects/{id}',
        summary: 'Wyświetla szczegóły konkretnego obiektu',
        security: [['bearerAuth' => []]],
        tags: ['Obiekty'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ]
    )]
    #[OA\Response(response: 200, description: 'Szczegóły obiektu')]
    #[OA\Response(response: 404, description: 'Obiekt nie został znaleziony')]
    public function show(Objects $objects): JsonResponse
    {
        try {
            return response()->json([
                'object' => $objects,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidzany bład ',
                'error' => $e->getMessage()
            ]);
        }
    }

    #[OA\Put(
        path: '/api/objects/{id}',
        summary: 'Aktualizuje istniejący obiekt',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type_of_building', type: 'string', enum: ['house', 'apartment', 'room']),
                    new OA\Property(property: 'country', type: 'string'),
                    new OA\Property(property: 'city', type: 'string'),
                ]
            )
        ),
        tags: ['Obiekty'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ]
    )]
    #[OA\Response(response: 200, description: 'Obiekt został zaktualizowany')]
    #[OA\Response(response: 403, description: 'Brak dostępu')]
    public function update(Request $request, $id): JsonResponse
    {

        $object = Objects::findOrFail($id);

        if (Gate::denies('update-object', $object)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }

            $validation = Validator::make($request->all(), [
                'type_of_building' => 'sometimes|in:house,apartment,room',
                'country'          => 'sometimes|string|max:255',
                'voivodeship'      => 'sometimes|string|max:255',
                'city'             => 'sometimes|string|max:255',
                'zip_code'         => ['sometimes','required', new PostalCode()],
                'street'           => 'sometimes|string|max:255',
                'house_number'     => 'sometimes|string|max:5',
                'apartment_number' => 'sometimes|string|max:5',

            ], [
                'type_of_building.required' => 'Typ budynku jest wymagany.',
                'type_of_building.in'       => 'Typ budynku musi być jednym z: dom, mieszkanie lub pokój.',
                'country.sometimes' => 'Pole kraj jest wymagane.',
                'country.string'   => 'Pole kraj musi być tekstem.',
                'country.max'      => 'Pole kraj może mieć maksymalnie :max znaków.',
                'voivodeship.sometimes' => 'Pole województwo jest wymagane.',
                'voivodeship.string'   => 'Pole województwo musi być tekstem.',
                'voivodeship.max'      => 'Pole województwo może mieć maksymalnie :max znaków.',
                'city.sometimes' => 'Pole miasto jest wymagane.',
                'city.string'   => 'Pole miasto musi być tekstem.',
                'city.max'      => 'Pole miasto może mieć maksymalnie :max znaków.',
                'zip_code.sometimes' => 'Pole kod pocztowy jest wymagane.',
                'street.sometimes' => 'Pole ulica jest wymagane.',
                'street.max'      => 'Pole ulica może mieć maksymalnie :max znaków.',
                'house_number.sometimes' => 'Pole numer domu jest wymagane.',
                'house_number.string'   => 'Pole numer domu musi być tekstem.',
                'house_number.max'      => 'Pole numer domu może mieć maksymalnie :max znaków.',
                'apartment_number.string' => 'Pole numer mieszkania musi być tekstem.',
                'apartment_number.max'    => 'Pole numer mieszkania może mieć maksymalnie :max znaków.',
            ]);
            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Nie udało się utworzyć obiektu',
                    'error'  => $validation->errors(),
                ], 400);
            }
        try{
            $object->update($request->all());
            return response()->json([
                'message' => 'Obiekt został zaktualizowany.',
                'data'=> $object
            ], 200);
        }catch(\Exception $e){
            return response()->json(['
                message' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    #[OA\Delete(
        path: '/api/objects/{id}',
        summary: 'Usuwa obiekt',
        security: [['bearerAuth' => []]],
        tags: ['Obiekty'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ]
    )]
    #[OA\Response(response: 200, description: 'Obiekt został usunięty')]
    #[OA\Response(response: 403, description: 'Brak dostępu')]
    public function destroy(Objects $objects): JsonResponse
    {
        if (Gate::denies('delete-object', $objects)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        try {
            $objects->delete();
            return response()->json([
                'message' => 'Obiekt zosatł usunity',

            ],200);
        }catch ( \Exception $e){
            return response()->json(['
                message' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
