<?php

namespace App\Services;

use App\Helpers\SscHelper;
use App\Models\Budget;
use App\Models\EnrollmentPayment;
use App\Notifications\EnrollmentPaidNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentPaymentSettlementService
{
    public function settlePayMongoPayment(
        EnrollmentPayment $payment,
        array $payMongoPayment,
        ?int $providerPaidAt = null
    ): bool {
        $wasSettled = DB::transaction(function () use ($payment, $payMongoPayment, $providerPaidAt) {
            $lockedPayment = EnrollmentPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === 'paid') {
                return false;
            }

            $sourceType = data_get($payMongoPayment, 'attributes.source.type');
            $lockedPayment->update([
                'method' => 'paymongo',
                'status' => 'paid',
                'proof_status' => 'approved',
                'proof_notes' => null,
                'paymongo_payment_id' => $payMongoPayment['id'] ?? null,
                'paymongo_payment_method' => is_string($sourceType) ? $sourceType : null,
                'paid_at' => $providerPaidAt ? Carbon::createFromTimestamp($providerPaidAt) : now(),
            ]);

            $budget = Budget::query()->lockForUpdate()->firstOrCreate(
                [
                    'title' => Budget::ENROLLMENT_TITLE_PREFIX,
                    'school_year' => $lockedPayment->semester ?: SscHelper::getActiveAcademicTerm(),
                ],
                [
                    'department' => 'All Departments',
                    'allocated_amount' => 0,
                    'remaining_balance' => 0,
                    'status' => 'Approved',
                    'created_by' => null,
                    'notes' => 'Consolidated enrollment fees collection for all departments.',
                ]
            );

            $budget->increment('allocated_amount', $lockedPayment->amount);
            $budget->increment('remaining_balance', $lockedPayment->amount);
            $budget->update(['status' => 'Approved']);

            return true;
        });

        if (! $wasSettled) {
            return false;
        }

        $payment->refresh()->load('user');
        SscHelper::logActivity(
            $payment->user_id,
            'ENROLLMENT_PAYMONGO_PAID',
            "Verified PayMongo enrollment payment #{$payment->id}"
        );

        try {
            $payment->user?->notify(new EnrollmentPaidNotification($payment));
        } catch (\Throwable $exception) {
            Log::warning('Unable to send PayMongo enrollment payment notification.', [
                'payment_id' => $payment->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return true;
    }
}
