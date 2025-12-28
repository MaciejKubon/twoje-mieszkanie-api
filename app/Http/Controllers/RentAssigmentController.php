<?php

namespace App\Http\Controllers;

use App\Models\Objects;
use App\Models\RentAssigment;
use App\Models\User;
use App\Rules\ObjectInRentAssigment;
use App\Services\RentAssigmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class RentAssigmentController extends Controller
{

    public function __construct(RentAssigmentService $service) {
        $this->rentService = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Gate::denies('show-all-rentAssigment')) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        try{
            $userId = auth()->id();
            $objectsWithAssignments = $this->rentService->getRentsAssignedToObjects($userId);
            return response()->json([
                'data' => $objectsWithAssignments
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'rent_email' => 'nullable|email',
            'id_object' => ['required',new ObjectInRentAssigment() ],
            'start_date' => 'required|date',
            'end_date' => 'required|date||after:start_date',
        ],[
            'rent_email.email' => 'Pole email jest niepoprawny.',
            'id_object.required' => 'Pole objekt jest wymagane.',
            'start_date.date' => 'Pole data początku jest wymagane.',
            'end_date.date' => 'Pole data końca jest wymagane.',
            'end_date.after' => 'Pole data końca jest wcześniesz bądź równa dacie początkowej'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => 'Nie udało się utworzyć wynajmu.',
                'error'  => $validation->errors(),
            ], 400);
        }
        if (Gate::denies('create-rentAssigment', $request->id_object)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        $data = $request->only([
            'rent_email', 'id_object', 'start_date', 'end_date'
        ]);
        $id_renter = null;
        if($request->filled('rent_email')) {
            $id_renter = $this->rentService->AssigningRenterToRentAssigment($request->rent_email);
        }
        try{
            RentAssigment::create([
                'id_renter' => $id_renter,
                'id_object' => $request->id_object,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
            ]);
            return response()->json([
                'message' => 'Wynajem został dodany',
            ], 200);
        }catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ]);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(RentAssigment $rentAssigment): JsonResponse
    {
        if (Gate::denies('show-rentAssigment', $rentAssigment)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        $user = auth()->user();
        if($user->role == 'rentier'){
            $rentAssigmentDetail = $this->rentService->getDetailsForRenter($user->id);
        }
        else{
            $rentAssigmentDetail = $this->rentService->getDetailsForOwner($rentAssigment->id);
        }
        try{
            return response()->json([
                'rentAssigmentDetail' => $rentAssigmentDetail
            ],200);
        }catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidziany błąd',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RentAssigment $rentAssigment)
    {
        if(Gate::denies('update-rentAssigment', $rentAssigment)) {
            abort(403, 'Brak dostępu do najmu');
        }
        $user = auth()->user();
        if($user->role == 'rentier'){
            $validation = Validator::make($request->all(), [
                'confirmed' => 'required|boolean',
            ],[
                'confirmed.required' => 'Pole jest wymagane.',
                'confirmed.boolean' => 'Pole jest wymagane.',
            ]);
            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Nie udało się potwierdzić najmu',
                    'error'  => $validation->errors(),
                ], 400);
            }
            if($request->confirmed){
                try{
                    $rentAssigment->update([
                        'confirmed' => true,
                    ]);
                    return response()->json([
                        'message' => 'Najem został potwierdzony',
                    ],200);
                }catch (\Exception $e){
                    return response()->json([
                        'message' => 'Nieprzewidzany błąd',
                        'error' => $e->getMessage()
                    ],500);
                }
            }else{
                return response()->json([
                    'message' => 'Nie potwierdzono najmu',
                ],200);
            }
        }else{
            $validation = Validator::make($request->all(), [
                'rent_email' => 'nullable|email',
                'start_date' => 'required|date',
                'end_date' => 'required|date||after:start_date',
                'confirmed' => 'nullable|boolean',
            ],[
                'rent_email.email' => 'Pole email jest niepoprawny.',
                'id_object.required' => 'Pole objekt jest wymagane.',
                'start_date.required' => 'Pole data jest wymagane.',
                'start_date.date' => 'Pole data jest wymagane.',
                'end_date.required' => 'Pole data jest wymagane.',
                'end_date.date' => 'Pole data jest wymagane.',
                'end_date.after' => 'Pole data końca jest wcześniesz bądź równa dacie początkowej',
                'confirmed' => 'Nieporawny typ pola confimed',
            ]);
            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Nie udało się potwierdzić najmu',
                    'error'  => $validation->errors(),
                ], 400);
            }
            if($rentAssigment->confirmed)
            {
                return response()->json([
                'message' => 'Nie można edytować potwierdzonego najmu',
                ], 400);
            }
            if($rentAssigment->renter_id != null && $request->filled('confirmed')
                && $request->confirmed == true){
                return response()->json([
                    'message' => 'Przy przypisanym najemcy, potwierdzić najem może tylko najemca',
                ], 400);
            }
            $id_renter = null;
            if($request->filled('rent_email')) {
                if($request->filled('rent_email')) {
                    $id_renter = $this->rentService->AssigningRenterToRentAssigment($request->rent_email);
                }
            }
            try {
                $rentAssigment->update(array_merge(
                    $request->only(['confirmed', 'start_date', 'end_date']),
                    ['id_renter' => $id_renter]
                ));

                return response()->json([
                    'message' => 'Najem został zaaktualizowany',
                ],200);
            }catch ( \Exception $e) {
                return response()->json([
                    'message' => 'Nieprzewidzany błąd',
                    'error' => $e->getMessage()
                ]);
            }

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentAssigment $rentAssigment)
    {
        if (Gate::denies('delete-rentAssigment', $rentAssigment)) {
            abort(403, 'Brak dostępu. Nie jesteś właścicielem.');
        }
        try{
            $rentAssigment->delete();
            return response()->json([
                'message' => 'Wynajem został usunięty'
            ]);
        } catch ( \Exception $e){
            return response()->json([
                'message' => 'Nieprzewidzany bład',
                'error' => $e->getMessage()
            ]);
        }
    }
}
