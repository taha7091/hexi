@extends('layouts.admin')
@section('title','New Purchase Entry')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr auto;

    }

    

    .card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        padding: 15px 20px;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #2d3748;
    }

    .form-group label {
        font-weight: 600;
        color: #4a5568;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
    }

    .btn {
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        border: none;
    }

    .btn-outline-primary {
        border: 1px solid #667eea;
        color: #667eea;
        background: transparent;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        color: #fff;
    }

    .btn-outline-danger {
        border: 1px solid #e53e3e;
        color: #e53e3e;
        background: transparent;
    }

    .btn-outline-danger:hover {
        background: #e53e3e;
        color: #fff;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #667eea;
        color: white;
        font-weight: 600;
        font-size: 14px;
        padding: 10px;
    }

    td {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        padding: 10px;
        vertical-align: middle;
    }

    tfoot th {
        background: #f7fafc;
        font-weight: 700;
    }

    .text-right {
        text-align: right;
    }

    .select2-container .select2-selection--single {
        height: 38px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .select2-selection__rendered {
        line-height: 36px !important;
    }

    .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.inventory-purchases.store') }}" id="purchaseForm">
  @csrf

  {{-- PURCHASE DETAILS --}}
  <div class="card">
    <div class="">
      <h5>Purchase Details</h5>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save & Receive</button>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Supplier <span class="text-danger">*</span></label>
          <select name="supplier_id" class="form-control" required>
            <option value="">-- Select Supplier --</option>
            @foreach($suppliers as $s)
              <option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
            @endforeach
          </select>
          @error('supplier_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="form-group col-md-4">
          <label>Location <span class="text-danger">*</span></label>
          <select name="location_id" class="form-control" required>
            <option value="">-- Select Location --</option>
            @foreach($locations as $l)
              <option value="{{ $l->id }}" {{ old('location_id')==$l->id?'selected':'' }}>{{ $l->name }}</option>
            @endforeach
          </select>
          @error('location_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="form-group col-md-4">
          <label>Invoice # <span class="text-danger">*</span></label>
          <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}" required>
          @error('invoice_number')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Invoice Date</label>
          <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->format('Y-m-d')) }}">
        </div>

        <div class="form-group col-md-4">
          <label>Tax Amount</label>
          <input type="number" step="0.01" min="0" name="tax_amount" class="form-control" value="{{ old('tax_amount', 0) }}">
        </div>

        <div class="form-group col-md-4">
          <label>Notes</label>
          <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- PURCHASE ITEMS --}}
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5>Items</h5>
      <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
        <i class="fas fa-plus mr-1"></i> Add Item
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0" id="itemsTable">
          <thead>
            <tr>
              <th style="width:45%">Inventory Item</th>
              <th style="width:15%">Qty (buying unit)</th>
              <th style="width:20%">Unit Cost</th>
              <th style="width:20%" class="text-right">Line Total</th>
              <th style="width:5%"></th>
            </tr>
          </thead>
          <tbody></tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-right">Subtotal</th>
              <th class="text-right" id="subtotalCell">0.00</th>
              <th></th>
            </tr>
            <tr>
              <th colspan="3" class="text-right">Tax</th>
              <th class="text-right" id="taxCell">0.00</th>
              <th></th>
            </tr>
            <tr>
              <th colspan="3" class="text-right">Total</th>
              <th class="text-right" id="totalCell">0.00</th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="card-footer text-right">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save & Receive</button>
    </div>
  </div>
</form>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let rowIndex = 0;
function addRow() {
  const tbody = document.querySelector('#itemsTable tbody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select class="form-control inv-select" name="items[${rowIndex}][inventory_item_id]" required></select>
    </td>
    <td><input type="number" step="0.000001" min="0.000001" class="form-control qty-input" name="items[${rowIndex}][quantity_buying]" value="1" required></td>
    <td><input type="number" step="0.01" min="0" class="form-control cost-input" name="items[${rowIndex}][unit_cost]" value="0"></td>
    <td class="text-right line-total">0.00</td>
    <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); recalcTotals();">✕</button></td>
  `;
  tbody.appendChild(tr);
  initSelect2(tr.querySelector('.inv-select'));
  attachRowHandlers(tr);
  rowIndex++;
  recalcTotals();
}

function initSelect2(el) {
  $(el).select2({
    placeholder: 'Search inventory items...',
    minimumInputLength: 1,
    ajax: {
      url: '{{ route('admin.recipes.search-inventory-items') }}',
      dataType: 'json',
      delay: 250,
      data: function(params) { return { q: params.term } },
      processResults: function(data){ return data; },
      cache: true
    },
    width: '100%'
  });
}

function attachRowHandlers(tr){
  const qty = tr.querySelector('.qty-input');
  const cost = tr.querySelector('.cost-input');
  const update = () => {
    const q = parseFloat(qty.value || '0');
    const c = parseFloat(cost.value || '0');
    const lt = q * c;
    tr.querySelector('.line-total').innerText = lt.toFixed(2);
    recalcTotals();
  };
  qty.addEventListener('input', update);
  cost.addEventListener('input', update);
}

function recalcTotals(){
  let subtotal = 0;
  document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
    subtotal += parseFloat(tr.querySelector('.line-total').innerText || '0');
  });
  const tax = parseFloat(document.querySelector('input[name="tax_amount"]').value || '0');
  document.getElementById('subtotalCell').innerText = subtotal.toFixed(2);
  document.getElementById('taxCell').innerText = tax.toFixed(2);
  document.getElementById('totalCell').innerText = (subtotal + tax).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function(){
  addRow();
  document.querySelector('input[name="tax_amount"]').addEventListener('input', recalcTotals);
});
</script>
@endpush
