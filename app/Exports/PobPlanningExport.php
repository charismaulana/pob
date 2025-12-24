<?php

namespace App\Exports;

use App\Models\PobPlanning;
use App\Services\RamesaApiService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PobPlanningExport implements FromArray, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;
    protected $dateFrom;
    protected $dateTo;
    protected $dates = [];
    protected $rowCount = 0;
    protected $cellStyles = [];
    protected $dailyTotals = [];
    protected $monthLabel;
    protected RamesaApiService $api;

    public function __construct(array $filters = [], ?RamesaApiService $api = null)
    {
        $this->filters = $filters;
        $this->api = $api ?? app(RamesaApiService::class);

        $month = $filters['month'] ?? date('Y-m');
        $this->dateFrom = Carbon::parse($month . '-01')->startOfMonth();
        $this->dateTo = Carbon::parse($month . '-01')->endOfMonth();
        $this->monthLabel = $this->dateFrom->format('F Y');

        $period = CarbonPeriod::create($this->dateFrom, $this->dateTo);
        foreach ($period as $date) {
            $this->dates[] = $date;
            $this->dailyTotals[$date->format('Y-m-d')] = 0;
        }
    }

    public function title(): string
    {
        return 'Schedule ' . $this->dateFrom->format('M Y');
    }

    public function array(): array
    {
        $data = [];

        // Title row
        $titleRow = ['POB Schedule - ' . $this->monthLabel];
        for ($i = 1; $i < 6 + count($this->dates); $i++) {
            $titleRow[] = '';
        }
        $data[] = $titleRow;

        // Header row
        $headerRow = [
            'No',
            'Employee ID',
            'Name',
            'Department',
            'Location',
            'Status',
        ];
        foreach ($this->dates as $date) {
            $headerRow[] = $date->format('d');
        }
        $data[] = $headerRow;

        // Get schedules
        $query = PobPlanning::active()
            ->overlapsDateRange($this->dateFrom->toDateString(), $this->dateTo->toDateString())
            ->orderBy('start_date');

        if (!empty($this->filters['department'])) {
            // Get employee IDs for this department from API
            $employees = $this->api->getEmployees(['department' => $this->filters['department']]);
            $employeeIds = $employees->pluck('id')->toArray();
            $query->whereIn('employee_id', $employeeIds);
        }

        if (!empty($this->filters['location'])) {
            $query->where('location', $this->filters['location']);
        }

        $schedules = $query->get();

        // Get all employees from API for lookup
        $allEmployees = $this->api->getEmployees();
        $employeesById = $allEmployees->keyBy('id');

        // Group schedules by employee
        $employeeSchedules = [];
        foreach ($schedules as $schedule) {
            $empId = $schedule->employee_id;
            $employee = $employeesById->get($empId);

            if (!isset($employeeSchedules[$empId])) {
                $employeeSchedules[$empId] = [
                    'employee' => $employee,
                    'schedules' => [],
                    'locations' => [],
                ];
            }
            $employeeSchedules[$empId]['schedules'][] = $schedule;
            if (!in_array($schedule->location, $employeeSchedules[$empId]['locations'])) {
                $employeeSchedules[$empId]['locations'][] = $schedule->location;
            }
        }

        $rowNum = 1;
        $dataRowNum = 3;

        foreach ($employeeSchedules as $empData) {
            $employee = $empData['employee'];
            $scheduleList = $empData['schedules'];
            $locations = implode(', ', $empData['locations']);

            $row = [
                $rowNum++,
                $employee['employee_number'] ?? '',
                $employee['name'] ?? '',
                $employee['department'] ?? '',
                $locations,
                $employee['employee_status'] ?? '',
            ];

            $colIndex = 6;

            foreach ($this->dates as $date) {
                $dateStr = $date->format('Y-m-d');
                $colLetter = $this->getColumnLetter($colIndex);
                $cell = $colLetter . $dataRowNum;

                $dayNumber = null;
                $isOnsite = false;

                foreach ($scheduleList as $schedule) {
                    $scheduleStartDate = $schedule->start_date;
                    $scheduleEndDate = $schedule->end_date ?? Carbon::parse('9999-12-31');

                    if ($date >= $scheduleStartDate && $date <= $scheduleEndDate) {
                        $thisDayNumber = $scheduleStartDate->diffInDays($date) + 1;

                        if ($dayNumber === null || $thisDayNumber > $dayNumber) {
                            $dayNumber = $thisDayNumber;
                        }
                        $isOnsite = true;
                    }
                }

                if ($isOnsite) {
                    $row[] = $dayNumber;
                    $this->cellStyles[$cell] = 'onsite';
                    $this->dailyTotals[$dateStr]++;
                } else {
                    $row[] = 'R';
                    $this->cellStyles[$cell] = 'offsite';
                }

                $colIndex++;
            }

            $data[] = $row;
            $dataRowNum++;
        }

        // Grand Total row
        $totalRow = ['', '', 'GRAND TOTAL', '', '', ''];
        foreach ($this->dates as $date) {
            $dateStr = $date->format('Y-m-d');
            $totalRow[] = $this->dailyTotals[$dateStr];
        }
        $data[] = $totalRow;
        $this->rowCount = count($data);

        return $data;
    }

    private function getColumnLetter($index)
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intval($index / 26) - 1;
        }
        return $letters;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,
            'B' => 12,
            'C' => 25,
            'D' => 15,
            'E' => 18,
            'F' => 12,
        ];

        $colIndex = 6;
        foreach ($this->dates as $date) {
            $col = $this->getColumnLetter($colIndex);
            $widths[$col] = 4;
            $colIndex++;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FF000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            2 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF00A8CC'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColIndex = 5 + count($this->dates);
                $lastCol = $this->getColumnLetter($lastColIndex);
                $lastRow = $this->rowCount;
                $totalRow = $lastRow;

                $sheet->mergeCells("A1:{$lastCol}1");

                foreach ($this->cellStyles as $cell => $status) {
                    if ($status === 'onsite') {
                        $sheet->getStyle($cell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF00CC66'],
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);
                    } else {
                        $sheet->getStyle($cell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFFF4444'],
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);
                    }
                }

                $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF333333'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF888888'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $dataLastRow = $lastRow - 1;
                $sheet->getStyle("C3:C{$dataLastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            },
        ];
    }
}
