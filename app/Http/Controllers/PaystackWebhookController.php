<?php

namespace App\Http\Controllers;

use App\Models\PaymentRecord;
use App\Models\SystemSetting;
use App\Services\PaystackService;
use App\Support\PaymentApprovalService;
use App\Support\PlatformActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, PaystackService $paystack, PaymentApprovalService $approvals): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $reference = $request->input('data.reference');

        if (blank($reference)) {
            return response()->json(['message' => 'Missing reference.'], 202);
        }

        $payment = PaymentRecord::query()
            ->where('payment_reference', $reference)
            ->orWhere('provider_reference', $reference)
            ->first();

        if (! $payment) {
            PlatformActivity::log('paystack_webhook_unknown_reference', "Paystack webhook ignored unknown reference {$reference}.");

            return response()->json(['message' => 'Reference not found.'], 202);
        }

        try {
            $verified = $paystack->verify($reference);
        } catch (\Throwable $exception) {
            PlatformActivity::log('paystack_webhook_verification_failed', "Paystack verification failed for {$reference}: {$exception->getMessage()}", $payment, [
                'school_id' => $payment->school_id,
            ]);

            return response()->json(['message' => 'Verification failed.'], 202);
        }

        if (($verified['status'] ?? null) === 'success') {
            $approvals->markPaystackVerified($payment, $verified);

            return response()->json(['message' => 'Payment verified.']);
        }

        $payment->update([
            'status' => 'failed',
            'provider_reference' => $reference,
            'provider_payload' => $verified,
            'notes' => trim(($payment->notes ?? '')."\nPaystack webhook status: ".($verified['status'] ?? 'unknown')),
        ]);

        return response()->json(['message' => 'Payment not successful.']);
    }

    private function hasValidSignature(Request $request): bool
    {
        $settings = SystemSetting::values();
        $secret = $settings['paystack_webhook_secret'] ?? $settings['paystack_secret_key'] ?? null;

        if (blank($secret)) {
            return false;
        }

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, (string) $request->header('x-paystack-signature'));
    }
}
