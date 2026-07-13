<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\PlatformAdmin;
use App\Support\PlatformActivity;
use App\Support\PublicSiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactInquiryController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user('platform_admin');
        abort_unless($admin?->canPerform('support.view'), 403);

        $inquiries = ContactInquiry::with('assignedAdmin')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->category))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('subject', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('name', 'like', '%'.$request->search.'%');
            }))
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.contact-inquiries.index', [
            'inquiries' => $inquiries,
            'categories' => ContactInquiry::CATEGORIES,
        ]);
    }

    public function show(Request $request, ContactInquiry $contactInquiry)
    {
        $admin = $request->user('platform_admin');
        abort_unless($admin?->canPerform('support.view'), 403);

        $contactInquiry->load('assignedAdmin');
        PlatformActivity::log('contact_inquiry_viewed', "Viewed contact inquiry {$contactInquiry->subject}.", $contactInquiry);

        return view('super-admin.contact-inquiries.show', [
            'inquiry' => $contactInquiry,
            'admins' => PlatformAdmin::whereIn('role', ['super_admin', 'support_admin'])->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, ContactInquiry $contactInquiry)
    {
        $admin = $request->user('platform_admin');
        abort_unless($admin?->canPerform('support.assign'), 403);

        $data = $request->validate([
            'assigned_admin_id' => ['nullable', 'exists:platform_admins,id'],
        ]);

        $old = $contactInquiry->assigned_admin_id;
        $contactInquiry->update([
            'assigned_admin_id' => $data['assigned_admin_id'] ?? null,
            'status' => filled($data['assigned_admin_id'] ?? null) && $contactInquiry->status === 'new' ? 'assigned' : $contactInquiry->status,
        ]);
        PlatformActivity::log('contact_inquiry_assigned', "Assigned contact inquiry {$contactInquiry->subject}.", $contactInquiry, [
            'old_values' => ['assigned_admin_id' => $old],
            'new_values' => ['assigned_admin_id' => $contactInquiry->assigned_admin_id],
        ]);

        return back()->with('success', 'Inquiry assignment updated.');
    }

    public function status(Request $request, ContactInquiry $contactInquiry)
    {
        $admin = $request->user('platform_admin');
        abort_unless($admin?->canPerform('support.manage'), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(ContactInquiry::STATUSES)],
        ]);

        $old = $contactInquiry->status;
        $timestamps = match ($data['status']) {
            'responded' => ['responded_at' => now()],
            'closed' => ['closed_at' => now()],
            default => [],
        };
        $contactInquiry->update(['status' => $data['status']] + $timestamps);

        PlatformActivity::log('contact_inquiry_status_changed', "Changed contact inquiry status from {$old} to {$data['status']}.", $contactInquiry, [
            'old_values' => ['status' => $old],
            'new_values' => ['status' => $data['status']],
        ]);

        return back()->with('success', 'Inquiry status updated.');
    }

    public function note(Request $request, ContactInquiry $contactInquiry)
    {
        $admin = $request->user('platform_admin');
        abort_unless($admin?->canPerform('support.manage'), 403);

        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        PlatformActivity::log('contact_inquiry_note_added', "Added note to contact inquiry {$contactInquiry->subject}.", $contactInquiry, [
            'new_values' => ['note' => $data['note']],
        ]);

        return back()->with('success', 'Internal note recorded in the audit log.');
    }

    public function respond(Request $request, ContactInquiry $contactInquiry)
    {
        $admin = $request->user('platform_admin');
        abort_unless($admin?->canPerform('support.manage'), 403);

        $data = $request->validate(['response' => ['required', 'string', 'min:5', 'max:5000']]);
        $supportEmail = PublicSiteSettings::get('support_email') ?: 'testserves.ng@gmail.com';

        Mail::raw($data['response'], function ($message) use ($contactInquiry, $supportEmail) {
            $message->to($contactInquiry->email, $contactInquiry->name)
                ->replyTo($supportEmail, 'TestServes Support')
                ->subject('Re: '.$contactInquiry->subject);
        });

        $contactInquiry->update(['status' => 'responded', 'responded_at' => now()]);
        PlatformActivity::log('contact_inquiry_response_sent', "Sent response to contact inquiry {$contactInquiry->subject}.", $contactInquiry);

        return back()->with('success', 'Response sent.');
    }
}
