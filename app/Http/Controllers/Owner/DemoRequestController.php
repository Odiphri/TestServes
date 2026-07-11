<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoRequestController extends Controller
{
    public function index()
    {
        $owner = Auth::guard('school_owner')->user();
        $owner->load('school');

        return view('owner.demo', [
            'owner' => $owner,
            'school' => $owner->school,
            'demoRequests' => DemoRequest::with(['assignedAdmin', 'plan'])
                ->where('school_owner_id', $owner->id)
                ->latest()
                ->get(),
            'plans' => SubscriptionPlan::where('status', 'active')->orderBy('monthly_price')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $owner = Auth::guard('school_owner')->user();
        $school = $owner->school;

        $data = $request->validate([
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'message' => ['nullable', 'string', 'max:2000'],
            'preferred_demo_date' => ['nullable', 'date'],
        ]);

        $plan = SubscriptionPlan::where('status', 'active')->findOrFail($data['subscription_plan_id']);

        DemoRequest::create([
            'school_owner_id' => $owner->id,
            'school_id' => $school?->id,
            'subscription_plan_id' => $plan->id,
            'school_name' => $school?->name ?? $owner->name.' school',
            'contact_person' => $owner->name,
            'email' => $owner->email,
            'phone' => $owner->phone,
            'location' => $school?->address,
            'message' => $data['message'] ?? "Owner requested CBT demo access for {$plan->name}.",
            'preferred_demo_date' => $data['preferred_demo_date'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', 'Demo request sent. A Sales Admin will approve your CBT demo link.');
    }

    public function destroy(DemoRequest $demoRequest)
    {
        $owner = Auth::guard('school_owner')->user();

        abort_unless($demoRequest->school_owner_id === $owner->id, 403);

        $demoRequest->delete();

        return back()->with('success', 'Demo request deleted.');
    }
}
