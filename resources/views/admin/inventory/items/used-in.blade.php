@extends('layouts.admin')
@section('title','Used In — '.$inventory_item->name)

@section('content')
<div class="header-actions" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <h2>Used In — {{ $inventory_item->name }}</h2>
    <div>
        <a href="{{ route('admin.inventory-items.index') }}" class="btn btn-secondary">Back to Items</a>
    </div>
</div>

<div class="table-container" style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
    <div style="margin-bottom:10px;color:#4a5568;">
        This inventory item is used in <strong>{{ $products->total() }}</strong> sales item(s).
    </div>

    <div class="table-responsive">
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:#667eea;color:#fff;">
                <tr>
                    <th style="padding:10px 12px;text-align:left;">Product</th>
                    <th style="padding:10px 12px;text-align:left;">SKU</th>
                    <th style="padding:10px 12px;text-align:left;">Barcode</th>
                    <th style="padding:10px 12px;text-align:left;width:160px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:10px 12px;">{{ $p->name }}</td>
                        <td style="padding:10px 12px;">{{ $p->sku }}</td>
                        <td style="padding:10px 12px;">{{ $p->barcode }}</td>
                        <td style="padding:10px 12px;">
                            <a href="{{ route('admin.products.recipes.edit', $p) }}" class="btn btn-sm btn-primary">View Recipe</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:12px;text-align:center;color:#718096;">No sales items found using this inventory item.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;">
        {{ $products->links() }}
    </div>
</div>
@endsection

