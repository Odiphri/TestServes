<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Models\School;
use App\Models\SupportTicket;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['school', 'owner', 'assignedAdmin'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->when($request->filled('search'), fn ($query) => $query->where('subject', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.support-tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('super-admin.support-tickets.create', $this->formData(new SupportTicket()));
    }

    public function store(Request $request)
    {
        $ticket = SupportTicket::create($this->validated($request));
        PlatformActivity::log('support_ticket_created', "Created support ticket {$ticket->subject}.", $ticket);

        return redirect()->route('super-admin.support-tickets.index')->with('success', 'Support ticket created.');
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['school', 'owner', 'assignedAdmin']);

        return view('super-admin.support-tickets.show', compact('supportTicket'));
    }

    public function edit(SupportTicket $supportTicket)
    {
        return view('super-admin.support-tickets.edit', $this->formData($supportTicket));
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        $supportTicket->update($this->validated($request));
        PlatformActivity::log('support_ticket_updated', "Updated support ticket {$supportTicket->subject}.", $supportTicket);

        return redirect()->route('super-admin.support-tickets.show', $supportTicket)->with('success', 'Support ticket updated.');
    }

    private function formData(SupportTicket $supportTicket): array
    {
        return [
            'supportTicket' => $supportTicket,
            'schools' => School::with('owner')->orderBy('name')->get(),
            'admins' => PlatformAdmin::whereIn('role', ['super_admin', 'support_admin'])->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'school_id' => ['nullable', 'exists:schools,id'],
            'school_owner_id' => ['nullable', 'exists:school_owners,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'assigned_admin_id' => ['nullable', 'exists:platform_admins,id'],
            'internal_notes' => ['nullable', 'string'],
        ]);
    }
}
