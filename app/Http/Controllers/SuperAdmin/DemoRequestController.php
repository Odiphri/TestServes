<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\PlatformAdmin;
use App\Support\PlatformActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemoRequestController extends Controller
{
    public function index(Request $request)
    {
        $demoRequests = DemoRequest::with('assignedAdmin')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(fn ($inner) => $inner
                    ->where('school_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.demo-requests.index', compact('demoRequests'));
    }

    public function create()
    {
        return view('super-admin.demo-requests.create', $this->formData(new DemoRequest()));
    }

    public function store(Request $request)
    {
        $demoRequest = DemoRequest::create($this->validated($request));
        PlatformActivity::log('demo_request_created', "Created demo request for {$demoRequest->school_name}.", $demoRequest);

        return redirect()->route('super-admin.demo-requests.index')->with('success', 'Demo request created.');
    }

    public function show(DemoRequest $demoRequest)
    {
        $demoRequest->load('assignedAdmin');

        return view('super-admin.demo-requests.show', compact('demoRequest'));
    }

    public function edit(DemoRequest $demoRequest)
    {
        return view('super-admin.demo-requests.edit', $this->formData($demoRequest));
    }

    public function update(Request $request, DemoRequest $demoRequest)
    {
        $demoRequest->update($this->validated($request));
        PlatformActivity::log('demo_request_updated', "Updated demo request for {$demoRequest->school_name}.", $demoRequest);

        return redirect()->route('super-admin.demo-requests.show', $demoRequest)->with('success', 'Demo request updated.');
    }

    public function destroy(DemoRequest $demoRequest)
    {
        $demoRequest->delete();
        PlatformActivity::log('demo_request_deleted', "Archived demo request for {$demoRequest->school_name}.", $demoRequest);

        return redirect()->route('super-admin.demo-requests.index')->with('success', 'Demo request archived.');
    }

    private function formData(DemoRequest $demoRequest): array
    {
        return [
            'demoRequest' => $demoRequest,
            'admins' => PlatformAdmin::whereIn('role', ['super_admin', 'sales_admin'])->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'preferred_demo_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['new', 'contacted', 'scheduled', 'completed', 'cancelled'])],
            'assigned_admin_id' => ['nullable', 'exists:platform_admins,id'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
