<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Items Stock Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
        h2 { margin: 0 0 12px 0; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f4f6f8; text-align: left; }
        tfoot td { font-weight: bold; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>Inventory Items Stock Report</h2>
    <div class="meta">Generated at: {{ $generatedAt->format('Y-m-d H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Brand</th>
                <th>Location</th>
                <th>Unit</th>
                <th class="right">Current Stock</th>
                <th class="right">Min Stock</th>
                <th class="right">Cost Price</th>
                <th class="right">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @php($grand = 0)
            @foreach($items as $item)
                @php($value = (float)$item->current_stock * (float)$item->cost_price)
                @php($grand += $value)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ optional($item->brand)->name }}</td>
                    <td>{{ optional($item->location)->name }}</td>
                    <td>{{ optional($item->stockUnit)->symbol ?? optional($item->stockUnit)->name }}</td>
                    <td class="right">{{ number_format((float)$item->current_stock, 3) }}</td>
                    <td class="right">{{ number_format((float)$item->minimum_stock, 3) }}</td>
                    <td class="right">{{ number_format((float)$item->cost_price, 2) }}</td>
                    <td class="right">{{ number_format($value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="right">Grand Total</td>
                <td class="right">{{ number_format($grand, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

