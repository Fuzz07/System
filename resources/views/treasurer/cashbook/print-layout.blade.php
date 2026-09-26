{{-- Shared shell for printable cash book reports. Expects $report; sections: title, page-size, styles, content. --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title') | SSC Transparency System</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/ssc_logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root { --sheet-width: 850px; }
    body { background: #f1f5f9; color: #111827; font-family: Arial, Helvetica, sans-serif; margin: 0; }
    .report-toolbar { display: flex; gap: 10px; justify-content: flex-end; margin: 18px auto; max-width: var(--sheet-width); padding: 0 16px; }
    .report-toolbar a, .report-toolbar button { border: 1px solid #cbd5e1; border-radius: 8px; color: #1f2937; cursor: pointer; font: inherit; font-size: .88rem; font-weight: 700; padding: 9px 14px; text-decoration: none; background: #fff; }
    .report-toolbar button { background: #d97706; border-color: #d97706; color: #fff; }
    .report-sheet { background: #fff; box-shadow: 0 18px 45px rgba(15, 23, 42, .12); margin: 0 auto 28px; max-width: var(--sheet-width); padding: 48px 56px; box-sizing: border-box; }
    .amount { text-align: right; white-space: nowrap; }
    .signatories { display: grid; grid-template-columns: 1fr 1fr; gap: 40px 80px; margin: 56px auto 0; max-width: 680px; }
    .signatory { text-align: center; }
    .signatory-label { margin-bottom: 36px; }
    .signatory-name { border-bottom: 1.5px solid #111827; display: inline-block; font-weight: 700; min-width: 230px; padding: 0 6px 1px; }
    .signatory-role { margin-top: 2px; text-transform: uppercase; }
    @page { size: @yield('page-size'); margin: 0.4in 0.5in; }
    @media print {
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
      body { background: #fff; }
      .report-toolbar { display: none !important; }
      .report-sheet { box-shadow: none; margin: 0; max-width: none; padding: 0; }
      .signatories { margin-top: 36px; }
    }
    @yield('styles')
  </style>
</head>
<body>
  <div class="report-toolbar">
    <a href="{{ route('treasurer.cashbook', ['month' => $report->start->format('Y-m')]) }}"><i class="bi bi-arrow-left"></i> Back to Cash Book</a>
    <button type="button" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
  </div>

  <section class="report-sheet">
    @yield('content')

    <div class="signatories">
      @foreach ([
          ['Prepared by:', \App\Helpers\SscHelper::signatoryName('Treasurer'), 'SSC Treasurer'],
          ['Verified by:', \App\Helpers\SscHelper::signatoryName('Auditor'), 'SSC Auditor'],
          ['Approved by:', \App\Helpers\SscHelper::signatoryName('President'), 'SSC President'],
          ['Noted by:', config('ssc.adviser'), 'SSC Adviser'],
      ] as [$label, $name, $role])
        <div class="signatory">
          <div class="signatory-label">{{ $label }}</div>
          <div class="signatory-name">{{ $name ?: ' ' }}</div>
          <div class="signatory-role">{{ $role }}</div>
        </div>
      @endforeach
    </div>
  </section>
</body>
</html>
