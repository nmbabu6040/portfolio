<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 36px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.45;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
            color: #4f46e5;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .r {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .items th {
            background: #eef0ff;
            text-align: left;
            padding: 8px;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .tot td {
            padding: 4px 8px;
        }

        .grand td {
            font-size: 13px;
            font-weight: bold;
            border-top: 2px solid #4f46e5;
            padding-top: 8px;
        }

        .box {
            background: #f8f9ff;
            padding: 10px 12px;
        }

        .h {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    @php
        $fmt = fn($v) => $d->currency . ' ' . number_format($v, 2);
        $date = fn($v) => \Carbon\Carbon::parse($v)->format('d M Y');
    @endphp
    <table>
        <tr>
            <td style="width:55%;vertical-align:top">
                @if ($logo)
                    <img src="{{ $logo }}" style="max-height:48px;max-width:200px"><br>
                @endif
                <b style="font-size:14px">{{ $s['name'] ?? config('app.name') }}</b><br>
                @if (!empty($s['company_address']))
                    {!! nl2br(e($s['company_address'])) !!}<br>
                @endif
                @if (!empty($s['email']))
                    {{ $s['email'] }}<br>
                @endif
                @if (!empty($s['phone']))
                    {{ $s['phone'] }}<br>
                @endif
                @if (!empty($s['tax_id']))
                    <span class="muted">Tax ID:</span> {{ $s['tax_id'] }}
                @endif
            </td>
            <td class="r" style="vertical-align:top">
                <h1>{{ strtoupper($label) }}</h1>
                <b>{{ $d->number }}</b><br>
                <span class="muted">Date:</span> {{ $date($d->issue_date) }}<br>
                @if ($d->due_date)
                    <span class="muted">{{ $d->type == 'quotation' ? 'Valid until' : 'Due date' }}:</span>
                    {{ $date($d->due_date) }}<br>
                @endif
                @if ($d->status == 'paid')
                    <b style="color:#16a34a">PAID</b>
                @endif
            </td>
        </tr>
    </table>

    <table style="margin-top:22px">
        <tr>
            <td class="box">
                <div class="h">{{ $d->type == 'quotation' ? 'Prepared for' : 'Bill to' }}</div>
                <b>{{ $d->client_name }}</b><br>
                @if ($d->client_address)
                    {!! nl2br(e($d->client_address)) !!}<br>
                @endif
                @if ($d->client_email)
                    {{ $d->client_email }}<br>
                @endif
                @if ($d->client_phone)
                    {{ $d->client_phone }}
                @endif
            </td>
        </tr>
    </table>

    <table class="items" style="margin-top:22px">
        <thead>
            <tr>
                <th>Description</th>
                <th class="r" style="width:50px">Qty</th>
                <th class="r" style="width:90px">Price</th>
                <th class="r" style="width:100px">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i)
                <tr>
                    <td>{{ $i->description }}</td>
                    <td class="r">{{ rtrim(rtrim(number_format($i->qty, 2, '.', ''), '0'), '.') }}</td>
                    <td class="r">{{ number_format($i->price, 2) }}</td>
                    <td class="r">{{ number_format($i->qty * $i->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top:10px">
        <tr>
            <td style="width:55%"></td>
            <td>
                <table class="tot">
                    <tr>
                        <td class="muted">Subtotal</td>
                        <td class="r">{{ $fmt($t['sub']) }}</td>
                    </tr>
                    @if ($t['discount'] > 0)
                        <tr>
                            <td class="muted">Discount</td>
                            <td class="r">- {{ $fmt($t['discount']) }}</td>
                        </tr>
                    @endif
                    @if ($d->tax_percent > 0)
                        <tr>
                            <td class="muted">Tax
                                ({{ rtrim(rtrim(number_format($d->tax_percent, 2, '.', ''), '0'), '.') }}%)</td>
                            <td class="r">{{ $fmt($t['tax']) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td>Total</td>
                        <td class="r">{{ $fmt($t['total']) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($d->notes)
        <div style="margin-top:22px">
            <div class="h">Notes</div>{!! nl2br(e($d->notes)) !!}
        </div>
    @endif
    @if ($d->type == 'invoice' && !empty($s['payment_details']))
        <div style="margin-top:16px">
            <div class="h">Payment details</div>{!! nl2br(e($s['payment_details'])) !!}
        </div>
    @endif
    @if ($d->terms)
        <div style="margin-top:16px">
            <div class="h">Terms</div>{!! nl2br(e($d->terms)) !!}
        </div>
    @endif
</body>

</html>
