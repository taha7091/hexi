@extends('layouts.admin')

@section('title', 'Stock Count')

@section('styles')
<style>
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    thead {
        background: #667eea;
        color: white;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    tbody tr:hover {
        background: #f7f9fc;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #c6f6d5;
        color: #22543d;
    }

    .status-inactive {
        background: #fed7d7;
        color: #742a2a;
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .search-filters {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto;
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        font-size: 14px;
    }

    .btn {
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: #fff;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    .alert {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 15px 20px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="header-actions">
    <h2>Stock Count</h2>
    <div class="d-flex gap-2">
        @if(isset($batchAdjustments) || isset($items))
            <a href="{{ route('admin.inventory-adjustments.count') }}" class="btn btn-secondary">History</a>
        @endif
        @if(!isset($items))
            <a href="{{ route('admin.inventory-adjustments.count', ['new' => 1]) }}" class="btn btn-primary">New Stock Count</a>
        @endif
        <a href="{{ route('admin.inventory-items.index') }}" class="btn btn-secondary">Items</a>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('created_adjustments') && is_array(session('created_adjustments')) && count(session('created_adjustments')))
    <div class="alert alert-info">
        <div class="fw-bold mb-1">Applied adjustments:</div>
        <div class="d-flex flex-wrap gap-2">
            @foreach(session('created_adjustments') as $aid)
                <a href="{{ route('admin.inventory-adjustments.show', $aid) }}" class="badge bg-light border">
                    {{ 'ADJ-' . str_pad($aid, 6, '0', STR_PAD_LEFT) }}
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- Filters --}}
@if(isset($items))
<div class="search-filters">
    <form method="GET" action="{{ route('admin.inventory-adjustments.count') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label>Search Items</label>
                <input type="hidden" name="new" value="1">
                <input type="text" name="search" placeholder="Name / Code / Barcode" value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </div>
    </form>
</div>
@endif

{{-- Batch History or Items --}}
@if(isset($batchAdjustments))
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Location</th>
                <th class="text-end">Before</th>
                <th class="text-end">Change</th>
                <th class="text-end">After</th>
                <th>User</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batchAdjustments as $adj)
                <tr>
                    <td>{{ $adj->inventoryItem->name ?? '#' }}</td>
                    <td>{{ $adj->location->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($adj->quantity_before, 6) }}</td>
                    <td class="text-end">{{ number_format($adj->adjustment_amount, 6) }}</td>
                    <td class="text-end">{{ number_format($adj->quantity_after, 6) }}</td>
                    <td>{{ $adj->user->name ?? '-' }}</td>
                    <td>{{ $adj->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@elseif(isset($groups))
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Batch</th>
                <th>Date</th>
                <th>Location</th>
                <th>Items</th>
                <th>User</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $g)
                <tr>
                    <td><code>{{ $g['code'] }}</code></td>
                    <td>{{ \Illuminate\Support\Carbon::parse($g['created_at'])->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($locationsMap->get($g['location_id']))->name ?? '-' }}</td>
                    <td>{{ $g['count'] }}</td>
                    <td>{{ optional($usersMap->get($g['user_id']))->name ?? '-' }}</td>
                    <td class="text-end"><a href="{{ route('admin.inventory-adjustments.count', ['batch' => $g['code']]) }}" class="btn btn-sm btn-secondary">Preview</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center p-4">No stock count history yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@elseif(isset($items))
<form method="POST" action="{{ route('admin.inventory-adjustments.count-apply') }}">
    @csrf
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Unit</th>
                    <th class="text-end">On Hand (System)</th>
                    <th class="text-end">New On Hand</th>
                    <th class="text-end">Variance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr data-item-id="{{ $item->id }}">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->stockUnit->symbol ?? '' }}</td>
                    <td class="text-end current">{{ number_format($item->current_stock,6) }}</td>
                    <td class="text-end">
                        <input type="number" name="new_qty[{{ $item->id }}]" class="form-control form-control-sm text-end new-qty"
                               step="0.000001" min="0" placeholder="{{ number_format($item->current_stock,6) }}">
                    </td>
                    <td class="text-end variance">0.000000</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Apply Adjustments</button>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
(function(){
    function recalcRow(tr){
        const curText = tr.querySelector('.current').textContent.replace(/,/g,'');
        const cur = parseFloat(curText) || 0;
        const input = tr.querySelector('.new-qty');
        const nv = parseFloat(input.value);
        const newVal = !isNaN(nv) ? nv : cur;
        tr.querySelector('.variance').textContent = (newVal - cur).toFixed(6);
    }

    document.addEventListener('input', function(e){
        if(e.target && e.target.classList.contains('new-qty')){
            const tr = e.target.closest('tr');
            recalcRow(tr);
        }
    });
})();
</script>
@endpush
