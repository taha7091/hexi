@extends('layouts.admin')

@section('title', 'Division Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Division Details</h3>
                    <div>
                        <a href="{{ route('admin.divisions.edit', $division) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.divisions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Divisions
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Name:</th>
                                    <td>
                                        @if($division->icon)
                                            <i class="{{ $division->icon }}" style="color: {{ $division->color ?? '#007bff' }}"></i>
                                        @endif
                                        {{ $division->name }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $division->category->brand->name }} - {{ $division->category->name }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($division->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Color:</th>
                                    <td>
                                        <span class="badge" style="background-color: {{ $division->color ?? '#007bff' }}">
                                            {{ $division->color ?? '#007bff' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order:</th>
                                    <td>{{ $division->sort_order ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $division->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated:</th>
                                    <td>{{ $division->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($division->description)
                                <h5>Description</h5>
                                <p class="text-muted">{{ $division->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Groups in this Division</h4>
                    <a href="{{ route('admin.groups.create', ['division_id' => $division->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Group
                    </a>
                </div>
                <div class="card-body">
                    @if($division->groups->count() > 0)
                        <div class="row">
                            @foreach($division->groups as $group)
                                <div class="col-md-4 mb-3">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="card-title">
                                                        @if($group->icon)
                                                            <i class="{{ $group->icon }}" style="color: {{ $group->color ?? '#007bff' }}"></i>
                                                        @endif
                                                        {{ $group->name }}
                                                    </h6>
                                                    <p class="card-text text-muted small">
                                                        {{ $group->products->count() }} products
                                                    </p>
                                                    @if($group->description)
                                                        <p class="card-text small">{{ Str::limit($group->description, 50) }}</p>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($group->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Groups Found</h5>
                            <p class="text-muted">This division doesn't have any groups yet.</p>
                            <a href="{{ route('admin.groups.create', ['division_id' => $division->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Group
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
