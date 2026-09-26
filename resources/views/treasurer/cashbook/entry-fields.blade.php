{{-- Cash book entry inputs. Expects $values (entry_date, type, particulars, reference_no, category, amount). --}}
<div class="row g-3" data-cashbook-fields>
    <div class="col-md-5">
        <label class="form-label-custom">Date <span class="text-danger">*</span></label>
        <input type="date" name="entry_date" class="form-control-custom" value="{{ $values['entry_date'] }}" required>
    </div>
    <div class="col-md-7">
        <label class="form-label-custom">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select-custom" required data-cashbook-type>
            @foreach(\App\Models\CashBookEntry::TYPES as $type => $label)
            <option value="{{ $type }}" @selected($values['type'] === $type)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label-custom">Particulars <span class="text-danger">*</span></label>
        <input type="text" name="particulars" class="form-control-custom" value="{{ $values['particulars'] }}" maxlength="255" required placeholder="e.g. 26pcs. 2x2 ft. signages tarpaulin">
    </div>
    <div class="col-md-5">
        <label class="form-label-custom">OR/AR No.</label>
        <input type="text" name="reference_no" class="form-control-custom" value="{{ $values['reference_no'] }}" maxlength="100" placeholder="e.g. 2275">
    </div>
    <div class="col-md-7">
        <label class="form-label-custom">Amount (₱) <span class="text-danger">*</span></label>
        <input type="number" name="amount" class="form-control-custom" value="{{ $values['amount'] }}" min="0.01" step="0.01" required placeholder="0.00">
    </div>
    <div class="col-12" data-cashbook-category>
        <label class="form-label-custom">Expense Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select-custom">
            <option value="">Select category...</option>
            @foreach(\App\Models\CashBookEntry::CATEGORIES as $category)
            <option value="{{ $category }}" @selected($values['category'] === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12" style="font-size:.75rem;color:#64748b;" data-cashbook-opening-hint>
        Use <strong>Beginning Balance</strong> once, for the cash on hand when you start using the cash book. Later months carry their balance forward automatically.
    </div>
</div>
