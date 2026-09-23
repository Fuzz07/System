<?php

namespace App\Observers;

use App\Models\Candidacy;
use App\Models\EnrollmentPayment;
use App\Models\Expense;
use App\Models\Feedback;
use App\Models\Liquidation;
use App\Models\Proposal;
use App\Models\User;
use App\Services\AdminAlertService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminRequestObserver
{
    public function created(Model $model): void
    {
        match (true) {
            $model instanceof User => $this->studentRegistered($model),
            $model instanceof Proposal => $this->proposalSubmitted($model),
            $model instanceof Expense => $this->expenseSubmitted($model),
            $model instanceof Feedback => $this->feedbackSubmitted($model),
            $model instanceof Candidacy => $this->candidacySubmitted($model),
            $model instanceof Liquidation => $this->liquidationSubmitted($model),
            default => null,
        };
    }

    public function updated(Model $model): void
    {
        if ($model instanceof EnrollmentPayment
            && $model->wasChanged('proof_path')
            && filled($model->proof_path)) {
            $this->enrollmentProofSubmitted($model);
        }

        if ($model instanceof Proposal
            && $model->wasChanged('completion_proof')
            && filled($model->completion_proof)) {
            $officer = $model->officer?->fullname ?? 'An officer';
            AdminAlertService::send(
                'Project completion submitted',
                "{$officer} submitted completion proof for \"{$model->project_title}\".",
                route('admin.proposals'),
                'proposal_completion'
            );
        }
    }

    private function studentRegistered(User $user): void
    {
        if (! $user->isStudent() || $user->status !== 'inactive') {
            return;
        }

        AdminAlertService::send(
            'Student approval requested',
            "{$user->fullname} ({$user->email}) registered and is waiting for account approval.",
            route('admin.students.index'),
            'student_approval'
        );
    }

    private function proposalSubmitted(Proposal $proposal): void
    {
        $officer = $proposal->officer?->fullname ?? 'An officer';
        AdminAlertService::send(
            'Proposal approval requested',
            "{$officer} submitted \"{$proposal->project_title}\" with a requested budget of PHP "
                . number_format((float) $proposal->requested_budget, 2) . '.',
            route('admin.proposals', ['status' => 'Pending']),
            'proposal_approval'
        );
    }

    private function expenseSubmitted(Expense $expense): void
    {
        $officer = $expense->officer?->fullname ?? 'An officer';
        AdminAlertService::send(
            'Expense approval requested',
            "{$officer} submitted \"{$expense->expense_title}\" for PHP "
                . number_format((float) $expense->amount, 2) . '.',
            route('admin.expenses', ['status' => 'Pending']),
            'expense_approval'
        );
    }

    private function feedbackSubmitted(Feedback $feedback): void
    {
        $student = $feedback->student?->fullname ?? 'A student';
        AdminAlertService::send(
            'New student feedback',
            "{$student} submitted feedback: " . Str::limit($feedback->message, 140),
            route('admin.feedback'),
            'student_feedback'
        );
    }

    private function candidacySubmitted(Candidacy $candidacy): void
    {
        $student = $candidacy->user?->fullname ?? 'A student';
        AdminAlertService::send(
            'New candidacy application',
            "{$student} applied for {$candidacy->position}.",
            route('admin.candidacies'),
            'candidacy_application'
        );
    }

    private function liquidationSubmitted(Liquidation $liquidation): void
    {
        $officer = $liquidation->officer?->fullname ?? 'An officer';
        AdminAlertService::send(
            'New liquidation report',
            "{$officer} uploaded the liquidation report \"{$liquidation->title}\".",
            route('admin.proposals'),
            'liquidation_report'
        );
    }

    private function enrollmentProofSubmitted(EnrollmentPayment $payment): void
    {
        $student = $payment->user?->fullname ?? 'A student';
        AdminAlertService::send(
            'Enrollment proof awaiting review',
            "{$student} uploaded enrollment payment proof for reference {$payment->reference}.",
            route('admin.enrollment.payments'),
            'enrollment_proof'
        );
    }
}
