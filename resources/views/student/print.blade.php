@php
    function proposalDocumentNumber(int $id): string {
        return 'BP' . str_pad((string)$id, 3, '0', STR_PAD_LEFT);
    }

    function proposalDocumentDate($date): string {
        if (!$date) {
            return date('F j, Y');
        }
        return \Illuminate\Support\Carbon::parse($date)->format('F j, Y');
    }

    function proposalDocumentTitle(string $projectTitle): string {
        $title = trim($projectTitle);
        $title = preg_replace('/^budget\s+proposal\s+for\s+/i', '', $title);
        return 'BUDGET PROPOSAL FOR ' . strtoupper($title);
    }

    function proposalDocumentObjectives($source): array {
        $source = trim($source ?? '');
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $source))));

        if (count($lines) === 1) {
            $sentences = preg_split('/(?<=[.!?])\s+/', $lines[0]);
            if ($sentences && count($sentences) > 1) {
                $lines = array_values(array_filter(array_map('trim', $sentences)));
            }
        }

        if (empty($lines)) {
            $lines = [
                'To support the successful implementation of the proposed activity.',
                'To provide a transparent allocation of funds and expected expenses.',
                'To promote responsible financial planning and accountability.',
            ];
        }

        return array_slice($lines, 0, 8);
    }

    function proposalDocumentBudgetItems($source, $requestedBudget): array {
        $source = trim($source ?? '');
        $items = [];

        foreach (preg_split('/\r\n|\r|\n/', $source) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(.+?)(?:\s*[-:=]\s*|\s{2,})([0-9][0-9,]*(?:\.[0-9]+)?)$/', $line, $matches)) {
                $items[] = [
                    'label' => trim($matches[1]),
                    'amount' => (float)str_replace(',', '', $matches[2]),
                ];
            } else {
                $items[] = [
                    'label' => $line,
                    'amount' => 0.00,
                ];
            }
        }

        if (empty($items)) {
            $items[] = [
                'label' => 'Project Expenses',
                'amount' => (float)$requestedBudget,
            ];
        }

        return $items;
    }

    function sscSignatureName(string $rosterName): string {
        $nameParts = explode(',', $rosterName, 2);
        if (count($nameParts) === 2) {
            return strtoupper(trim($nameParts[1]) . ' ' . trim($nameParts[0]));
        }
        return strtoupper(trim($rosterName));
    }

    function sscOfficerNameByPosition(array $executiveOfficers, string $position): string {
        foreach ($executiveOfficers as $officer) {
            if (strcasecmp($officer['position'], $position) === 0) {
                return $officer['name'];
            }
        }
        return '';
    }

    $objectives = proposalDocumentObjectives($proposal->objectives ?? $proposal->description);
    $budgetItems = proposalDocumentBudgetItems($proposal->budget_items, $proposal->requested_budget);
    $itemTotal = array_sum(array_column($budgetItems, 'amount'));
    $requestedBudget = (float)$proposal->requested_budget;
    $eventDate = trim($proposal->proposal_event_date ?? '') ?: 'To be announced';
    $participants = !empty($proposal->participant_count) ? (int)$proposal->participant_count : null;

    $treasurerName = sscSignatureName(sscOfficerNameByPosition($sscExecutiveOfficers, 'Treasurer'));
    $presidentName = sscSignatureName(sscOfficerNameByPosition($sscExecutiveOfficers, 'President'));
    $adviserName = 'EMILY A. VILLACERAN, LPT, MAEDc';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ proposalDocumentTitle($proposal->project_title) }} | SSC Transparency System</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {
      background: #f1f5f9;
      color: #111827;
      font-family: Arial, Helvetica, sans-serif;
      margin: 0;
    }

    .proposal-print-toolbar {
      align-items: center;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin: 18px auto;
      max-width: 900px;
    }

    .proposal-print-toolbar a,
    .proposal-print-toolbar button {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      color: #1f2937;
      cursor: pointer;
      font: inherit;
      font-size: .88rem;
      font-weight: 700;
      padding: 9px 14px;
      text-decoration: none;
    }

    .proposal-print-toolbar button {
      background: #d97706;
      border-color: #d97706;
      color: #fff;
    }

    .proposal-print-sheet {
      background: #fff;
      box-shadow: 0 18px 45px rgba(15, 23, 42, .12);
      margin: 0 auto 28px;
      max-width: 900px;
      min-height: 1100px;
      padding: 68px 76px 62px;
    }

    .proposal-letterhead {
      align-items: center;
      display: grid;
      gap: 28px;
      grid-template-columns: 120px 1fr 120px;
      margin-bottom: 22px;
      text-align: center;
    }

    .proposal-seal {
      height: 86px;
      justify-self: center;
      object-fit: contain;
      width: 86px;
    }

    .proposal-school {
      font-size: 1.55rem;
      font-weight: 900;
      letter-spacing: .08em;
      line-height: 1.1;
      text-transform: uppercase;
    }

    .proposal-council {
      font-size: 1.12rem;
      font-weight: 800;
      letter-spacing: .06em;
      margin-top: 6px;
      text-transform: uppercase;
    }

    .proposal-meta {
      color: #4b5563;
      font-size: .74rem;
      line-height: 1.35;
      margin-top: 6px;
    }

    .proposal-title {
      font-size: 1.02rem;
      font-weight: 900;
      letter-spacing: .04em;
      margin: 28px 0 30px;
      text-align: center;
      text-transform: uppercase;
    }

    .proposal-dates {
      display: grid;
      font-size: .98rem;
      gap: 40px;
      grid-template-columns: 1fr 1fr;
      margin: 0 auto 62px;
      max-width: 690px;
    }

    .proposal-dates > div:last-child {
      text-align: center;
    }

    .proposal-objectives {
      font-size: .98rem;
      line-height: 1.35;
      margin: 0 auto 48px;
      max-width: 690px;
    }

    .budget-summary-title {
      font-size: 1rem;
      font-weight: 800;
      letter-spacing: .03em;
      margin-bottom: 0;
      text-align: center;
      text-transform: uppercase;
    }

    .proposal-budget-table {
      border-collapse: collapse;
      font-size: .98rem;
      margin: 0 auto 28px;
      max-width: 700px;
      table-layout: fixed;
      width: 100%;
    }

    .proposal-budget-table th,
    .proposal-budget-table td {
      border: 1px solid #111827;
      padding: 5px 8px;
    }

    .proposal-budget-table th {
      background: #fed7aa;
      font-size: 1rem;
      text-align: center;
      text-transform: uppercase;
    }

    .proposal-budget-table td:first-child {
      font-weight: 700;
      text-align: left;
    }

    .proposal-budget-table td:last-child {
      font-weight: 700;
      text-align: right;
    }

    .proposal-budget-table tr:not(.section-row):not(.total-row) td:first-child {
      font-weight: 500;
      padding-left: 135px;
    }

    .proposal-budget-table .total-row td:first-child {
      text-align: center;
    }

    .participants-line {
      font-size: 1rem;
      font-weight: 800;
      margin: 30px auto 72px;
      max-width: 700px;
    }

    .proposal-signatures {
      display: grid;
      gap: 86px;
      margin-left: 86px;
      max-width: 360px;
    }

    .proposal-signature-block {
      min-height: 94px;
      text-align: center;
    }

    .signature-label {
      margin-bottom: 48px;
      text-align: left;
    }

    .signature-name {
      border-bottom: 2px solid #111827;
      display: inline-block;
      font-weight: 900;
      line-height: 1.1;
      min-width: 235px;
      padding: 0 8px 2px;
      text-transform: uppercase;
    }

    .signature-role {
      font-size: .92rem;
      margin-top: 3px;
      text-transform: uppercase;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      @page {
        margin: 0.4in 0.5in;
        size: letter portrait;
      }

      body {
        background: #fff;
      }

      .proposal-print-toolbar {
        display: none !important;
      }

      .proposal-print-sheet {
        box-shadow: none;
        margin: 0;
        max-width: none;
        min-height: auto;
        padding: 0;
      }

      .proposal-letterhead {
        margin-bottom: 15px !important;
        gap: 15px !important;
      }

      .proposal-seal {
        height: 70px !important;
        width: 70px !important;
      }

      .proposal-school {
        font-size: 1.35rem !important;
      }

      .proposal-council {
        font-size: 1.05rem !important;
      }

      .proposal-title {
        margin: 15px 0 20px !important;
        font-size: 1rem !important;
      }

      .proposal-dates {
        margin: 0 auto 25px !important;
      }

      .proposal-objectives {
        margin: 0 auto 20px !important;
      }

      .proposal-budget-table {
        margin: 0 auto 15px !important;
      }

      .proposal-budget-table th,
      .proposal-budget-table td {
        padding: 4px 6px !important;
      }

      .participants-line {
        margin: 15px auto 25px !important;
      }

      .proposal-signatures {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 30px !important;
        margin-left: 86px !important;
        max-width: 360px !important;
      }

      .proposal-signature-block {
        min-height: 0 !important;
      }

      .signature-label {
        margin-bottom: 25px !important;
      }

      .signature-name {
        min-width: 235px !important;
        width: auto !important;
      }
    }
  </style>
</head>
<body>
  <div class="proposal-print-toolbar">
    <a href="javascript:history.back()"><i class="bi bi-arrow-left"></i> Back to Detail</a>
    <button type="button" onclick="window.print()"><i class="bi bi-printer"></i> Print Proposal</button>
  </div>

  <section class="proposal-print-sheet">
    <header class="proposal-letterhead">
      <img src="{{ asset('assets/images/mcc_logo.png') }}" alt="MCC Logo" class="proposal-seal mcc">
      <div>
        <div class="proposal-school">Madridejos Community College</div>
        <div class="proposal-council">Supreme Student Council</div>
        <div class="proposal-meta">
          Crossing Bunakan, Madridejos, Cebu<br>
          Gmail: sscmcc130@gmail.com<br>
          Fb page: Madridejos Community College - Supreme Student Council
        </div>
      </div>
      <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="SSC Logo" class="proposal-seal ssc">
    </header>

    <h1 class="proposal-title">{{ proposalDocumentTitle($proposal->project_title) }}</h1>

    <div class="proposal-dates">
      <div>Today's Date: {{ proposalDocumentDate($proposal->created_at) }}</div>
      <div>
        <strong>No. {{ proposalDocumentNumber($proposal->id) }}</strong><br>
        Date of Event: {{ $eventDate }}
      </div>
    </div>

    <div class="proposal-objectives">
      <strong>Objectives:</strong>
      @foreach ($objectives as $index => $objective)
        <div>{{ chr(97 + $index) }}. {{ $objective }}</div>
      @endforeach
    </div>

    <div class="budget-summary-title">Budget Summary Proposal</div>
    <table class="proposal-budget-table">
      <thead>
        <tr>
          <th>Particulars</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr class="section-row">
          <td>Budget for Event</td>
          <td></td>
        </tr>
        <tr class="total-row">
          <td>Total</td>
          <td>{!! \App\Helpers\SscHelper::formatCurrency($requestedBudget) !!}</td>
        </tr>
        <tr class="section-row">
          <td>Expenses</td>
          <td></td>
        </tr>
        @foreach ($budgetItems as $item)
          <tr>
            <td>{{ $item['label'] }}</td>
            <td>{{ $item['amount'] > 0 ? \App\Helpers\SscHelper::formatCurrency($item['amount']) : '' }}</td>
          </tr>
        @endforeach
        <tr class="total-row">
          <td>Total</td>
          <td>{!! \App\Helpers\SscHelper::formatCurrency($itemTotal ?: $requestedBudget) !!}</td>
        </tr>
      </tbody>
    </table>

    <div class="participants-line">
      Numbers of Participants: <strong>{{ $participants !== null ? $participants : '______' }}</strong>
    </div>

    <div class="proposal-signatures">
      <div class="proposal-signature-block">
        <div class="signature-label">Prepared by:</div>
        <div class="signature-name">{{ $treasurerName ?: ' ' }}</div>
        <div class="signature-role">SSC Treasurer</div>
      </div>

      <div class="proposal-signature-block">
        <div class="signature-label">Approved by:</div>
        <div class="signature-name">{{ $presidentName ?: ' ' }}</div>
        <div class="signature-role">SSC President</div>
      </div>

      <div class="proposal-signature-block">
        <div class="signature-label">Noted by:</div>
        <div class="signature-name">{{ $adviserName }}</div>
        <div class="signature-role">SSC Adviser</div>
      </div>
    </div>
  </section>
</body>
</html>
