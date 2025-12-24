<?php

namespace App\Http\Controllers;

use App\Models\PobPlanning;
use App\Services\RamesaApiService;
use App\Exports\PobPlanningExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PobController extends Controller
{
    protected array $locations = ['Ramba', 'Bentayan', 'Mangunjaya', 'Keluang'];
    protected RamesaApiService $api;

    public function __construct(RamesaApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Show the POB planning page.
     */
    public function planning(Request $request)
    {
        $user = auth()->user();
        $location = $request->get('location', '');
        $search = $request->get('search', '');
        $departmentFilter = $request->get('department', '');

        // Get schedules
        $query = PobPlanning::active()->orderBy('start_date', 'desc');

        // Department-based access filter
        if (!$user->canAccessAllDepartments()) {
            // Get employee IDs from user's department via API
            $departmentEmployees = $this->api->getEmployees(['department' => $user->department]);
            $employeeIds = $departmentEmployees->pluck('id')->toArray();
            $query->whereIn('employee_id', $employeeIds);
        } elseif ($departmentFilter) {
            $departmentEmployees = $this->api->getEmployees(['department' => $departmentFilter]);
            $employeeIds = $departmentEmployees->pluck('id')->toArray();
            $query->whereIn('employee_id', $employeeIds);
        }

        if ($location) {
            $query->forLocation($location);
        }

        if ($search) {
            // Search employees via API
            $searchEmployees = $this->api->getEmployees(['search' => $search]);
            $searchIds = $searchEmployees->pluck('id')->toArray();
            $query->whereIn('employee_id', $searchIds);
        }

        $schedules = $query->paginate(20);

        // Enrich schedules with employee data from API
        $employeeIds = $schedules->pluck('employee_id')->unique()->toArray();

        // Get employees - filter by department for non-admin/non-GS users
        if ($user->canAccessAllDepartments()) {
            $employees = $this->api->getEmployees();
        } else {
            // Only get employees from user's department
            $employees = $this->api->getEmployees(['department' => $user->department]);
        }

        $employeesById = $employees->keyBy('id');

        foreach ($schedules as $schedule) {
            $schedule->employee = (object) ($employeesById->get($schedule->employee_id) ?? [
                'id' => $schedule->employee_id,
                'name' => 'Unknown',
                'employee_number' => 'N/A',
                'department' => '',
            ]);
        }

        // Get departments for filter dropdown
        if ($user->canAccessAllDepartments()) {
            $departments = $this->api->getDepartments();
        } else {
            // Only show user's own department
            $departments = collect([$user->department]);
        }

        // Count on board by location
        $today = Carbon::today()->toDateString();
        $onBoardByLocation = [];
        foreach ($this->locations as $loc) {
            $onBoardQuery = PobPlanning::active()->forLocation($loc)->activeOnDate($today);

            if (!$user->canAccessAllDepartments()) {
                $onBoardQuery->whereIn('employee_id', $employeeIds);
            }

            $onBoardByLocation[$loc] = $onBoardQuery->count();
        }

        return view('pob.planning', compact(
            'schedules',
            'employees',
            'departments',
            'location',
            'search',
            'onBoardByLocation'
        ))->with('locations', $this->locations);
    }

    /**
     * Store a new employee schedule.
     */
    public function storePlanning(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'required|string|in:' . implode(',', $this->locations),
            'status' => 'nullable|in:planned,confirmed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for overlapping schedules
        $overlap = PobPlanning::where('employee_id', $validated['employee_id'])
            ->forLocation($validated['location'])
            ->active()
            ->where(function ($query) use ($validated) {
                $startDate = $validated['start_date'];
                $endDate = $validated['end_date'] ?? '9999-12-31';

                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                        ->where(function ($q2) use ($startDate) {
                            $q2->whereNull('end_date')
                                ->orWhere('end_date', '>=', $startDate);
                        });
                });
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['overlap' => 'This employee already has an overlapping schedule for this location.'])->withInput();
        }

        PobPlanning::create([
            'employee_id' => $validated['employee_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'status' => $validated['status'] ?? 'planned',
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        // Get employee name from API
        $employee = $this->api->getEmployee($validated['employee_id']);
        $employeeName = $employee['name'] ?? 'Employee';

        return back()->with('success', "Schedule added for {$employeeName}.");
    }

    /**
     * Update an employee schedule.
     */
    public function updatePlanning(Request $request, PobPlanning $planning)
    {
        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'location' => 'sometimes|string|in:' . implode(',', $this->locations),
            'status' => 'sometimes|in:planned,confirmed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $planning->update($validated);

        return back()->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove an employee schedule.
     */
    public function destroyPlanning(PobPlanning $planning)
    {
        $employee = $this->api->getEmployee($planning->employee_id);
        $employeeName = $employee['name'] ?? 'Unknown';
        $planning->delete();

        return back()->with('success', "Schedule for {$employeeName} removed.");
    }

    /**
     * Show the planning vs actual comparison page.
     */
    public function comparison(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::today()->toDateString());
        $dateTo = $request->get('date_to', Carbon::today()->toDateString());
        $location = $request->get('location', '');

        $comparisonData = [];
        $totals = [
            'planned' => 0,
            'actual' => 0,
            'present' => 0,
            'absent' => 0,
            'unexpected' => 0,
        ];

        foreach ($this->locations as $loc) {
            if ($location && $location !== $loc) {
                continue;
            }

            // Get planned employee IDs from local DB
            $plannedEmployeeIds = PobPlanning::forLocation($loc)
                ->active()
                ->overlapsDateRange($dateFrom, $dateTo)
                ->distinct()
                ->pluck('employee_id')
                ->toArray();

            // Get actual employee IDs from API
            $actualEmployeeIds = $this->api->getAttendanceEmployeeIds([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'location' => $loc,
            ])->toArray();

            $plannedCount = count($plannedEmployeeIds);
            $actualCount = count($actualEmployeeIds);

            $presentIds = array_intersect($plannedEmployeeIds, $actualEmployeeIds);
            $presentCount = count($presentIds);

            $absentIds = array_diff($plannedEmployeeIds, $actualEmployeeIds);
            $absentCount = count($absentIds);

            $unexpectedIds = array_diff($actualEmployeeIds, $plannedEmployeeIds);
            $unexpectedCount = count($unexpectedIds);

            $comparisonData[$loc] = [
                'planned' => $plannedCount,
                'actual' => $actualCount,
                'present' => $presentCount,
                'absent' => $absentCount,
                'unexpected' => $unexpectedCount,
                'presentIds' => $presentIds,
                'absentIds' => $absentIds,
                'unexpectedIds' => $unexpectedIds,
            ];

            $totals['planned'] += $plannedCount;
            $totals['actual'] += $actualCount;
            $totals['present'] += $presentCount;
            $totals['absent'] += $absentCount;
            $totals['unexpected'] += $unexpectedCount;
        }

        $totals['variance'] = $totals['planned'] > 0
            ? round(($totals['actual'] / $totals['planned']) * 100, 1)
            : 0;

        // Get employee details for drilldown
        $allAbsentIds = [];
        $allUnexpectedIds = [];
        foreach ($comparisonData as $data) {
            $allAbsentIds = array_merge($allAbsentIds, $data['absentIds']);
            $allUnexpectedIds = array_merge($allUnexpectedIds, $data['unexpectedIds']);
        }

        $absentEmployees = $this->api->getEmployeesByIds(array_unique($allAbsentIds));
        $unexpectedEmployees = $this->api->getEmployeesByIds(array_unique($allUnexpectedIds));

        $departments = $this->api->getDepartments();

        return view('pob.comparison', compact(
            'comparisonData',
            'totals',
            'dateFrom',
            'dateTo',
            'location',
            'absentEmployees',
            'unexpectedEmployees',
            'departments'
        ))->with('locations', $this->locations);
    }

    /**
     * Export POB planning data to Excel.
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $department = $request->get('department');
        if (!$user->canAccessAllDepartments()) {
            $department = $user->department;
        }

        $filters = [
            'department' => $department,
            'location' => $request->get('location'),
            'month' => $request->get('month', date('Y-m')),
        ];

        $monthLabel = Carbon::parse($filters['month'] . '-01')->format('M_Y');
        $filename = 'POB_Schedule_' . $monthLabel . '.xlsx';

        return Excel::download(new PobPlanningExport($filters, $this->api), $filename);
    }
}
