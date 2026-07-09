<?php

namespace App\Services;

use App\Models\PaymentRecord;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    public function enabled(): bool
    {
        $settings = SystemSetting::values();

        return ($settings['paystack_enabled'] ?? '') === '1' && filled($settings['paystack_secret_key'] ?? null);
    }

    public function initialize(PaymentRecord $payment, string $email, string $callbackUrl): string
    {
        $settings = SystemSetting::values();
        $secretKey = $settings['paystack_secret_key'] ?? null;

        if (blank($secretKey)) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::timeout(30)
            ->withToken($secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'amount' => (int) round(((float) $payment->amount) * 100),
                'email' => $email,
                'currency' => $payment->currency ?: 'NGN',
                'reference' => $payment->payment_reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'payment_record_id' => $payment->id,
                    'school_id' => $payment->school_id,
                ],
            ]);

        if (! $response->successful() || ! ($response->json('status') === true)) {
            throw new RuntimeException($response->json('message') ?: 'Unable to initialize Paystack payment.');
        }

        return $response->json('data.authorization_url');
    }

    public function verify(string $reference): array
    {
        $secretKey = SystemSetting::values()['paystack_secret_key'] ?? null;

        if (blank($secretKey)) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::timeout(30)
            ->withToken($secretKey)
            ->get('https://api.paystack.co/transaction/verify/'.$reference);

        if (! $response->successful() || ! ($response->json('status') === true)) {
            throw new RuntimeException($response->json('message') ?: 'Unable to verify Paystack payment.');
        }

        return $response->json('data') ?? [];
    }
}
