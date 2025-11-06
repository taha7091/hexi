@extends('layouts.admin')

@section('title', 'Customer Details - ERP System')
@section('page-title', 'Customer Details')
@section('breadcrumb', 'Home > Admin > Customers > ' . $customer->name)

@section('styles')
<style>
.container-fluid{
    padding: 20px;
    margin: 20px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-top: 20px;
}
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .btn {
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;    
        text-decoration: none;
        border: none;
        background: #667eea;
        color: #fff
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 30px; /* Increased gap */
    }

    .card-header {
        background: #667eea;
        color: #fff;
        font-weight: 600;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        padding: 12px 20px;
    }

    .card-body {
        padding: 20px;
        font-size: 14px;
    }

    .summary-badge {
        display: inline-block;
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

    .header-actions .btn {
        margin-left: 10px;
    }

    .info-label {
        font-weight: 600;
        color: #2d3748;
    }

    .info-value {
        color: #4a5568;
    }

    /* Extra spacing between sections */
    .section-gap {
        margin-bottom: 40px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 header-actions">
        <h1 class="h3 mb-0 text-gray-800">Customer: {{ $customer->name }}</h1>
        <div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Contact Info + Notes -->
        <div class="col-md-8">
            <div class="card section-gap">
                <div class="card-header">Contact Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <span class="info-label">Email:</span> <span class="info-value">{{ $customer->email ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="info-label">Phone:</span> <span class="info-value">{{ $customer->phone ?? '-' }}</span>
                        </div>
                        <div class="col-12 mb-3">
                            <span class="info-label">Address:</span> <span class="info-value">{{ $customer->address ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="info-label">City:</span> <span class="info-value">{{ $customer->city ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="info-label">Country:</span> <span class="info-value">{{ $customer->country ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-gap">
                <div class="card-header">Notes</div>
                <div class="card-body">
                    <p class="mb-0">{{ $customer->notes ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="col-md-4">
            <div class="card section-gap">
                <div class="card-header">Summary</div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="info-label">Code:</span> <span class="info-value">{{ $customer->code ?? '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="info-label">Group:</span> <span class="info-value">{{ optional($customer->group)->name ?? '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="info-label">Status:</span>
                        <span class="summary-badge {{ $customer->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mb-0">
                        <span class="info-label">Balance:</span> <span class="info-value">${{ number_format($customer->balance, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
