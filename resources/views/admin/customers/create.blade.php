 @extends('layouts.admin') 
@section('title', isset($customer) ? 'Edit Customer' : 'Create Customer')
@section('styles')
<style>
  
    .container-fluid a.btn {
        margin-left: 10px;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        
        border: none;
        background: #667eea;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;

    }

    .container-fluid h1 {
        margin: 0;
    }

    .container-fluid {
        padding: 20px;
        margin: 20px;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
    }


    .card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin: 30px;
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
        padding: 25px;
    }

    .form-group label {
        font-weight: 600;
        color: #2d3748;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        padding: 10px;
        font-size: 14px;
    }

    .form-check-label {
        font-weight: 600;
    }

    .btn-primary {
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
        font-size: 14px;
    }

    .btn-secondary {
        background: #f1f1f1;
        color: #2c3e50;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
    }

    .section-gap {
        margin-bottom: 40px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div>
        <h1 >{{ isset($customer) ? 'Edit Customer' : 'Create Customer' }}</h1>
        <a href="{{ route('admin.customers.index') }}" class="btn">
             Back to Customers
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            {{ isset($customer) ? 'Edit Customer Details' : 'New Customer Details' }}
        </div>
        <div class="card-body">
            <form action="{{ isset($customer) ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" method="POST">
                @csrf
                @if(isset($customer))
                    @method('PUT')
                @endif

                <!-- Basic Info -->
                <div class="section-gap">
                    <h5 class="mb-3">Basic Information</h5>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="customer_group_id">Group</label>
                            <select name="customer_group_id" id="customer_group_id" class="form-control">
                                <option value="">-- None --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('customer_group_id', $customer->customer_group_id ?? '') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_group_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="code">Customer Code</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $customer->code ?? '') }}" class="form-control" placeholder="Optional code">
                            @error('code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $customer->name ?? '') }}" class="form-control" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="section-gap">
                    <h5 class="mb-3">Contact Information</h5>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}" class="form-control">
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="form-control">
                            @error('phone')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="balance">Balance</label>
                            <input type="number" step="0.01" name="balance" id="balance" value="{{ old('balance', $customer->balance ?? 0) }}" class="form-control">
                            @error('balance')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="section-gap">
                    <h5 class="mb-3">Address</h5>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="address">Street Address</label>
                            <input type="text" name="address" id="address" value="{{ old('address', $customer->address ?? '') }}" class="form-control">
                            @error('address')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $customer->city ?? '') }}" class="form-control">
                            @error('city')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="country">Country</label>
                            <input type="text" name="country" id="country" value="{{ old('country', $customer->country ?? '') }}" class="form-control">
                            @error('country')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes and Status -->
                <div class="section-gap">
                    <h5 class="mb-3">Other Details</h5>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mt-3">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
