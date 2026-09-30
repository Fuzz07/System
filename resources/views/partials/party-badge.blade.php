{{--
    A candidate's party list as a small coloured pill, or a neutral
    "Independent" pill. Pass $party (a PartyList or null). Optional:
    $full = true to show the full name instead of the acronym.
--}}
@php $label = $party ? (!empty($full) ? $party->name : $party->short_name) : \App\Models\PartyList::INDEPENDENT; @endphp
@if($party)
<span class="d-inline-flex align-items-center gap-1 text-nowrap" title="{{ $party->name }}"
    style="background:{{ $party->color }}; color:{{ $party->text_color }}; font-size:0.68rem; font-weight:700; letter-spacing:0.3px; padding:2px 8px; border-radius:999px; line-height:1.5;">
    <i class="bi bi-flag-fill" aria-hidden="true"></i> {{ $label }}
</span>
@else
<span class="d-inline-flex align-items-center gap-1 text-nowrap" title="Running without a party list"
    style="background:#F1F5F9; color:#475569; border:1px solid #E2E8F0; font-size:0.68rem; font-weight:700; letter-spacing:0.3px; padding:1px 8px; border-radius:999px; line-height:1.5;">
    <i class="bi bi-person" aria-hidden="true"></i> {{ $label }}
</span>
@endif
