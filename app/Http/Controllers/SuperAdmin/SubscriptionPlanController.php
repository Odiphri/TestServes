<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Support\PlatformActivity;
use App\Http\Controllers\SuperAdmin\Concerns\AuthorizesPlatformSections;
use App\Services\PlanAiService;

class SubscriptionPlanController extends Controller
{
    use AuthorizesPlatformSections;

    public function index()
    {
        $plans = SubscriptionPlan::withCount('schools')->latest()->paginate(12);

        return view('super-admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('super-admin.plans.create', [
            'plan' => new SubscriptionPlan(),
            'availableFeatures' => $this->availableFeatures(),
        ]);
    }

    public function store(Request $request)
    {
        $plan = SubscriptionPlan::create($this->validated($request));
        PlatformActivity::log('plan_created', "Created plan {$plan->name}.", $plan);

        return redirect()->route('super-admin.subscription-plans.index')->with('success', 'Subscription plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('super-admin.plans.edit', [
            'plan' => $plan,
            'availableFeatures' => $this->availableFeatures(),
        ]);
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $plan->update($this->validated($request, $plan));
        PlatformActivity::log('plan_updated', "Updated plan {$plan->name}.", $plan);

        return redirect()->route('super-admin.subscription-plans.index')->with('success', 'Subscription plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->schools()->exists()) {
            $plan->update(['status' => 'inactive']);
            PlatformActivity::log('plan_archived', "Marked attached plan {$plan->name} inactive.", $plan);

            return back()->with('info', 'Plan has schools attached, so it was marked inactive instead of deleted.');
        }

        $plan->delete();
        PlatformActivity::log('plan_deleted', "Deleted plan {$plan->name}.", $plan);

        return back()->with('success', 'Subscription plan deleted.');
    }

    public function generateDraft(Request $request, PlanAiService $ai)
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        try {
            $draft = $ai->generateDraft($data['prompt'], $this->availableFeatures());
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['prompt' => $e->getMessage()]);
        }

        return back()
            ->withInput($draft)
            ->with('success', 'AI plan draft generated. Review the fields, then save the plan.');
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('subscription_plans', 'slug')->ignore($plan?->id)],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'trial_days' => ['required', 'integer', 'min:0'],
            'admin_limit' => ['required', 'integer', 'min:1', 'max:50'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in($this->availableFeatures())],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_recommended' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $duplicateSlug = SubscriptionPlan::where('slug', $data['slug'])
            ->when($plan, fn ($query) => $query->whereKeyNot($plan->id))
            ->exists();

        if ($duplicateSlug) {
            throw ValidationException::withMessages([
                'slug' => 'This plan slug is already in use. Change the slug or plan name.',
            ]);
        }

        $data['student_limit'] = null;
        $data['staff_limit'] = null;
        $data['exam_limit'] = null;
        $data['storage_limit'] = null;
        $data['is_recommended'] = $request->boolean('is_recommended');
        $data['features'] = collect($data['features'] ?? [])
            ->intersect($this->availableFeatures())
            ->values()
            ->all();

        return $data;
    }

    private function availableFeatures(): array
    {
        return [
            'School owner onboarding',
            'Dedicated school subdomain',
            'Separate school database',
            'Admin dashboard',
            'HOD dashboard',
            'CBT personnel dashboard',
            'Teacher dashboard',
            'Student dashboard',
            'Prefect dashboard',
            'Class management',
            'Subject management',
            'Student and staff management',
            'Teacher class assignment',
            'Exam creation',
            'AI question generation',
            'Question bank',
            'Live exam monitoring',
            'Student exam taking',
            'Autosaved exam answers',
            'Result calculation',
            'Exam retake controls',
            'HOD override approvals',
            'Bursary and fee tracking',
            'Payment restriction for owing students',
            'Attendance management',
            'Academic session promotion',
            'Student profile management',
            'Student directory',
            'Traffic analytics',
            'School branding settings',
            'Support tickets',
            'Activity logs',
        ];
    }
}
