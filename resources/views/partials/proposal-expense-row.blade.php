{{-- One estimated-expense row. Expects $index and $item (description, qty, unit_cost). --}}
<tr data-expense-row>
    <td><input type="text" name="budget_items[{{ $index }}][description]" class="form-control form-control-sm" value="{{ $item['description'] ?? '' }}" placeholder="e.g. Cartolina (assorted colors)" maxlength="255" required></td>
    <td><input type="number" name="budget_items[{{ $index }}][qty]" class="form-control form-control-sm" value="{{ $item['qty'] ?? 1 }}" min="1" step="1" required data-expense-qty></td>
    <td><input type="number" name="budget_items[{{ $index }}][unit_cost]" class="form-control form-control-sm" value="{{ $item['unit_cost'] ?? '' }}" min="0" step="0.01" placeholder="0.00" required data-expense-cost></td>
    <td class="text-end fw-semibold text-nowrap" data-expense-line-total>₱0.00</td>
    <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger p-0" data-expense-remove title="Remove item" aria-label="Remove item"><i class="bi bi-trash"></i></button></td>
</tr>
