<?php

namespace App\Http\Controllers;

use App\Models\NotificationRecipient;
use App\Models\SchoolOwner;
use App\Support\NotificationCenter;
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

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllReadForCurrentUser($request);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function reply(Request $request, NotificationRecipient $notification): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->notifications->replyForCurrentUser($request, $notification, $validated['message']);

        return back()->with('success', 'Reply sent.');
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
