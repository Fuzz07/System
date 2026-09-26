{{-- Estimated expenses editor plus the requested budget field. Expects $items and $requestedBudget. --}}
<div data-proposal-expenses>
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label-custom mb-0">Estimated Expenses</label>
            <button type="button" class="btn btn-sm btn-outline-primary" data-expense-add><i class="bi bi-plus-lg"></i> Add Expense</button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" style="font-size:.85rem;min-width:520px;">
                <thead class="table-light">
                    <tr><th>Description</th><th style="width:80px;">Qty</th><th style="width:120px;">Unit Cost (₱)</th><th class="text-end" style="width:110px;">Total</th><th style="width:32px;"></th></tr>
                </thead>
                <tbody data-expense-rows>
                    @foreach($items as $item)
                        @include('partials.proposal-expense-row', ['index' => $loop->index, 'item' => $item])
                    @endforeach
                </tbody>
                <tfoot data-expense-footer>
                    <tr><th colspan="3" class="text-end">Total Estimated Expenses</th><th class="text-end text-nowrap" data-expense-grand-total>₱0.00</th><th></th></tr>
                </tfoot>
            </table>
        </div>
        <div class="text-muted small py-2" data-expense-empty>No expenses listed yet. Add the items you expect to purchase, or enter the requested budget directly.</div>
        <template data-expense-template>
            @include('partials.proposal-expense-row', ['index' => '__INDEX__', 'item' => []])
        </template>
    </div>

    <div class="mb-3">
        <label class="form-label-custom">Requested Budget (₱) <span class="text-danger">*</span></label>
        <input type="number" name="requested_budget" class="form-control-custom" value="{{ $requestedBudget }}" placeholder="0.00" min="1" step="0.01" required data-expense-budget>
        <div style="font-size:.72rem;color:#a0aec0;margin-top:4px;" data-expense-budget-hint hidden>Calculated automatically from the estimated expenses.</div>
    </div>
</div>
