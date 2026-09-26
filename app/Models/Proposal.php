<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'officer_id', 'project_title', 'requested_budget', 'approved_budget',
        'description', 'status', 'approved_by', 'admin_notes',
        'project_status', 'completion_proof', 'proposal_event_date',
        'participant_count', 'objectives', 'budget_items', 'project_image',
    ];
    protected $casts = ['created_at' => 'datetime', 'requested_budget' => 'decimal:2', 'approved_budget' => 'decimal:2'];
    protected $attributes = ['project_status' => 'Ongoing'];

    public function officer() { return $this->belongsTo(User::class, 'officer_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function comments() { return $this->hasMany(ProposalComment::class); }

    /**
     * Estimated expense lines as [description, qty, unit_cost, total].
     * Items are stored as JSON; older plain-text "Label - 1,000" lines are still read.
     */
    public function budgetItemList(): array
    {
        $source = trim((string) $this->budget_items);
        if ($source === '') {
            return [];
        }

        $decoded = json_decode($source, true);
        if (is_array($decoded)) {
            return array_values(array_map(function ($item) {
                $qty = (int) ($item['qty'] ?? 1);
                $unitCost = (float) ($item['unit_cost'] ?? 0);
                return [
                    'description' => (string) ($item['description'] ?? ''),
                    'qty'         => $qty,
                    'unit_cost'   => $unitCost,
                    'total'       => round($qty * $unitCost, 2),
                ];
            }, array_filter($decoded, 'is_array')));
        }

        $items = [];
        foreach (preg_split('/\r\n|\r|\n/', $source) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $amount = 0.0;
            if (preg_match('/^(.+?)(?:\s*[-:=]\s*|\s{2,})([0-9][0-9,]*(?:\.[0-9]+)?)$/', $line, $matches)) {
                $line = trim($matches[1]);
                $amount = (float) str_replace(',', '', $matches[2]);
            }
            $items[] = ['description' => $line, 'qty' => 1, 'unit_cost' => $amount, 'total' => $amount];
        }
        return $items;
    }

    public function budgetItemTotal(): float
    {
        return round(array_sum(array_column($this->budgetItemList(), 'total')), 2);
    }
}
