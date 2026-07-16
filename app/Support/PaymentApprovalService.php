<?php

namespace App\Support;

use App\Models\PaymentRecord;
use App\Models\PlatformAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentApprovalService
{
    public function __construct(
        private readonly SubscriptionActivator $activator,
        private readonly SubscriptionLifecycleService $lifecycle,
    )
    {
    }

    public function mark(PaymentRecord $payment, string $status, PlatformAdmin $admin, ?string $notes = null): PaymentRecord
    {
        abort_unless($admin->canPerform(in_array($status, ['paid', 'refunded'], true) ? 'payments.approve' : 'payments.manage'), 403);

        if (! in_array($status, ['paid', 'failed', 'rejected', 'refunded', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => 'Unsupported payment status.']);
        }

        return DB::transaction(function () use ($payment, $status, $admin, $notes) {
            $payment = PaymentRecord::query()->lockForUpdate()->findOrFail($payment->id);

            if ($status === 'paid') {
                $this->assertReferenceNotAlreadyActivated($payment);
            }

            $old = $payment->only(['status', 'payment_date', 'approved_by_admin_id', 'approved_at', 'rejected_by_admin_id', 'rejected_at', 'notes']);
            $payload = [
                'status' => $status,
                'notes' => filled($notes) ? trim(($payment->notes ? $payment->notes."\n\n" : '').$notes) : $payment->notes,
            ];

            if ($status === 'paid') {
                $payload += [
                    'payment_date' => $payment->payment_date ?? now()->toDateString(),
                    'approved_by_admin_id' => $admin->id,
                    'approved_at' => now(),
                    'rejected_by_admin_id' => null,
                    'rejected_at' => null,
                    'verified_at' => now(),
                ];
            }

            if (in_array($status, ['failed', 'rejected'], true)) {
                $payload += [
                    'rejected_by_admin_id' => $admin->id,
                    'rejected_at' => now(),
                ];
            }

            $payment->update($payload);

            if ($status === 'paid') {
                $this->activator->activateFromPayment($payment->fresh());
            }

            if (in_array($status, ['failed', 'rejected'], true)) {
                $this->lifecycle->deactivateForFailedPayment($payment->fresh(), $notes);
            }

            PlatformActivity::log(
                'payment_marked_'.$status,
                "Marked payment {$payment->payment_reference} as {$status}.",
                $payment,
                [
                    'school_id' => $payment->school_id,
                    'old_values' => $old,
                    'new_values' => $payload,
                ]
            );

            return $payment->fresh();
        });
    }

    public function markPaystackVerified(PaymentRecord $payment, array $providerPayload): PaymentRecord
    {
        return DB::transaction(function () use ($payment, $providerPayload) {
            $payment = PaymentRecord::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'paid' && $payment->verified_at) {
                return $payment;
            }

            $this->assertReferenceNotAlreadyActivated($payment);

            $old = $payment->only(['status', 'payment_date', 'verified_at', 'provider_reference', 'provider_payload', 'notes']);
            $payload = [
                'status' => 'paid',
                'payment_date' => $payment->payment_date ?? now()->toDateString(),
                'verified_at' => now(),
                'provider_reference' => $providerPayload['reference'] ?? $payment->payment_reference,
                'provider_payload' => $providerPayload,
                'receipt_number' => $providerPayload['id'] ?? $payment->receipt_number,
                'notes' => trim(($payment->notes ?? '')."\nPaystack webhook verified successfully."),
            ];

            $payment->update($payload);
            $this->activator->activateFromPayment($payment->fresh());

            PlatformActivity::log(
                'payment_paystack_webhook_verified',
                "Verified Paystack payment {$payment->payment_reference} from webhook.",
                $payment,
                [
                    'school_id' => $payment->school_id,
                    'old_values' => $old,
                    'new_values' => $payload,
                ]
            );

            return $payment->fresh();
        });
    }

    private function assertReferenceNotAlreadyActivated(PaymentRecord $payment): void
    {
        if (blank($payment->payment_reference)) {
            return;
        }

        $alreadyPaid = PaymentRecord::query()
            ->whereKeyNot($payment->id)
            ->where('payment_reference', $payment->payment_reference)
            ->where('status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            throw ValidationException::withMessages([
                'payment_reference' => 'This payment reference has already activated a subscription.',
            ]);
        }
    }
}
