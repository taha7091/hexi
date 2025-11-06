@extends('layouts.admin')

@section('title', 'Inventory Adjustments')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Inventory Adjustments</h2>
    <a href="{{ route('admin.inventory-adjustments.create') }}" class="btn btn-primary">New Adjustment</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('admin.inventory-adjustments.index') }}" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Location</label>
          <select name="location_id" class="form-select">
            <option value="">All locations</option>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}" @selected(request('location_id')==$loc->id)>{{ $loc->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Item name/code</label>
          <input type="text" name="item" value="{{ request('item') }}" class="form-control" placeholder="Search item name or code">
        </div>
        <div class="col-md-2">
          <label class="form-label">Date from</label>
          <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Date to</label>
          <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Type</label>
          <select name="type" class="form-select">
            <option value="adjustments" @selected(($type ?? request('type','adjustments'))==='adjustments')>Adjustments only</option>
            <option value="purchases" @selected(($type ?? request('type'))==='purchases')>Purchases only</option>
            <option value="all" @selected(($type ?? request('type'))==='all')>All</option>
          </select>
        </div>
        <div class="col-12 d-flex gap-2">
          <button class="btn btn-outline-primary">Filter</button>
          <a href="{{ route('admin.inventory-adjustments.index') }}" class="btn btn-light">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th>Adj #</th>
              <th>Date</th>
              <th>Item</th>
              <th>Location</th>
              <th>Type</th>
              <th class="text-end">Before</th>
              <th class="text-end">Change</th>
              <th class="text-end">After</th>
              <th>Reason</th>
              <th>User</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($adjustments as $adj)
              <tr>
                <td><a href="{{ route('admin.inventory-adjustments.show', $adj) }}">{{ $adj->number ?? ('ADJ-' . str_pad($adj->id, 6, '0', STR_PAD_LEFT)) }}</a></td>
                <td>{{ $adj->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $adj->inventoryItem->name ?? '#' }}</td>
                <td>{{ $adj->location->name ?? '-' }}</td>
                <td><span class="badge bg-{{ $adj->adjustment_type === 'add' ? 'success' : ($adj->adjustment_type === 'subtract' ? 'danger' : 'secondary') }}">{{ ucfirst($adj->adjustment_type) }}</span></td>
                <td class="text-end">{{ number_format($adj->quantity_before, 6) }}</td>
                <td class="text-end">{{ number_format($adj->adjustment_amount, 6) }}</td>
                <td class="text-end">{{ number_format($adj->quantity_after, 6) }}</td>
                <td>{{ $adj->reason }}</td>
                <td>{{ $adj->user->name ?? '-' }}</td>
                <td><a href="{{ route('admin.inventory-adjustments.show', $adj) }}" class="btn btn-sm btn-outline-secondary">Preview</a></td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-center p-4">No adjustments found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-3">
        {{ $adjustments->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

