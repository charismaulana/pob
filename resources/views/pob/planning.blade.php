@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1 class="page-title">Planning POB</h1>
                <p class="page-subtitle">Schedule employees with ON (arrival) and OFF (departure) dates</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" onclick="openAddModal()">
                    <i class="bi bi-plus-circle"></i> Add Schedule
                </button>
                <button type="button" class="btn btn-success" onclick="openExportModal()">
                    <i class="bi bi-calendar3" style="color: black;"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Summary: Currently On Board Today -->
    <div class="stats-grid">
        @foreach($locations as $loc)
            <div class="stat-card">
                <div class="stat-icon">📍</div>
                <div class="stat-value">{{ $onBoardByLocation[$loc] ?? 0 }}</div>
                <div class="stat-label">{{ $loc }}</div>
            </div>
        @endforeach
        <div class="stat-card stat-highlight">
            <div class="stat-icon">📊</div>
            <div class="stat-value">{{ array_sum($onBoardByLocation) }}</div>
            <div class="stat-label">Total On Board Today</div>
        </div>
    </div>

    <!-- Schedules List -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Employee Schedules</h2>
            <form class="filter-bar" style="margin-bottom: 0; align-items: center;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" name="search" class="form-control" placeholder="Search employee..."
                        value="{{ $search }}" style="width: 180px;" onchange="this.form.submit()">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select name="department" class="form-control" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select name="location" class="form-control" onchange="this.form.submit()">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ $location == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($schedules->isEmpty())
            <div class="text-center" style="padding: 3rem; color: var(--text-muted);">
                <i class="bi bi-calendar-x" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                <p>No schedules found</p>
            </div>
        @else
            <div class="table-container">
                <table id="schedulesTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="name">Employee</th>
                            <th class="sortable" data-sort="department">Department</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th class="sortable" data-sort="location">Location</th>
                            <th class="sortable" data-sort="start_date">ON Date</th>
                            <th class="sortable" data-sort="end_date">OFF Date</th>
                            <th class="sortable" data-sort="board_status">Board Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                            @php
                                $isActive = $schedule->isCurrentlyActive();
                            @endphp
                            <tr style="{{ $isActive ? 'background: rgba(0, 255, 136, 0.05);' : '' }}">
                                <td>
                                    <strong>{{ $schedule->employee->name ?? 'N/A' }}</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                        {{ $schedule->employee->employee_number ?? '' }}
                                    </div>
                                </td>
                                <td>{{ $schedule->employee->department ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-secondary" style="font-size: 0.75rem;">
                                        {{ $schedule->employee->employee_status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $schedule->location }}</td>
                                <td>
                                    <span style="color: var(--success);">{{ $schedule->start_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    @if($schedule->end_date)
                                        <span style="color: var(--error);">{{ $schedule->end_date->format('d M Y') }}</span>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            {{ $schedule->duration }} days
                                        </div>
                                    @else
                                        <span class="badge badge-primary">Ongoing</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isActive)
                                        <span class="badge badge-success">On Board</span>
                                    @elseif($schedule->status === 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @elseif($schedule->start_date > now())
                                        <span class="badge badge-warning">Upcoming</span>
                                    @else
                                        <span class="badge"
                                            style="background: rgba(128,128,128,0.15); color: #888; border: 1px solid rgba(128,128,128,0.3);">Completed</span>
                                    @endif
                                </td>
                                <td class="actions">
                                    <button type="button" class="btn btn-sm btn-secondary" title="Edit"
                                        onclick="openEditModal({{ $schedule->id }}, '{{ $schedule->employee->name ?? 'N/A' }}', '{{ $schedule->employee->employee_number ?? '' }}', '{{ $schedule->start_date->format('Y-m-d') }}', '{{ $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '' }}', '{{ $schedule->location }}', '{{ addslashes($schedule->notes ?? '') }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($isActive && !$schedule->end_date)
                                        <form action="{{ route('pob.updatePlanning', $schedule) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Set OFF to today"
                                                onclick="return confirm('Set OFF date to today?')">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('pob.destroyPlanning', $schedule) }}" method="POST"
                                        style="display: inline;" onsubmit="return confirm('Remove this schedule?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-container"
                style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div class="pagination-info" style="color: var(--text-muted); font-size: 0.85rem;">
                    Showing {{ $schedules->firstItem() ?? 0 }} to {{ $schedules->lastItem() ?? 0 }} of {{ $schedules->total() }}
                    entries
                    <span style="margin-left: 1rem;">|</span>
                    <span style="margin-left: 1rem;">Page {{ $schedules->currentPage() }} of {{ $schedules->lastPage() }}</span>
                </div>
                <div class="pagination-links">
                    {{ $schedules->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Add Schedule Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>Add Employee Schedule</h3>
                <button type="button" class="close-btn" onclick="closeAddModal()">&times;</button>
            </div>
            <form action="{{ route('pob.storePlanning') }}" method="POST" id="scheduleForm">
                @csrf

                <div class="row">
                    <div class="col col-6">
                        <!-- Department Filter -->
                        <div class="form-group">
                            <label class="form-label">Filter by Department</label>
                            <select id="departmentFilter" class="form-control">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col col-6">
                        <!-- Location -->
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <select name="location" id="locationSelect" class="form-control" required>
                                <option value="">Select Location</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                            <small id="locationHint" class="form-hint" style="display: none;"></small>
                        </div>
                    </div>
                </div>

                <!-- Searchable Employee Select -->
                <div class="form-group">
                    <label class="form-label">Select Employee</label>
                    <div class="search-select-container">
                        <input type="text" id="employeeSearch" class="form-control"
                            placeholder="Type to search employee name..." autocomplete="off">
                        <div class="employee-dropdown" id="employeeDropdown">
                            @foreach($employees as $employee)
                                <div class="employee-option" data-id="{{ $employee['id'] }}" data-name="{{ $employee['name'] }}"
                                    data-number="{{ $employee['employee_number'] ?? '' }}"
                                    data-department="{{ $employee['department'] ?? '' }}"
                                    data-status="{{ $employee['employee_status'] ?? '' }}"
                                    data-location="{{ $employee['location'] ?? '' }}">
                                    <strong>{{ $employee['name'] }}</strong>
                                    <span class="employee-info">{{ $employee['employee_number'] ?? '' }} •
                                        {{ $employee['department'] ?? 'N/A' }} •
                                        <em>{{ $employee['employee_status'] ?? 'N/A' }}</em></span>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="employee_id" id="selectedEmployeeId" required>
                    </div>
                    <div id="selectedEmployee" class="selected-employee" style="display: none;">
                        <span id="selectedEmployeeName"></span>
                        <button type="button" class="clear-btn" onclick="clearEmployee()">&times;</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label class="form-label">📅 ON Date (Arrival)</label>
                            <input type="date" name="start_date" id="startDate" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label class="form-label">📅 OFF Date (Departure)</label>
                            <input type="date" name="end_date" id="endDate" class="form-control" value="">
                            <small class="form-hint">Auto: +19 days from ON date</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g., Project X, Rotation 1, etc.">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Excel Modal -->
    <div class="modal-overlay" id="exportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="bi bi-calendar3" style="color: white;"></i> Export to Excel</h3>
                <button type="button" class="close-btn" onclick="closeExportModal()">&times;</button>
            </div>
            <form action="{{ route('pob.export') }}" method="GET">
                <div class="form-group">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control month-picker" value="{{ date('Y-m') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <select name="location" class="form-control">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="form-hint" style="margin-bottom: 1rem;">
                    📊 Calendar-style export: Green numbered days (1,2,3...) = On-site, Red "R" = Off-site
                </p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeExportModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Download Excel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Schedule</h3>
                <button type="button" class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="form-label">Employee</label>
                    <div class="employee-info-display">
                        <strong id="editEmployeeName"></strong>
                        <span id="editEmployeeNumber"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <select name="location" id="editLocation" class="form-control" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label class="form-label">📅 ON Date</label>
                            <input type="date" name="start_date" id="editStartDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label class="form-label">📅 OFF Date</label>
                            <input type="date" name="end_date" id="editEndDate" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" id="editNotes" class="form-control" placeholder="Optional notes...">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .stat-highlight {
            background: linear-gradient(135deg, rgba(0, 168, 204, 0.2), rgba(0, 168, 204, 0.1)) !important;
            border-color: var(--primary) !important;
        }

        /* Table column alignment */
        table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border-bottom: 1px solid var(--card-border) !important;
            vertical-align: middle;
        }

        table th:nth-child(1),
        table td:nth-child(1) {
            width: 15%;
        }

        /* Employee */
        table th:nth-child(2),
        table td:nth-child(2) {
            width: 10%;
        }

        /* Department */
        table th:nth-child(3),
        table td:nth-child(3) {
            width: 10%;
            text-align: center;
        }

        /* Status */
        table th:nth-child(4),
        table td:nth-child(4) {
            width: 10%;
        }

        /* Location */
        table th:nth-child(5),
        table td:nth-child(5) {
            width: 12%;
        }

        /* ON Date */
        table th:nth-child(6),
        table td:nth-child(6) {
            width: 13%;
        }

        /* OFF Date */
        table th:nth-child(7),
        table td:nth-child(7) {
            width: 12%;
            text-align: center;
        }

        /* Board Status */
        table th:nth-child(8),
        table td:nth-child(8) {
            width: 18%;
            text-align: center;
        }

        /* Actions */

        td.actions {
            display: table-cell !important;
        }

        td.actions>* {
            display: inline-block;
            margin: 0 0.25rem;
        }

        .search-select-container {
            position: relative;
        }

        .employee-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 250px;
            overflow-y: auto;
            background: rgba(10, 20, 20, 0.98);
            border: 1px solid var(--card-border);
            border-top: none;
            border-radius: 0 0 10px 10px;
            z-index: 200;
        }

        .employee-dropdown.show {
            display: block;
        }

        .employee-option {
            padding: 0.6rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--card-border);
            transition: background 0.2s;
        }

        .employee-option:hover {
            background: rgba(0, 168, 204, 0.15);
        }

        .employee-option:last-child {
            border-bottom: none;
        }

        .employee-option strong {
            display: block;
            color: var(--text-primary);
        }

        .employee-info {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .employee-info em {
            color: var(--accent);
        }

        .selected-employee {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(0, 168, 204, 0.15);
            border: 1px solid var(--primary);
            border-radius: 10px;
            margin-top: 0.5rem;
            color: var(--text-primary);
        }

        .clear-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0 0.5rem;
        }

        .clear-btn:hover {
            color: var(--error);
        }

        .form-hint {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.8rem;
            color: var(--accent);
        }

        .employee-option.hidden {
            display: none;
        }

        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            padding: 1.5rem;
            width: 100%;
            max-width: 450px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .modal-lg {
            max-width: 600px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--text-primary);
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .close-btn:hover {
            color: var(--error);
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .employee-info-display {
            padding: 0.75rem 1rem;
            background: rgba(0, 168, 204, 0.1);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-primary);
        }

        .employee-info-display strong {
            display: block;
            font-size: 1rem;
        }

        .employee-info-display span {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Month picker styling */
        .month-picker {
            color: white !important;
            color-scheme: dark;
        }

        .month-picker::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(2);
            cursor: pointer;
            opacity: 1;
        }

        input[type="month"]::-webkit-inner-spin-button,
        input[type="month"]::-webkit-calendar-picker-indicator,
        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-calendar-picker-indicator,
        .modal-content input[type="date"]::-webkit-calendar-picker-indicator,
        .modal-content input[type="month"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(2) !important;
            cursor: pointer;
            opacity: 1 !important;
        }

        .modal-content input[type="date"],
        .modal-content input[type="month"] {
            color-scheme: dark;
        }

        /* Mobile Responsive for Planning Page */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-actions {
                flex-direction: column;
                width: 100%;
            }

            .header-actions .btn {
                width: 100%;
            }

            table {
                table-layout: auto;
                min-width: 1000px;
            }

            /* Show all columns - allow horizontal scroll instead of hiding */

            table th,
            table td {
                width: auto !important;
                padding: 0.4rem;
                font-size: 0.7rem;
            }

            td.actions {
                white-space: nowrap;
            }

            td.actions .btn {
                padding: 0.25rem 0.4rem;
                font-size: 0.6rem;
            }

            .modal-content {
                width: 95% !important;
                max-width: none !important;
                margin: 0.5rem;
            }

            .modal-content .row {
                flex-direction: column;
            }

            .modal-content .col-6 {
                max-width: 100%;
            }

            .employee-dropdown {
                max-height: 200px;
            }

            .employee-option {
                padding: 0.5rem;
                font-size: 0.85rem;
            }

            .pagination-container {
                flex-direction: column;
                text-align: center;
            }

            .pagination-info {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {

            /* Keep all columns visible on extra small screens too */
            table {
                min-width: 900px;
            }
        }
    </style>

    @push('scripts')
        <script>
            const employeeSearch = document.getElementById('employeeSearch');
            const employeeDropdown = document.getElementById('employeeDropdown');
            const departmentFilter = document.getElementById('departmentFilter');
            const selectedEmployeeId = document.getElementById('selectedEmployeeId');
            const selectedEmployeeDiv = document.getElementById('selectedEmployee');
            const selectedEmployeeName = document.getElementById('selectedEmployeeName');
            const locationSelect = document.getElementById('locationSelect');
            const locationHint = document.getElementById('locationHint');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const employeeOptions = document.querySelectorAll('.employee-option');

            // Auto-set OFF date (+19 days from ON date)
            function setOffDate(startDate) {
                const date = new Date(startDate);
                date.setDate(date.getDate() + 19);
                return date.toISOString().split('T')[0];
            }

            // Initialize OFF date on page load
            endDateInput.value = setOffDate(startDateInput.value);

            // Update OFF date when ON date changes
            startDateInput.addEventListener('change', function () {
                endDateInput.value = setOffDate(this.value);
            });

            // Show dropdown on focus
            employeeSearch.addEventListener('focus', function () {
                filterAndShowDropdown();
            });

            // Filter as user types
            employeeSearch.addEventListener('input', function () {
                filterAndShowDropdown();
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.search-select-container')) {
                    employeeDropdown.classList.remove('show');
                }
            });

            // Filter employees
            function filterAndShowDropdown() {
                const searchTerm = employeeSearch.value.toLowerCase();
                const selectedDept = departmentFilter.value;
                let hasVisible = false;

                employeeOptions.forEach(option => {
                    const name = option.dataset.name.toLowerCase();
                    const number = option.dataset.number.toLowerCase();
                    const department = option.dataset.department || '';

                    const matchesSearch = name.includes(searchTerm) || number.includes(searchTerm);
                    const matchesDept = !selectedDept || department === selectedDept;

                    if (matchesSearch && matchesDept) {
                        option.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (hasVisible) {
                    employeeDropdown.classList.add('show');
                }
            }

            // Department filter change
            departmentFilter.addEventListener('change', function () {
                filterAndShowDropdown();
            });

            // Select employee
            employeeOptions.forEach(option => {
                option.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const number = this.dataset.number;
                    const status = this.dataset.status;
                    const homebaseLocation = this.dataset.location;

                    selectedEmployeeId.value = id;
                    selectedEmployeeName.textContent = `${name} (${number}) - ${status}`;
                    selectedEmployeeDiv.style.display = 'flex';
                    employeeSearch.style.display = 'none';
                    employeeDropdown.classList.remove('show');

                    // Auto-select location based on homebase
                    if (homebaseLocation) {
                        for (let i = 0; i < locationSelect.options.length; i++) {
                            if (locationSelect.options[i].value === homebaseLocation) {
                                locationSelect.value = homebaseLocation;
                                locationHint.textContent = `📍 Auto-selected: ${homebaseLocation} (homebase)`;
                                locationHint.style.display = 'block';
                                break;
                            }
                        }
                    }
                });
            });

            // Clear selected employee
            function clearEmployee() {
                selectedEmployeeId.value = '';
                selectedEmployeeDiv.style.display = 'none';
                employeeSearch.style.display = 'block';
                employeeSearch.value = '';
                locationHint.style.display = 'none';
                locationSelect.value = '';
            }

            // Modal Functions
            function openAddModal() {
                document.getElementById('addModal').classList.add('show');
            }
            function closeAddModal() {
                document.getElementById('addModal').classList.remove('show');
            }
            function openExportModal() {
                document.getElementById('exportModal').classList.add('show');
            }
            function closeExportModal() {
                document.getElementById('exportModal').classList.remove('show');
            }
            function openEditModal(id, name, number, startDate, endDate, location, notes) {
                document.getElementById('editForm').action = '/planning/' + id;
                document.getElementById('editEmployeeName').textContent = name;
                document.getElementById('editEmployeeNumber').textContent = number;
                document.getElementById('editStartDate').value = startDate;
                document.getElementById('editEndDate').value = endDate;
                document.getElementById('editLocation').value = location;
                document.getElementById('editNotes').value = notes;
                document.getElementById('editModal').classList.add('show');
            }
            function closeEditModal() {
                document.getElementById('editModal').classList.remove('show');
            }

            // Close modals on overlay click
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', function (e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                    }
                });
            });

            // Close modals on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.show').forEach(modal => {
                        modal.classList.remove('show');
                    });
                }
            });

            // Table Sorting for Schedules
            const schedulesTable = document.getElementById('schedulesTable');
            if (schedulesTable) {
                const headers = schedulesTable.querySelectorAll('th.sortable');
                headers.forEach(header => {
                    header.addEventListener('click', function () {
                        const sortKey = this.dataset.sort;
                        const tbody = schedulesTable.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const isAsc = this.classList.contains('asc');

                        // Remove sort classes from all headers
                        headers.forEach(h => h.classList.remove('asc', 'desc'));

                        // Add appropriate class to clicked header
                        this.classList.add(isAsc ? 'desc' : 'asc');

                        // Get column index
                        const colIndex = Array.from(this.parentElement.children).indexOf(this);

                        rows.sort((a, b) => {
                            let aVal = a.children[colIndex]?.textContent.trim() || '';
                            let bVal = b.children[colIndex]?.textContent.trim() || '';

                            // Check if numeric
                            if (!isNaN(aVal) && !isNaN(bVal)) {
                                return isAsc ? bVal - aVal : aVal - bVal;
                            }

                            return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    });
                });
            }
        </script>
    @endpush
@endsection