{{--
    Party list picker for the candidacy form. Expects $partyLists (the active
    PartyList records). Optional $mobile = true for the mobile form styling.
    Picking a party shows its colour, full name and platform underneath, so a
    candidate sees exactly what they are filing under.
--}}
@if(!empty($mobile))
<div style="margin-bottom: 16px;" data-party-field>
    <label for="partyListSelect" style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--slate-700); margin-bottom: 6px;">Party List</label>
    <select id="partyListSelect" name="party_list_id" style="width: 100%; border: 1px solid var(--slate-200); border-radius: 10px; padding: 10px 12px; font-size: 0.85rem; font-family: inherit; background: #fff;">
@else
<div class="mb-3" data-party-field>
    <label for="partyListSelect" class="form-label-custom">Party List</label>
    <select id="partyListSelect" name="party_list_id" class="form-select-custom">
@endif
        <option value="" data-party-color="" data-party-name="{{ \App\Models\PartyList::INDEPENDENT }}" data-party-description="You will appear on the ballot without a party list.">{{ \App\Models\PartyList::INDEPENDENT }} (no party list)</option>
        @foreach($partyLists as $party)
        <option value="{{ $party->id }}" @selected((string) old('party_list_id') === (string) $party->id)
            data-party-color="{{ $party->color }}" data-party-text="{{ $party->text_color }}" data-party-name="{{ $party->name }}"
            data-party-description="{{ $party->description ?: 'No platform statement on file.' }}">
            {{ $party->acronym ? $party->acronym . ' — ' : '' }}{{ $party->name }}
        </option>
        @endforeach
    </select>
    <div class="d-flex align-items-start gap-2 mt-2 p-2" data-party-preview
        style="border:1px solid #E2E8F0; border-radius:12px; background:#F8FAFC; font-size:0.8rem;">
        <span data-party-swatch style="width:12px; height:12px; border-radius:50%; margin-top:3px; flex-shrink:0; background:#CBD5E1;"></span>
        <div style="min-width:0;">
            <div class="fw-bold" data-party-preview-name style="color:#1E293B;">{{ \App\Models\PartyList::INDEPENDENT }}</div>
            <div class="text-muted" data-party-preview-description style="line-height:1.45;">You will appear on the ballot without a party list.</div>
        </div>
    </div>
    <div class="form-text text-muted mt-1" style="font-size:0.75rem;">
        Each party list may field only one candidate per position. Party lists are registered by the SSC administrator.
    </div>
</div>

@once
@push('scripts')
<script>
    (function () {
        function sync(field) {
            var select = field.querySelector('select');
            var option = select.options[select.selectedIndex];
            if (!option) return;
            field.querySelector('[data-party-swatch]').style.background = option.dataset.partyColor || '#CBD5E1';
            field.querySelector('[data-party-preview-name]').textContent = option.dataset.partyName;
            field.querySelector('[data-party-preview-description]').textContent = option.dataset.partyDescription;
        }

        document.addEventListener('change', function (event) {
            var field = event.target.closest && event.target.closest('[data-party-field]');
            if (field) sync(field);
        });
        document.querySelectorAll('[data-party-field]').forEach(sync);
    })();
</script>
@endpush
@endonce
