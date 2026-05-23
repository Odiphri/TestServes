<?php

namespace App\Http\Controllers;

use App\Models\PrefectRole;
use Illuminate\Http\Request;

class PrefectRoleController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureViewer($request);

        return view('management.prefect-roles.index', [
            'prefectRoles' => PrefectRole::withCount('prefects')->latest()->paginate(20),
            'canEdit' => $this->canEditRoles($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:prefect_roles,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PrefectRole::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Prefect role created successfully.');
    }

    public function update(Request $request, PrefectRole $prefectRole)
    {
        $this->ensureEditor($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:prefect_roles,name,' . $prefectRole->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $prefectRole->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Prefect role updated successfully.');
    }

    public function destroy(Request $request, PrefectRole $prefectRole)
    {
        $this->ensureEditor($request);

        $prefectRole->delete();

        return back()->with('success', 'Prefect role deleted successfully.');
    }

    private function ensureViewer(Request $request): void
    {
        abort_unless($request->user() && in_array($request->user()->role, ['admin', 'hod', 'teacher', 'cbt_personnel'], true), 403);
    }

    private function ensureEditor(Request $request): void
    {
        abort_unless($request->user() && $this->canEditRoles($request), 403);
    }

    private function canEditRoles(Request $request): bool
    {
        $user = $request->user();

        return $user && (in_array($user->role, ['admin', 'hod'], true) || $user->can('student_roles.manage'));
    }
}
