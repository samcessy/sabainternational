<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1b1b18; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td { padding: 6px 0; border-bottom: 1px solid #e3e3e0; }
        td:first-child { color: #706f6c; width: 40%; }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }} — Donation Receipt</h1>

    <table>
        <tr><td>Receipt date</td><td>{{ now()->toFormattedDateString() }}</td></tr>
        <tr><td>Donor</td><td>{{ $donation->anonymous ? 'Anonymous' : $donation->supporter->name }}</td></tr>
        <tr><td>Amount</td><td>${{ number_format($donation->amount_cents / 100, 2) }} {{ $donation->currency }}</td></tr>
        <tr><td>Frequency</td><td>{{ $donation->frequency->value === 'monthly' ? 'Monthly (recurring)' : 'One-time' }}</td></tr>
        <tr><td>Designation</td><td>{{ $donation->program->name ?? 'General Fund' }}</td></tr>
        <tr><td>Transaction reference</td><td>{{ $transaction->gateway_reference }}</td></tr>
    </table>

    <p style="margin-top: 24px; color: #706f6c;">
        Please retain this receipt for your tax records.
    </p>
</body>
</html>
