<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\NotificationCampaign;
use App\Models\School;
use App\Models\SchoolOwner;
use App\Support\NotificationCampaignService;
use App\Support\PlatformPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $campaigns = NotificationCampaign::with(['creator', 'school'])
            ->withCount('recipients')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.notification-campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $admin = Auth::guard('platform_admin')->user();

        return view('super-admin.notification-campaigns.create', [
            'owners' => SchoolOwner::with('school')->where('status', 'active')->orderBy('name')->get(),
            'schools' => School::with('owner')->orderBy('name')->get(),
            'canSendSystem' => PlatformPermission::allows($admin, 'notifications.system'),
            'canSendPlatformWide' => PlatformPermission::allows($admin, 'notifications.platform_wide'),
        ]);
    }

    public function store(Request $request, NotificationCampaignService $service): RedirectResponse
    {
        $admin = Auth::guard('platform_admin')->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', 'string', 'max:50'],
            'recipient_scope' => ['required', Rule::in([
                'single_school_owner',
                'selected_school_owners',
                'school_owners_for_school',
                'all_school_owners',
            ])],
            'school_owner_id' => ['nullable', 'integer', 'exists:school_owners,id'],
            'school_owner_ids' => ['nullable', 'array'],
            'school_owner_ids.*' => ['integer', 'exists:school_owners,id'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'action_url' => ['nullable', 'url', 'max:500'],
            'is_system_notification' => ['nullable', 'boolean'],
            'allows_replies' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $payload = match ($validated['recipient_scope']) {
            'single_school_owner' => ['school_owner_id' => $validated['school_owner_id'] ?? null],
            'selected_school_owners' => ['school_owner_ids' => $validated['school_owner_ids'] ?? []],
            'school_owners_for_school' => ['school_id' => $validated['school_id'] ?? null],
            default => [],
        };

        $campaign = $service->create($admin, [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'] ?: 'general',
            'recipient_scope' => $validated['recipient_scope'],
            'recipient_payload' => $payload,
            'school_id' => $validated['recipient_scope'] === 'school_owners_for_school' ? ($validated['school_id'] ?? null) : null,
            'action_url' => $validated['action_url'] ?? null,
            'is_system_notification' => $request->boolean('is_system_notification'),
            'allows_replies' => $request->boolean('allows_replies', true),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()
            ->route('super-admin.notification-campaigns.show', $campaign)
            ->with('success', 'Notification sent.');
    }

    public function show(NotificationCampaign $notificationCampaign): View
    {
        $notificationCampaign->load([
            'creator',
            'school',
            'recipients.notifiable',
            'recipients.thread.messages.sender',
        ]);

        return view('super-admin.notification-campaigns.show', [
            'campaign' => $notificationCampaign,
        ]);
    }
}
