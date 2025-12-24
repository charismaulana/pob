@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage user approvals and roles</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card {{ $status === 'all' ? 'stat-highlight' : '' }}">
            <a href="{{ route('admin.users', ['status' => 'all']) }}" style="text-decoration: none; color: inherit;">
                <div class="stat-icon">👥</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Users</div>
            </a>
        </div>
        <div class="stat-card {{ $status === 'pending' ? 'stat-highlight' : '' }}">
            <a href="{{ route('admin.users', ['status' => 'pending']) }}" style="text-decoration: none; color: inherit;">
                <div class="stat-icon">⏳</div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending Approval</div>
            </a>
        </div>
        <div class="stat-card {{ $status === 'approved' ? 'stat-highlight' : '' }}">
            <a href="{{ route('admin.users', ['status' => 'approved']) }}" style="text-decoration: none; color: inherit;">
                <div class="stat-icon">✅</div>
                <div class="stat-value">{{ $stats['approved'] }}</div>
                <div class="stat-label">Approved</div>
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="bi bi-people"></i>
                @if($status === 'pending')
                    Pending Users
                @elseif($status === 'approved')
                    Approved Users
                @else
                    All Users
                @endif
            </h2>
        </div>

        @if($users->isEmpty())
            <div class="text-center" style="padding: 3rem; color: var(--text-muted);">
                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                <p>Tidak ada user untuk ditampilkan.</p>
            </div>
        @else
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $user->email }}</div>
                                </td>
                                <td>{{ $user->department ?? 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('admin.users.updateRole', $user) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="form-control"
                                            style="width: auto; padding: 0.3rem 0.5rem; font-size: 0.8rem;"
                                            onchange="this.form.submit()">
                                            <option value="department_user" {{ $user->role === 'department_user' ? 'selected' : '' }}>
                                                User</option>
                                            <option value="gs" {{ $user->role === 'gs' ? 'selected' : '' }}>GS Staff</option>
                                            <option value="superadmin" {{ $user->role === 'superadmin' ? 'selected' : '' }}>Super
                                                Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    @if($user->is_approved)
                                        <span class="badge badge-success">Approved</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        @if(!$user->is_approved)
                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.revoke', $user) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning" title="Revoke"
                                                    onclick="return confirm('Cabut persetujuan user ini?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$user->isSuperAdmin())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Hapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div style="margin-top: 1rem; display: flex; justify-content: center;">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>

    <style>
        .stat-highlight {
            background: linear-gradient(135deg, rgba(0, 168, 204, 0.2), rgba(0, 168, 204, 0.1)) !important;
            border-color: var(--primary) !important;
        }
    </style>
@endsection