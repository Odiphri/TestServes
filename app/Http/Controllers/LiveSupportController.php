<?php

namespace App\Http\Controllers;

use App\Models\LiveSupportConversation;
use App\Events\LiveSupportMessageSent;
use App\Models\School;
use App\Support\TestServesDomains;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LiveSupportController extends Controller
{
    public function create(Request $request)
    {
        $owner = auth('school_owner')->user();

        return view('live-support.create', [
            'school' => $this->schoolFromRequest($request),
            'owner' => $owner,
        ]);
    }

    public function store(Request $request)
    {
        $owner = auth('school_owner')->user();

        $data = $request->validate([
            'visitor_name' => [$owner ? 'nullable' : 'required', 'nullable', 'string', 'max:255'],
            'visitor_email' => ['nullable', 'email', 'max:255'],
            'visitor_phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $school = $this->schoolFromRequest($request);

        $conversation = LiveSupportConversation::create([
            'school_id' => $school?->id,
            'school_owner_id' => $school?->owner?->id,
            'reference' => $this->reference(),
            'access_token' => Str::random(64),
            'visitor_name' => $data['visitor_name'] ?? $owner?->name,
            'visitor_email' => $data['visitor_email'] ?? $owner?->email,
            'visitor_phone' => $data['visitor_phone'] ?? $owner?->phone,
            'subject' => $data['subject'],
            'status' => 'open',
            'priority' => 'medium',
            'last_message_at' => now(),
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'visitor',
            'sender_name' => $data['visitor_name'] ?? $owner?->name ?? 'Visitor',
            'message' => $data['message'],
        ]);

        $this->broadcastMessage($message);

        return redirect()->route('live-support.show', $conversation->access_token)
            ->with('success', 'Your live support conversation has started.');
    }

    public function show(string $token)
    {
        $conversation = LiveSupportConversation::with('messages.platformAdmin')
            ->where('access_token', $token)
            ->firstOrFail();

        return view('live-support.show', compact('conversation'));
    }

    public function reply(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $conversation = LiveSupportConversation::where('access_token', $token)->firstOrFail();

        abort_if($conversation->status === 'closed', 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'visitor',
            'sender_name' => $conversation->visitor_name,
            'message' => $data['message'],
        ]);

        $conversation->update([
            'status' => 'waiting',
            'last_message_at' => now(),
        ]);

        $this->broadcastMessage($message);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => (new LiveSupportMessageSent($message))->broadcastWith(),
            ]);
        }

        return back()->with('success', 'Message sent.');
    }

    private function schoolFromRequest(Request $request): ?School
    {
        $slug = TestServesDomains::schoolSlugFromRequest($request);

        return $slug ? School::with('owner')->where('slug', $slug)->first() : null;
    }

    private function reference(): string
    {
        do {
            $reference = 'CHAT-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (LiveSupportConversation::where('reference', $reference)->exists());

        return $reference;
    }

    private function broadcastMessage($message): void
    {
        try {
            broadcast(new LiveSupportMessageSent($message))->toOthers();
        } catch (Throwable $exception) {
            Log::warning('Live support broadcast failed; message was saved for refresh fallback.', [
                'message_id' => $message->id,
                'conversation_id' => $message->live_support_conversation_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
