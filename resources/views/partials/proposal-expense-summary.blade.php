{{-- Read-only estimated expenses table. Expects $proposal. --}}
@php $expenseItems = $proposal->budgetItemList(); @endphp
@if(count($expenseItems))
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
        <thead class="table-light">
            <tr><th>Description</th><th class="text-center">Qty</th><th class="text-end">Unit Cost</th><th class="text-end">Total</th></tr>
        </thead>
        <tbody>
            @foreach($expenseItems as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-end text-nowrap">{{ \App\Helpers\SscHelper::formatCurrency($item['unit_cost']) }}</td>
                <td class="text-end text-nowrap">{{ \App\Helpers\SscHelper::formatCurrency($item['total']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="3" class="text-end">Total Estimated Expenses</th><th class="text-end text-nowrap">{{ \App\Helpers\SscHelper::formatCurrency($proposal->budgetItemTotal()) }}</th></tr>
        </tfoot>
    </table>
</div>
@else
<div class="text-muted small">No itemized expenses were listed for this proposal.</div>
@endif
