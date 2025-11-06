@extends('layouts.admin')

@section('title', 'Role Setup')
@section('page-title', 'Role Setup')

@section('content')
<div class="header-actions">
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        ➕ Create New Role
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Users Assigned</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td>{{ Str::limit($role->description ?: 'No description', 50) }}</td>
                <td>
                    <span class="status-badge {{ $role->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>{{ $role->users->count() }}</td>
                <td>{{ $role->creator->name ?? '-' }}</td>
                <td>{{ $role->created_at->format('M j, Y') }}</td>
                <td>
                    <a href="{{ route('admin.roles.privileges', $role) }}" class="btn btn-sm btn-primary">Privileges</a>
                    
                    @if($role->users->count() === 0)
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    @else
                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete role with assigned users">Delete</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#718096;">No roles found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

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
    justify-content: flex-end;
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

.btn-danger {
    background: #dc3545;
    color: #fff;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

@media (max-width: 768px) {
    .header-actions {
        justify-content: center;
    }
}
</style>
@endsection
