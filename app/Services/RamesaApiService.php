<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RamesaApiService
{
    protected string $baseUrl;
    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ramesa.url'), '/');
        $this->token = config('services.ramesa.token');
    }

    /**
     * Check if API connection is working.
     */
    public function checkConnection(): bool
    {
        try {
            $url = $this->baseUrl . '/api/employees';
            $response = Http::withToken($this->token)
                ->timeout(5)
                ->acceptJson()
                ->get($url, ['limit' => 1]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set the API token dynamically.
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Make authenticated API request.
     */
    protected function request(string $method, string $endpoint, array $data = [])
    {
        $url = $this->baseUrl . '/api' . $endpoint;

        $request = Http::withToken($this->token)
            ->acceptJson();

        $response = match ($method) {
            'GET' => $request->get($url, $data),
            'POST' => $request->post($url, $data),
            default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
        };

        if ($response->failed()) {
            throw new \Exception("API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Login to Ramesa API and get token.
     */
    public function login(string $email, string $password, string $deviceName = 'pob-client'): array
    {
        $url = $this->baseUrl . '/api/login';

        $response = Http::acceptJson()->post($url, [
            'email' => $email,
            'password' => $password,
            'device_name' => $deviceName,
        ]);

        if ($response->failed()) {
            throw new \Exception("Login failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Get all active employees.
     */
    public function getEmployees(array $filters = []): Collection
    {
        $filters['all'] = true; // Get all without pagination
        $filters['status'] = $filters['status'] ?? 'active';

        $response = $this->request('GET', '/employees', $filters);

        return collect($response['data'] ?? []);
    }

    /**
     * Get a single employee by ID.
     */
    public function getEmployee(int $id): ?array
    {
        try {
            $response = $this->request('GET', "/employees/{$id}");
            return $response['data'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get distinct departments.
     */
    public function getDepartments(): Collection
    {
        $response = $this->request('GET', '/employees/departments');
        return collect($response['data'] ?? []);
    }

    /**
     * Get distinct locations.
     */
    public function getLocations(): Collection
    {
        $response = $this->request('GET', '/employees/locations');
        return collect($response['data'] ?? []);
    }

    /**
     * Get attendances with filters.
     */
    public function getAttendances(array $filters = []): Collection
    {
        $filters['all'] = true;
        $response = $this->request('GET', '/attendances', $filters);
        return collect($response['data'] ?? []);
    }

    /**
     * Get distinct employee IDs with attendance in date range.
     */
    public function getAttendanceEmployeeIds(array $filters = []): Collection
    {
        $response = $this->request('GET', '/attendances/employee-ids', $filters);
        return collect($response['data'] ?? []);
    }

    /**
     * Get employees by IDs (for bulk lookup).
     */
    public function getEmployeesByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect([]);
        }

        // Cast all IDs to integers for consistent comparison
        $ids = array_map('intval', array_values($ids));

        // Get all employees and filter by IDs
        $employees = $this->getEmployees();
        return $employees->filter(fn($emp) => in_array((int) $emp['id'], $ids, true));
    }
}
