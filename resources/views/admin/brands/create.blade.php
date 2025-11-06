@extends('layouts.admin')

@section('title', 'Add New Brand')

@section('content')
<style>
    .form-container {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        max-width: 500px;
        width: 100%;
        margin: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.15);
        outline: none;
    }

    .btn {
        border-radius: 8px;
        padding: 12px 20px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        display: inline-block;
        width: 100%; /* Full width by default for mobile-first approach */
        margin-top: 10px;
    }

    .btn-primary {
        background: #007bff;
        color: #fff;
        border: 1px solid #007bff;
    }

    .btn-secondary {
        background: #f8f9fa;
        color: #333;
        border: 1px solid #ced4da;
    }

    .form-actions {
        display: flex;
        flex-direction: column;
    }

    /* Responsive Media Queries */
    @media (min-width: 576px) { /* Small devices (landscape phones, 576px and up) */
        .form-actions {
            flex-direction: row-reverse; /* Buttons side-by-side */
            justify-content: flex-start;
        }

        .btn {
            width: auto; /* Auto width for larger screens */
            margin-top: 0;
            margin-left: 10px;
        }
    }
</style>

<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; background-color: #f4f6fb;">
    <div class="form-container">
        <h4 style="font-size: 1.5rem; color: #007bff; font-weight: 700; margin-bottom: 20px; text-align: center;">Add New Brand</h4>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-bottom: 0; margin-top: 10px; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.brands.store') }}" method="POST" autocomplete="off">
            @csrf
            <div style="margin-bottom: 20px;">
                <label for="company_id" class="form-label">Company <span style="color: red;">*</span></label>
                <input type="number" name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" value="{{ old('company_id') }}" required placeholder="Enter Company ID">
                @error('company_id')
                    <div style="color: red; font-size: 0.875em; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="name" class="form-label">Brand Name <span style="color: red;">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div style="color: red; font-size: 0.875em; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div style="color: red; font-size: 0.875em; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Brand</button>
                <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection