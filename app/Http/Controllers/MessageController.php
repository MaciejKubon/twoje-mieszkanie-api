<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
class MessageController extends Controller
{
    #[OA\Get(
        path: '/api/messages',
        summary: 'Pobiera listę unikalnych partnerów konwersacji (rozmówców) zalogowanego użytkownika.',
        security: [['bearerAuth' => []]],
        tags: ['Wiadomości']
    )]
    #[OA\Response(
        response: 200,
        description: 'Zwraca listę partnerów konwersacji.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message_list',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/User')
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Błąd serwera.'
    )]

    public function index()
    {
        try{
            $userId = Auth::id();
            $conversationPartners = User::where('id', '!=', $userId)
            ->where(function ($query) use ($userId) {
                $query->whereHas('receivedMessages', function ($q) use ($userId) {
                    $q->where('from_user_id', $userId);
                })
                    ->orWhereHas('sentMessages', function ($q) use ($userId) {
                        $q->where('to_user_id', $userId);
                    });
            })
                ->get();
            return response()->json([
                'message_list' => $conversationPartners,
            ], 400);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    #[OA\Get(
        path: '/api/messages/{partner}',
        summary: 'Pobiera historię wiadomości z konkretnym partnerem konwersacji.',
        security: [['bearerAuth' => []]],
        tags: ['Wiadomości']
    )]
    #[OA\Parameter(
        name: 'partner',
        description: 'ID partnera konwersacji (obiekt User).',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Zwraca chronologiczną listę wiadomości.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Message')
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Użytkownik (partner) nie znaleziony.'
    )]
    #[OA\Response(
        response: 500,
        description: 'Błąd serwera.'
    )]
    public function show(User $partner)
    {
        try{
            $userId = Auth::id();
            $messages = Message::where(function ($query) use ($userId, $partner) {
                $query->where('from_user_id', $userId)
                    ->where('to_user_id', $partner->id);
            })
                ->orWhere(function ($query) use ($userId, $partner) {
                    $query->where('from_user_id', $partner->id)
                        ->where('to_user_id', $userId);
                })
                ->with(['from_user', 'to_user'])
                ->oldest()
                ->get();
            return response()->json([
                'message' => $messages,
            ], 400);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/messages/{receiver}',
        summary: 'Wysyła nową wiadomość do określonego odbiorcy.',
        security: [['bearerAuth' => []]],
        tags: ['Wiadomości']
    )]
    #[OA\Parameter(
        name: 'receiver',
        description: 'ID użytkownika, do którego wysyłana jest wiadomość.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['message'],
            properties: [
                new OA\Property(
                    property: 'message',
                    description: 'Treść wiadomości (max 1000 znaków)',
                    type: 'string'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Wiadomość została pomyślnie wysłana.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Wiadomość wysłana pomyślnie.'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Błąd walidacji (np. brak pola "message").'
    )]
    #[OA\Response(
        response: 500,
        description: 'Błąd serwera.'
    )]
    public function store(Request $request, User $receiver)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $message = Message::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $receiver->id,
                'message' => $request->input('message'),
                'sent_at' => now(),
                'is_read' => false,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Nieprzewidzany błąd',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
