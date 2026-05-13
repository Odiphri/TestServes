<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index()
    {
        $requests = ChangeRequest::where('student_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('student.requests.index', compact('requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|in:name_change,role_change,prefect_title',
            'new_value' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $oldValue = match ($validated['request_type']) {
            'name_change' => $user->full_name,
            'role_change' => $user->role,
            'prefect_title' => $user->prefect_title,
        };

        ChangeRequest::create([
            'student_id' => $user->id,
            'request_type' => $validated['request_type'],
            'old_value' => $oldValue,
            'new_value' => $validated['new_value'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Request submitted for admin review.');
    }
}
