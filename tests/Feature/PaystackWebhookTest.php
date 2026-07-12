<?php

namespace Tests\Feature;

use App\Models\PaymentRecord;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Services\PaystackService;
use App\Support\TenantDatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_paystack_webhook_rejects_invalid_signature(): void
    {
        SystemSetting::create(['key' => 'paystack_secret_key', 'value' => 'secret']);

        $this->postJson(route('paystack.webhook'), ['data' => ['reference' => 'PSK-TEST']])
            ->assertUnauthorized();
    }

    public function test_paystack_webhook_accepts_unknown_reference_without_creating_payment(): void
    {
        SystemSetting::create(['key' => 'paystack_secret_key', 'value' => 'secret']);
        $payload = ['event' => 'charge.success', 'data' => ['reference' => 'PSK-UNKNOWN']];

        $this->postJson(route('paystack.webhook'), $payload, [
            'x-paystack-signature' => $this->signature($payload),
        ])->assertAccepted();

        $this->assertDatabaseMissing('payment_records', ['payment_reference' => 'PSK-UNKNOWN']);
    }

    public function test_paystack_webhook_verifies_existing_payment_idempotently(): void
    {
        $this->mock(TenantDatabaseManager::class)
            ->shouldReceive('createAndMigrate')
            ->once();

        SystemSetting::create(['key' => 'paystack_secret_key', 'value' => 'secret']);

        $plan = SubscriptionPlan::create([
            'name' => 'Webhook Plan',
            'slug' => 'webhook-plan',
            'monthly_price' => 5000,
            'yearly_price' => 50000,
            'status' => 'active',
        ]);

        $school = School::create([
            'name' => 'Webhook School',
            'slug' => 'webhook-school',
            'status' => 'awaiting_payment',
            'subscription_status' => 'pending',
            'subscription_plan_id' => $plan->id,
        ]);

        PaymentRecord::create([
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'amount' => 5000,
            'currency' => 'NGN',
            'payment_method' => 'paystack',
            'payment_reference' => 'PSK-WEBHOOK',
            'status' => 'pending',
            'period_start' => now()->toDateString(),
            'period_end' => now()->addMonth()->toDateString(),
        ]);

        $this->mock(PaystackService::class)
            ->shouldReceive('verify')
            ->twice()
            ->with('PSK-WEBHOOK')
            ->andReturn([
                'id' => 123456,
                'reference' => 'PSK-WEBHOOK',
                'status' => 'success',
                'amount' => 500000,
            ]);

        $payload = ['event' => 'charge.success', 'data' => ['reference' => 'PSK-WEBHOOK']];
        $headers = ['x-paystack-signature' => $this->signature($payload)];

        $this->postJson(route('paystack.webhook'), $payload, $headers)->assertOk();
        $this->postJson(route('paystack.webhook'), $payload, $headers)->assertOk();

        $this->assertDatabaseHas('payment_records', [
            'payment_reference' => 'PSK-WEBHOOK',
            'status' => 'paid',
            'provider_reference' => 'PSK-WEBHOOK',
        ]);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function signature(array $payload): string
    {
        return hash_hmac('sha512', json_encode($payload), 'secret');
    }
}
