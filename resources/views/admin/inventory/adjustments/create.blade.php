@extends('layouts.admin')

@section('title', 'New Inventory Adjustment')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">New Inventory Adjustment</h2>
    <a href="{{ route('admin.inventory-adjustments.index') }}" class="btn btn-outline-secondary">Back to Adjustments</a>
  </div>

  <div class="card">
    <div class="card-body">
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.inventory-adjustments.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Location</label>
            <select name="location_id" class="form-select" required>
              <option value="">Select location</option>
              @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected(old('location_id')==$loc->id)>{{ $loc->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label">Inventory Item</label>
            <select id="inventoryItem" class="form-select" style="width:100%"></select>
            <input type="hidden" name="inventory_item_id" id="inventory_item_id" value="{{ old('inventory_item_id') }}">
            <div class="form-text">Search for an inventory item by name or code.</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Adjustment Type</label>
            <select name="adjustment_type" class="form-select" id="adjType" required>
              <option value="add" @selected(old('adjustment_type')==='add')>Add</option>
              <option value="subtract" @selected(old('adjustment_type')==='subtract')>Subtract</option>
              <option value="set" @selected(old('adjustment_type')==='set')>Set</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label" id="qtyLabel">Quantity</label>
            <input type="number" step="0.000001" min="0" class="form-control" name="quantity" value="{{ old('quantity', 0) }}" required>
            <div class="form-text" id="qtyHelp">For SET, enter the final stock value.</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Reason (optional)</label>
            <input type="text" class="form-control" name="reason" value="{{ old('reason') }}" maxlength="255">
          </div>

          <div class="col-md-12">
            <label class="form-label">Notes (optional)</label>
            <textarea class="form-control" rows="2" name="notes">{{ old('notes') }}</textarea>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save Adjustment</button>
          <a href="{{ route('admin.inventory-adjustments.index') }}" class="btn btn-light">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function(){
  function initItemSelect() {
    const $el = $('#inventoryItem');
    $el.select2({
      placeholder: 'Search inventory items...',
      allowClear: true,
      ajax: {
        url: '{{ route('admin.recipes.search-inventory-items') }}',
        dataType: 'json',
        delay: 200,
        data: function (params) {
          return { q: params.term };
        },
        processResults: function (data) {
          return {
            results: data.map(function(item){
              return { id: item.id, text: item.name + (item.code ? ' ('+item.code+')' : '') };
            })
          };
        },
        cache: true
      }
    });

    $el.on('select2:select', function(e){
      $('#inventory_item_id').val(e.params.data.id);
    });

    $el.on('select2:clear', function(){
      $('#inventory_item_id').val('');
    });

    // Preselect if coming back after validation error
    var presetId = $('#inventory_item_id').val();
    if (presetId) {
      // Attempt to fetch the item text
      $.getJSON('{{ route('admin.recipes.search-inventory-items') }}', { q: presetId }, function(data){
        var found = data.find(function(i){ return String(i.id) === String(presetId); });
        if (found) {
          var option = new Option(found.name, found.id, true, true);
          $el.append(option).trigger('change');
        }
      });
    }
  }

  function wireTypeHelp() {
    function updateHelp() {
      var type = $('#adjType').val();
      var help = document.getElementById('qtyHelp');
      help.textContent = (type === 'set') ? 'For SET, enter the final stock value.' : 'Enter the quantity to add or subtract.';
    }
    $('#adjType').on('change', updateHelp);
    updateHelp();
  }

  document.addEventListener('DOMContentLoaded', function(){
    initItemSelect();
    wireTypeHelp();
  });
})();
</script>
@endpush

