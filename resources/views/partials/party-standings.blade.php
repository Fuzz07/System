{{--
    Party list standings for a results screen. Expects $partyStandings (from
    ElectionResultsService) and $final (bool): true once results are announced
    or the year is archived, so seats read as "won" rather than "leading".
--}}
@if(!empty($partyStandings))
<div class="card border-0 shadow-sm mb-4" style="border-radius:20px; overflow:hidden;">
    <div class="card-header bg-light py-3 px-4 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-flag-fill text-primary me-1"></i> Party List Standings</h5>
        <span class="text-muted small">{{ $final ? 'Seats won' : 'Seats currently leading' }} across all positions</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Party List</th>
                        <th class="text-center">{{ $final ? 'Seats Won' : 'Leading In' }}</th>
                        <th class="text-center">Candidates</th>
                        <th class="text-end pe-4">Total Votes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partyStandings as $row)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @include('partials.party-badge', ['party' => $row['party']])
                                @if($row['party'] && $row['party']->acronym)
                                <span class="text-muted small">{{ $row['party']->name }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center fw-bold {{ $row['seats'] ? 'text-success' : 'text-muted' }}">{{ $row['seats'] }}</td>
                        <td class="text-center">{{ $row['candidates'] }}</td>
                        <td class="text-end pe-4 fw-semibold">{{ number_format($row['votes']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
