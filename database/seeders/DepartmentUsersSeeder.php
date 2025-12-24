<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'GS' => 'gs@pob.com',
            'ICT' => 'ict@pob.com',
            'SCM' => 'scm@pob.com',
            'HSSE' => 'hsse@pob.com',
            'PO' => 'po@pob.com',
            'RAM' => 'ram@pob.com',
            'WS' => 'ws@pob.com',
            'FM' => 'fm@pob.com',
            'RELATION' => 'relation@pob.com',
            'PE' => 'pe@pob.com',
            'Plan & Eval' => 'planeval@pob.com',
            'LMF' => 'lmf@pob.com',
        ];

        // Create Super Admin if not exists
        User::firstOrCreate(
            ['email' => 'admin@pob.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@pob.com',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'department' => 'GS',
                'is_approved' => true,
            ]
        );

        $this->command->info('✓ Super Admin created/verified: admin@pob.com');

        // Create department users
        foreach ($departments as $department => $email) {
            $isGS = $department === 'GS';

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $department . ' User',
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role' => $isGS ? 'gs' : 'department_user',
                    'department' => $department,
                    'is_approved' => true, // Auto-approve seeded users
                ]
            );

            $this->command->info("✓ {$department} user created: {$email}");
        }

        $this->command->newLine();
        $this->command->info('All department users created with password: password123');
    }
}
