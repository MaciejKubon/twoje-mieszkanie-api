<?php

namespace App\Http\Controllers;

use App\Models\FullRent;
use App\Rules\FullRentDateRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;


class FullRentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $userId = $user->id;
            $perPage = $request->query('per_page', 5);
            $sortBy  = $request->query('sort_by', 'date');
            $order   = $request->query('order', 'desc');
            $order   = strtolower($order) === 'asc' ? 'asc' : 'desc';
            $dbFields = ['id', 'amount', 'date', 'is_paid', 'is_accepted'];
            $safeSortBy = in_array($sortBy, $dbFields) ? $sortBy : 'date';

            $query = FullRent::query();
            if ($user->role == 'owner') {
                $query->whereHas('rentAssignment.objectInRentAssigment', function ($q) use ($userId) {
                    $q->where('id_owner', $userId);
                })->with(['rentAssignment.objectInRentAssigment', 'rentAssignment.renter']);

                $mapCallback = function ($rent) {
                    $assignment = $rent->rentAssignment;
                    $object = $assignment ? $assignment->objectInRentAssigment : null;
                    $renter = $assignment ? $assignment->renter : null;
                    return [
                        'id'           => $rent->id,
                        'amount'       => $rent->amount,
                        'date'         => $rent->date,
                        'is_paid'      => (bool)$rent->is_paid,
                        'object_name'  => $object->name ?? 'Brak nazwy',
                        'renter_email' => $renter->email ?? null,
                        'renter_name'  => $renter ? ($renter->first_name . ' ' . $renter->last_name) : null,
                        'is_accepted'  => $rent->is_accepted ?? null,
                    ];
                };

            } else if ($user->role == 'rentier') {
                $query->whereHas('rentAssignment.objectInRentAssigment', function ($q) use ($userId) {
                    $q->where('id_renter', $userId);
                })->with(['rentAssignment.objectInRentAssigment.owner']);

                $mapCallback = function ($rent) {
                    $assignment = $rent->rentAssignment;
                    $object = $assignment ? $assignment->objectInRentAssigment : null;
                    $owner = $object ? $object->owner : null;
                    return [
                        'id'               => $rent->id,
                        'amount'           => $rent->amount,
                        'date'             => $rent->date,
                        'is_paid'          => (bool) $rent->is_paid,
                        'object_name'      => $object->name ?? 'Brak nazwy',
                        'owner_first_name' => $owner->first_name ?? null,
                        'owner_last_name'  => $owner->last_name ?? null,
                        'owner_email'      => $owner->email ?? null,
                        'is_accepted'      => $rent->is_accepted ?? null,
                    ];
                };
            } else {
                return response()->json(['data' => [], 'message' => 'Brak uprawnień'], 403);
            }
            $paginatedRents = $query->orderBy($safeSortBy, $order)->paginate($perPage);
            $transformedData = $paginatedRents->getCollection()->map($mapCallback);
            $paginatedRents->setCollection($transformedData);

            return response()->json($paginatedRents, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    #[OA\Post(
        path: '/api/full-rents',
        summary: 'Tworzy nowy czynsz',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FullRent')
        ),
        tags: ['Czynsz'],
        responses: [
            new OA\Response(response: 200, description: 'Czynsz został utworzony'),
            new OA\Response(response: 400, description: 'Błąd walidacji'),
            new OA\Response(response: 403, description: 'Brak dostępu')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'id_rent_assigment' => 'required|exists:rent_assigment,id',
            'amount' => 'required|numeric|min:0.1',
            'date' => ['required', new FullRentDateRequest()]
        ],[
            'id_rent_assigment.required' => 'Pole id_rent_assigment jest wymagany.',
            'id_rent_assigment.exists'=> 'Wybrana umowa najmu nie istnieje',
            'amount.required' => 'Pole amount jest wymagane.',
            'amount.numeric'=>'Pole amount musi być liczba',
            'amount.min'=>'Pole musi być większe od 0.1',
            'date.required' => 'Pole data jest wymagane.',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Nie udało się utworzyć czynszu',
                'error'  => $validation->errors(),
            ], 400);
        }
        if(Gate::denies('create-fullRent', $request->id_rent_assigment)){
         abort(403, 'Brak dostępu.');
        }
        try{
            FullRent::create(
                $request->only(['id_rent_assigment', 'amount', 'date']));
            return response()->json([
                'message' => 'Czynsz został utworzony',
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ]);
        }
    }

    #[OA\Get(
        path: '/api/full-rents/{id}',
        summary: 'Wyświetla szczegóły czynszu',
        security: [['bearerAuth' => []]],
        tags: ['Czynsz'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dane czynszu'),
            new OA\Response(response: 403, description: 'Brak dostępu'),
            new OA\Response(response: 404, description: 'Nie znaleziono zasobu')
        ]
    )]
    public function show(FullRent $fullRent): JsonResponse
    {
//        if(Gate::denies('show-fullRent', $fullRent)){
//            abort(403, 'Brak dostępu.');
//        }
        $user = auth()->user();
        $userId = $user->id;
        if($user->role == 'owner'){
            $fullRents = FullRent::query()
                ->whereHas('rentAssignment.objectInRentAssigment', function ($query) use ($userId) {
                    $query->where('id_owner', $userId);
                })
                ->with([
                    'rentAssignment.objectInRentAssigment',
                    'rentAssignment.renter'
                ])
                ->get();
            $result = $fullRents->map(function ($rent) {
                $assignment = $rent->rentAssignment;
                $object = $assignment ? $assignment->objectInRentAssigment : null;
                $renter = $assignment ? $assignment->renter : null;
                return [
                    'id'               => $rent->id,
                    'amount'           => $rent->amount,
                    'date'             => $rent->date,
                    'is_paid'          => (bool) $rent->is_paid,
                    'id_object'        => $object -> id,
                    'object_name'      => $object->name ?? 'Brak nazwy',
                    'renter_email' => $renter->email ?? null,
                    'renter_name' => $renter ? ($renter->first_name . ' ' . $renter->last_name) : null,
                    'is_accepted'      => $rent->is_accepted ?? null,
                ];
            });
        }else if($user->role == 'rentier'){
            $fullRents = FullRent::query()
                ->whereHas('rentAssignment.objectInRentAssigment', function ($query) use ($userId) {
                    $query->where('id_renter', $userId);
                })
                ->with([
                    'rentAssignment.objectInRentAssigment',
                    'rentAssignment.objectInRentAssigment.owner'
                ])
                ->get();
            $result = $fullRents->map(function ($rent) {
                $assignment = $rent->rentAssignment;
                $object = $assignment ? $assignment->objectInRentAssigment : null;
                $owner = $object ? $object->owner : null;
                return [
                    'id'               => $rent->id,
                    'amount'           => $rent->amount,
                    'date'             => $rent->date,
                    'is_paid'          => (bool) $rent->is_paid,
                    'object_name'      => $object->name ?? 'Brak nazwy',
                    'owner_first_name' => $owner->first_name ?? null,
                    'owner_last_name'  => $owner->last_name ?? null,
                    'owner_email'      => $owner->email ?? null,
                    'date_paid'        => $rent->date_paid ?? null,
                    'is_accepted'      => $rent->is_accepted ?? null,
                ];
            });
        }
        else{
            $result = null;
        }
        try {
            $fullRent->rentAssignment();
            return response()->json([
                'fullRent' => $result
            ]);
        }catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ]);
        }

    }

    #[OA\Put(
        path: '/api/full-rents/{id}',
        summary: 'Aktualizuje istniejący czynsz',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FullRent')
        ),
        tags: ['Czynsz'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Czynsz zaktualizowany'),
            new OA\Response(response: 400, description: 'Błąd walidacji lub czynsz jest już zaakceptowany'),
            new OA\Response(response: 403, description: 'Brak dostępu')
        ]
    )]
    public function update(Request $request, FullRent $fullRent): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'id_rent_assigment' => 'required|exists:rent_assigment,id',
            'amount' => 'required|numeric|min:0.1',
            'date' => ['required', new FullRentDateRequest()]
        ],[
            'id_rent_assigment.required' => 'Pole id_rent_assigmentu jest wymagany.',
            'id_rent_assigment.exists'=> 'Wybrana umowa najmu nie istnieje',
            'amount.required' => 'Pole amount jest wymagane.',
            'amount.numeric'=>'Pole amount musi być liczba',
            'amount.min'=>'Pole musi być większe od 0.1',
            'date.required' => 'Pole data jest wymagane.'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Nie udało się edytować czynszu.',
                'error'  => $validation->errors(),
            ], 400);
        }
        if (Gate::denies('update-fullRent', $fullRent)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        if($fullRent->is_accepted)
        {
            return response()->json([
                'message' => 'Nie można edytować potwierdzonego czysznu',
            ], 400);
        }
        try {
            $fullRent->update(request()->only(['id_rent_assigment', 'amount', 'date']));
            return response()->json([
                'message' => 'Czynsz został zaaktualizowany',
            ],200);
        }catch ( \Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ]);
        }

    }

    #[OA\Delete(
        path: '/api/full-rents/{id}',
        summary: 'Usuwa czynsz',
        security: [['bearerAuth' => []]],
        tags: ['Czynsz'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Czynsz usunięty'),
            new OA\Response(response: 400, description: 'Nie można usunąć zaakceptowanego czynszu'),
            new OA\Response(response: 403, description: 'Brak dostępu')
        ]
    )]
    public function destroy(FullRent $fullRent): JsonResponse
    {
        if (Gate::denies('delete-fullRent', $fullRent)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        if($fullRent->is_accepted){
            return response()->json([
                'message' => 'Nie można usunąć potwierdzonego czysznu',
            ], 400);
        }
        try{
            $fullRent->delete();
            return response()->json(['message'=>'Czynsz został usunięty'], 200);
        } catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidzany bład',
                'error' => $e->getMessage()
            ]);
        }
    }

    #[OA\Patch(
        path: '/api/full-rents/{id}/accept',
        summary: 'Akceptuje czynsz',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'is_accepted', type: 'boolean', example: true)
                ]
            )
        ),
        tags: ['Czynsz'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Czynsz potwierdzony'),
            new OA\Response(response: 400, description: 'Błąd walidacji')
        ]
    )]

    public function accept(FullRent $fullRent, Request $request): JsonResponse
    {
        if (Gate::denies('update-fullRent', $fullRent)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        $validation = Validator::make($request->all(), [
            'is_accepted' => 'required|boolean|in:1'
        ],[
            'is_accepted.required' => 'Pole is_accepted jest wymagane.',
            'is_accepted.boolean' => 'Pole is_accepted musi być typu boolean',
            'is_accepted.in' => 'Pole is_accepted musi być prawdą'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Błąd podczas potwierdzania czynszu',
                'error'  => $validation->errors(),
            ],400);
        }
        try{
            $fullRent->update(request()->only(['is_accepted']));
            return response()->json([
                'message' => 'Czynsz potwierdzony',
            ],200);
        } catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ],500);
        }
    }

    #[OA\Patch(
        path: '/api/full-rents/{id}/confirm-paid',
        summary: 'Potwierdza opłacenie czynszu',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'is_paid', type: 'boolean', example: true)
                ]
            )
        ),
        tags: ['Czynsz'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Płatność potwierdzona'),
            new OA\Response(response: 400, description: 'Błąd walidacji lub brak wcześniejszej akceptacji')
        ]
    )]
    public function confirmPaid(FullRent $fullRent, Request $request): JsonResponse
    {
        if (Gate::denies('confirm-paid-fullRent', $fullRent)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        $validation = Validator::make($request->all(), [
            'is_paid' => 'required|boolean|in:1'
        ],[
            'is_paid.required' => 'Pole is_paid jest wymagane.',
            'is_paid.boolean' => 'Pole is_paid musi być typu boolean',
            'is_paid.in' => 'Pole is_paid musi być prawdą'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Błąd podczas potwierdzania czynszu',
                'error'  => $validation->errors(),
            ],400);
        }
        if(!$fullRent->is_accepted){
            return response()->json([
                'message' => 'Nie można potwierdzić płatności przed potwierdzeniem',
            ],400);
        }
        $date = Carbon::now();
        try{
            $fullRent->update(array_merge(
                $request->only(['is_paid']),
                ['date_paid' => $date]
            ));
            return response()->json([
                'message' => 'Płatność potwierdzona',
            ],200);
        } catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ],500);
        }
    }
}
