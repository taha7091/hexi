<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->sale_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .branch-info {
            font-size: 10px;
            margin-bottom: 3px;
        }
        .sale-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        .sale-info div {
            margin-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th,
        .items-table td {
            text-align: left;
            padding: 2px 0;
            font-size: 10px;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        .item-name {
            width: 60%;
        }
        .item-qty {
            width: 15%;
            text-align: center;
        }
        .item-price {
            width: 25%;
            text-align: right;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-bottom: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .payment-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 9px;
        }
        .thank-you {
            font-weight: bold;
            margin-bottom: 5px;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
                box-shadow: none;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $sale->branch->company->name }}</div>
            <div class="branch-info">{{ $sale->branch->name }}</div>
            @if($sale->branch->address)
                <div class="branch-info">{{ $sale->branch->address }}</div>
            @endif
            @if($sale->branch->phone)
                <div class="branch-info">Tel: {{ $sale->branch->phone }}</div>
            @endif
        </div>

        <!-- Sale Information -->
        <div class="sale-info">
            <div><strong>Receipt #:</strong> {{ $sale->sale_number }}</div>
            <div><strong>Date:</strong> {{ $sale->sale_date->format('M d, Y H:i') }}</div>
            <div><strong>Cashier:</strong> {{ $sale->user->name }}</div>
            <div><strong>POS:</strong> {{ $sale->posDevice->device_name }}</div>
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="item-name">Item</th>
                    <th class="item-qty">Qty</th>
                    <th class="item-price">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                    <tr>
                        <td class="item-name">
                            {{ $item->product->name }}
                            @if($item->discount_amount > 0)
                                <br><small>Discount: -${{ number_format($item->discount_amount, 2) }}</small>
                            @endif
                        </td>
                        <td class="item-qty">{{ $item->quantity }}</td>
                        <td class="item-price">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->discount_amount > 0)
                <div class="total-row">
                    <span>Discount:</span>
                    <span>-${{ number_format($sale->discount_amount, 2) }}</span>
                </div>
            @endif
            @if($sale->tax_amount > 0)
                <div class="total-row">
                    <span>Tax:</span>
                    <span>${{ number_format($sale->tax_amount, 2) }}</span>
                </div>
            @endif
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>${{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        @if($sale->payments->count() > 0)
            <div class="payment-info">
                <div><strong>Payment Details:</strong></div>
                @foreach($sale->payments as $payment)
                    <div>{{ ucfirst($payment->method) }}: ${{ number_format($payment->amount, 2) }}</div>
                @endforeach
                @php
                    $totalPaid = $sale->payments->sum('amount');
                    $change = $totalPaid - $sale->total_amount;
                @endphp
                @if($change > 0)
                    <div><strong>Change: ${{ number_format($change, 2) }}</strong></div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Thank You for Your Purchase!</div>
            <div>{{ $sale->branch->company->name }}</div>
            @if($sale->notes)
                <div style="margin-top: 10px;">
                    <strong>Notes:</strong> {{ $sale->notes }}
                </div>
            @endif
            <div style="margin-top: 10px;">
                Items: {{ $sale->saleItems->sum('quantity') }} | 
                Printed: {{ now()->format('M d, Y H:i') }}
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
