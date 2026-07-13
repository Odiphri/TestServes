<?php

namespace App\Http\Controllers;

use App\Models\NotificationRecipient;
use App\Models\SchoolOwner;
use App\Support\NotificationCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationCenter $notifications)
    {
    }

    public function index(Request $request): View
    {
        $query = $this->notifications->queryForCurrentUser($request);
        $context = $this->viewContext($request);

        return view('notifications.index', $context + [
            'notifications' => $query->latest('id')->paginate(15),
        ]);
    }

    public function markRead(Request $request, NotificationRecipient $notification): RedirectResponse
    {
        $this->notifications->markReadForCurrentUser($request, $notification);

        return back()->with('success', 'Notification marked as read.');
    }

    public function show(Request $request, NotificationRecipient $notification): View
    {
        $this->notifications->markReadForCurrentUser($request, $notification);
        $notification->load(['campaign', 'thread.messages.sender']);
        if ($notification->campaign?->allows_replies && ! $notification->campaign?->is_system_notification && ! $notification->thread) {
            $notification->thread()->create(['status' => 'open']);
            $notification->load(['thread.messages.sender']);
        }
        $context = $this->viewContext($request);

        return view('notifications.show', $context + [
            'notification' => $notification,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllReadForCurrentUser($request);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function reply(Request $request, NotificationRecipient $notification): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->notifications->replyForCurrentUser($request, $notification, $validated['message']);

        if ($request->expectsJson()) {
            $notification->load(['thread.messages.sender']);
            $message = $notification->thread?->messages()->latest('id')->first();

            return response()->json([
                'message' => $message ? [
                    'id' => $message->id,
                    'thread_id' => $message->notification_thread_id,
                    'sender_type' => $message->sender_type,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? $message->sender?->full_name ?? 'You',
                    'message' => $message->message,
                    'created_at' => optional($message->created_at)->toISOString(),
                ] : null,
            ]);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function destroy(Request $request, NotificationRecipient $notification): RedirectResponse
    {
        $this->notifications->hideForCurrentUser($request, $notification);

        return redirect()->route($this->viewContext($request)['routePrefix'].'.index')
            ->with('success', 'Notification deleted from your inbox.');
    }

    private function viewContext(Request $request): array
    {
        if (auth('platform_admin')->check()) {
            abort(404);
        }

        if (auth('school_owner')->check()) {
            $owner = auth('school_owner')->user();

            return [
                'layout' => 'owner.app',
                'routePrefix' => 'platform.notifications',
                'owner' => $owner,
                'school' => $owner instanceof SchoolOwner ? $owner->school : null,
            ];
        }

        return [
            'layout' => 'layouts.admin',
            'routePrefix' => 'notifications',
        ];
    }
}
