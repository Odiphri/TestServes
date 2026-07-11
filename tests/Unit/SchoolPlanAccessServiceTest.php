<?php

namespace Tests\Unit;

use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Support\SchoolPlanAccessService;
use Tests\TestCase;

class SchoolPlanAccessServiceTest extends TestCase
{
    public function test_it_maps_routes_to_plan_features(): void
    {
        $service = app(SchoolPlanAccessService::class);

        $this->assertSame('Exam creation', $service->featureForRoute('admin.exams.create'));
        $this->assertSame('Student exam taking', $service->featureForRoute('student.exams.show'));
        $this->assertSame('Bursary and fee tracking', $service->featureForRoute('teacher.payments'));
        $this->assertSame('Student dashboard', $service->featureForRoute('student.dashboard'));
    }

    public function test_it_allows_only_features_on_the_school_plan(): void
    {
        $plan = new SubscriptionPlan([
            'features' => ['Admin dashboard', 'Exam creation'],
        ]);
        $school = new School();
        $school->setRelation('plan', $plan);

        $service = app(SchoolPlanAccessService::class);

        $this->assertTrue($service->allows($school, 'Exam creation'));
        $this->assertFalse($service->allows($school, 'Bursary and fee tracking'));
    }
}
