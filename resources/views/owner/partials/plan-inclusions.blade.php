@php
    $features = $plan->features ?? [];
    $limits = [
        'Admin accounts' => $plan->admin_limit ? number_format($plan->admin_limit) : null,
        'Students' => $plan->student_limit ? number_format($plan->student_limit) : null,
        'Staff' => $plan->staff_limit ? number_format($plan->staff_limit) : null,
        'Exams' => $plan->exam_limit ? number_format($plan->exam_limit) : null,
        'Storage' => $plan->storage_limit ? number_format($plan->storage_limit).' MB' : null,
    ];
    $visibleLimits = array_filter($limits);
@endphp

@if($visibleLimits)
    <span class="feature-title">Plan limits</span>
    <ul class="pricing-feature-list">
        @foreach($visibleLimits as $label => $value)
            <li>{{ $label }}: {{ $value }}</li>
        @endforeach
    </ul>
@endif

<span class="feature-title">Included features</span>
@if($features)
    <ul class="pricing-feature-list">
        @foreach($features as $feature)
            <li>{{ $feature }}</li>
        @endforeach
    </ul>
@else
    <span class="pricing-sub">No features listed for this plan yet.</span>
@endif
