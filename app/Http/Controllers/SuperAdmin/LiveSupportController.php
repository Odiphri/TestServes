<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LiveSupportConversation;
use App\Models\PlatformAdmin;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LiveSupportController extends Controller
{
    public function index(Request $request)
    {
        $conversations = LiveSupportConversation::with(['school', 'owner', 'assignedAdmin'])
            ->withCount('messages')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->search.'%';
                $query->where(fn ($inner) => $inner
                    ->where('reference', 'like', $search)
                    ->orWhere('subject', 'like', $search)
                    ->orWhere('visitor_name', 'like', $search)
                    ->orWhere('visitor_email', 'like', $search)
                );
            })
            ->orderByDesc('last_message_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.live-support.index', compact('conversations'));
    }

    public function show(LiveSupportConversation $liveSupport)
    {
        $liveSupport->load(['messages.platformAdmin', 'school', 'owner', 'assignedAdmin']);

        return view('super-admin.live-support.show', [
            'conversation' => $liveSupport,
            'admins' => PlatformAdmin::whereIn('role', ['super_admin', 'support_admin'])->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function reply(Request $request, LiveSupportConversation $liveSupport)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $admin = Auth::guard('platform_admin')->user();

        $liveSupport->messages()->create([
            'platform_admin_id' => $admin?->id,
            'sender_type' => 'admin',
            'sender_name' => $admin?->name ?? 'Support',
            'message' => $data['message'],
        ]);

        $liveSupport->update([
            'status' => 'answered',
            'assigned_admin_id' => $liveSupport->assigned_admin_id ?: $admin?->id,
            'last_message_at' => now(),
        ]);

        PlatformActivity::log('live_support_replied', "Replied to live support {$liveSupport->reference}.", $liveSupport);

        return back()->with('success', 'Reply sent.');
    }

    public function update(Request $request, LiveSupportConversation $liveSupport)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'waiting', 'answered', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'assigned_admin_id' => ['nullable', 'exists:platform_admins,id'],
        ]);

        $liveSupport->update($data);
        PlatformActivity::log('live_support_updated', "Updated live support {$liveSupport->reference}.", $liveSupport);

        return back()->with('success', 'Conversation updated.');
    }
}
