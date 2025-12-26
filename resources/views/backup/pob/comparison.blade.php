@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Plan vs Actual POB</h1>
        <p class="page-subtitle">Compare planned POB against actual attendance from catering</p>
    </div>

    <!-- Filter Form -->
    <div class="card">
        <form class="filter-bar" method="GET">
            <div class="form-group">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="form-group">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <select name="location" class="form-control">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ $location == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-value">{{ $totals['planned'] }}</div>
            <div class="stat-label">Planned</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $totals['actual'] }}</div>
            <div class="stat-label">Actual</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div class="stat-value"
                style="{{ $totals['present'] > 0 ? 'color: var(--success); -webkit-text-fill-color: var(--success);' : '' }}">
                {{ $totals['present'] }}
            </div>
            <div class="stat-label">Present</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-value"
                style="{{ $totals['absent'] > 0 ? 'color: var(--error); -webkit-text-fill-color: var(--error);' : '' }}">
                {{ $totals['absent'] }}
            </div>
            <div class="stat-label">Absent</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❓</div>
            <div class="stat-value"
                style="{{ $totals['unexpected'] > 0 ? 'color: var(--warning); -webkit-text-fill-color: var(--warning);' : '' }}">
                {{ $totals['unexpected'] }}
            </div>
            <div class="stat-label">Unexpected</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value {{ $totals['variance'] >= 100 ? 'variance-positive' : ($totals['variance'] >= 80 ? 'variance-neutral' : 'variance-negative') }}"
                style="-webkit-text-fill-color: {{ $totals['variance'] >= 100 ? 'var(--success)' : ($totals['variance'] >= 80 ? 'var(--warning)' : 'var(--error)') }};">
                {{ $totals['variance'] }}%
            </div>
            <div class="stat-label">Attendance Rate</div>
        </div>
    </div>

    <!-- Comparison Table by Location -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Comparison by Location</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Location</th>
                        <th class="text-center">Planned</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Present</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Unexpected</th>
                        <th class="text-center">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comparisonData as $loc => $data)
                        @php
                            $rate = $data['planned'] > 0 ? round(($data['actual'] / $data['planned']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $loc }}</strong></td>
                            <td class="text-center">{{ $data['planned'] }}</td>
                            <td class="text-center">{{ $data['actual'] }}</td>
                            <td class="text-center" style="color: var(--success);">{{ $data['present'] }}</td>
                            <td class="text-center" style="color: var(--error);">{{ $data['absent'] }}</td>
                            <td class="text-center" style="color: var(--warning);">{{ $data['unexpected'] }}</td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $rate >= 100 ? 'badge-success' : ($rate >= 80 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $rate }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 2rem; color: var(--text-muted);">
                                No data available for the selected filters
                            </td>
                        </tr>
                    @endforelse
                    @if(count($comparisonData) > 1)
                        <tr style="background: rgba(0, 168, 204, 0.1); font-weight: 600;">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-center">{{ $totals['planned'] }}</td>
                            <td class="text-center">{{ $totals['actual'] }}</td>
                            <td class="text-center" style="color: var(--success);">{{ $totals['present'] }}</td>
                            <td class="text-center" style="color: var(--error);">{{ $totals['absent'] }}</td>
                            <td class="text-center" style="color: var(--warning);">{{ $totals['unexpected'] }}</td>
                            <td class="text-center">
                                <span
                                    class="badge {{ $totals['variance'] >= 100 ? 'badge-success' : ($totals['variance'] >= 80 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $totals['variance'] }}%
                                </span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <!-- Absent Employees -->
        <div class="col col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="bi bi-x-circle" style="color: var(--error);"></i> Absent Employees
                    </h2>
                    <span class="badge badge-danger">{{ $totals['absent'] }}</span>
                </div>

                @if($absentEmployees->isEmpty())
                    <div class="text-center" style="padding: 2rem; color: var(--text-muted);">
                        <i class="bi bi-emoji-smile" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                        <p>No absent employees - everyone showed up!</p>
                    </div>
                @else
                    <div class="table-container">
                        <table id="absentTable">
                            <thead>
                                <tr>
                                    <th class="sortable">Employee</th>
                                    <th class="sortable">Department</th>
                                    <th class="sortable">Location</th>
                                    <th class="sortable">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($absentEmployees as $emp)
                                    <tr>
                                        <td>
                                            <strong>{{ $emp['name'] ?? 'N/A' }}</strong>
                                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                                {{ $emp['employee_number'] ?? '' }}
                                            </div>
                                        </td>
                                        <td>{{ $emp['department'] ?? 'N/A' }}</td>
                                        <td>{{ $emp['location'] ?? 'N/A' }}</td>
                                        <td>{{ $emp['employee_status'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Unexpected Employees -->
        <div class="col col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="bi bi-question-circle" style="color: var(--warning);"></i> Unexpected Attendees
                    </h2>
                    <span class="badge badge-warning">{{ $totals['unexpected'] }}</span>
                </div>

                @if($unexpectedEmployees->isEmpty())
                    <div class="text-center" style="padding: 2rem; color: var(--text-muted);">
                        <i class="bi bi-clipboard-check" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                        <p>No unexpected attendees - planning was accurate!</p>
                    </div>
                @else
                    <div class="table-container">
                        <table id="unexpectedTable">
                            <thead>
                                <tr>
                                    <th class="sortable">Employee</th>
                                    <th class="sortable">Department</th>
                                    <th class="sortable">Location</th>
                                    <th class="sortable">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unexpectedEmployees as $emp)
                                    <tr>
                                        <td>
                                            <strong>{{ $emp['name'] ?? 'N/A' }}</strong>
                                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                                {{ $emp['employee_number'] ?? '' }}
                                            </div>
                                        </td>
                                        <td>{{ $emp['department'] ?? 'N/A' }}</td>
                                        <td>{{ $emp['location'] ?? 'N/A' }}</td>
                                        <td>{{ $emp['employee_status'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card" style="margin-top: 1rem;">
        <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.9rem;">
            <div><strong style="color: var(--success);">●</strong> Present: Employees who were planned AND attended</div>
            <div><strong style="color: var(--error);">●</strong> Absent: Employees who were planned but did NOT attend</div>
            <div><strong style="color: var(--warning);">●</strong> Unexpected: Employees who attended but were NOT planned
            </div>
        </div>
    </div>

    <style>
        /* Mobile Responsive for Comparison Page */
        @media (max-width: 768px) {
            .row {
                flex-direction: column;
            }

            .col-6 {
                max-width: 100%;
            }

            table th:nth-child(n+4),
            table td:nth-child(n+4) {
                display: none;
            }

            table th,
            table td {
                padding: 0.4rem;
                font-size: 0.7rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .card div[style*="gap: 2rem"] {
                flex-direction: column;
                gap: 0.5rem !important;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @push('scripts')
        <script>
            // Generic table sorting for Absent and Unexpected tables
            function setupTableSorting(tableId) {
                const table = document.getElementById(tableId);
                if (!table) return;

                const headers = table.querySelectorAll('th.sortable');
                headers.forEach(header => {
                    header.addEventListener('click', function () {
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const isAsc = this.classList.contains('asc');

                        // Remove sort classes from all headers in this table
                        headers.forEach(h => h.classList.remove('asc', 'desc'));

                        // Add appropriate class to clicked header
                        this.classList.add(isAsc ? 'desc' : 'asc');

                        // Get column index
                        const colIndex = Array.from(this.parentElement.children).indexOf(this);

                        rows.sort((a, b) => {
                            let aVal = a.children[colIndex]?.textContent.trim() || '';
                            let bVal = b.children[colIndex]?.textContent.trim() || '';

                            return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    });
                });
            }

            setupTableSorting('absentTable');
            setupTableSorting('unexpectedTable');
        </script>
    @endpush
@endsection