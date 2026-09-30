{{--
    Fields for registering or editing a party list. Expects $party (PartyList
    or null) and $prefix, which keeps element ids unique across modals. Values
    come back from old() only for the form that was submitted.
--}}
@php
    $mine = old('_form') === ($party ? 'party_edit_' . $party->id : 'party_create');
    $value = fn (string $field, $default = '') => $mine ? old($field, $default) : ($party->{$field} ?? $default);
@endphp
<div class="modal-body p-4">
    <div class="row g-3">
        <div class="col-md-8">
            <label for="{{ $prefix }}PartyName" class="form-label-custom">Party List Name <span class="text-danger">*</span></label>
            <input id="{{ $prefix }}PartyName" name="name" class="form-control-custom" value="{{ $value('name') }}" minlength="3" maxlength="100" placeholder="e.g. Abante Party" required>
        </div>
        <div class="col-md-4">
            <label for="{{ $prefix }}PartyAcronym" class="form-label-custom">Acronym</label>
            <input id="{{ $prefix }}PartyAcronym" name="acronym" class="form-control-custom text-uppercase" value="{{ $value('acronym') }}" maxlength="15" placeholder="e.g. ABANTE">
        </div>
        <div class="col-12">
            <label for="{{ $prefix }}PartyColor" class="form-label-custom">Party Colour <span class="text-danger">*</span></label>
            <div class="d-flex align-items-center gap-3">
                <input id="{{ $prefix }}PartyColor" type="color" name="color" class="form-control form-control-color" value="{{ $value('color', '#4F46E5') }}" title="Choose the party colour" required>
                <span class="text-muted small">Used for the party's badge on the ballot and in results.</span>
            </div>
        </div>
        <div class="col-12">
            <label for="{{ $prefix }}PartyDescription" class="form-label-custom">Platform / Advocacy <span class="text-muted fw-normal">(optional)</span></label>
            <textarea id="{{ $prefix }}PartyDescription" name="description" class="form-control-custom" rows="3" maxlength="1000" placeholder="A short statement of what the party stands for." style="resize:vertical;">{{ $value('description') }}</textarea>
        </div>
    </div>
</div>
