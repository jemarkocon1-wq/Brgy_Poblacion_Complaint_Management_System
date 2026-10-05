<?php

namespace Database\Seeders;

use App\Models\BarangayInformation;
use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isProduction() && (
            Str::length((string) config('brgy.admin_password')) < 16
            || config('brgy.admin_password') === config('brgy.demo_password')
        )) {
            throw new RuntimeException('Production seeding requires a unique admin password of at least 16 characters.');
        }

        $admin = User::updateOrCreate(
            ['email' => config('brgy.admin_email')],
            [
                'name' => 'Barangay System Administrator',
                'password' => config('brgy.admin_password'),
                'phone' => '09170000001',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        if (app()->isProduction()) {
            BarangayInformation::query()->firstOrCreate([], ['name' => 'Barangay Poblacion']);

            return;
        }

        $staff = collect([
            ['name' => 'Maria Santos', 'email' => 'maria.santos@poblacion.local', 'phone' => '09170000002'],
            ['name' => 'Jose Reyes', 'email' => 'jose.reyes@poblacion.local', 'phone' => '09170000003'],
        ])->map(fn (array $data): User => User::updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'password' => config('brgy.demo_password'),
                'role' => 'staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ));

        $residents = collect([
            ['name' => 'Ana Cruz', 'email' => 'ana.cruz@gmail.com', 'phone' => '09170000004'],
            ['name' => 'Ramon Dela Cruz', 'email' => 'ramon.delacruz@gmail.com', 'phone' => '09170000005'],
            ['name' => 'Liza Mendoza', 'email' => 'liza.mendoza@gmail.com', 'phone' => '09170000006'],
        ])->map(fn (array $data): User => User::updateOrCreate(
            ['email' => $data['email']],
            $data + [
                'password' => config('brgy.demo_password'),
                'role' => 'resident',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ));

        if (Complaint::query()->exists()) {
            return;
        }

        $examples = [
            ['resident' => 0, 'title' => 'Streetlight out along the market road', 'category' => 'infrastructure', 'status' => 'pending'],
            ['resident' => 1, 'title' => 'Uncollected waste beside the public plaza', 'category' => 'sanitation', 'status' => 'approved'],
            ['resident' => 2, 'title' => 'Late night noise near the covered court', 'category' => 'noise', 'status' => 'resolved'],
            ['resident' => 0, 'title' => 'Blocked drainage after heavy rain', 'category' => 'infrastructure', 'status' => 'rejected'],
        ];

        foreach ($examples as $index => $example) {
            $status = $example['status'];
            $complaint = Complaint::create([
                'reference' => 'BRGY-DEMO-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'resident_id' => $residents[$example['resident']]->id,
                'assigned_staff_id' => $status === 'approved' ? $staff[0]->id : null,
                'category' => $example['category'],
                'title' => $example['title'],
                'description' => 'This sample report was added to demonstrate the complaint tracking workflow for Barangay Poblacion.',
                'location' => 'Poblacion proper',
                'status' => $status,
                'rejection_reason' => $status === 'rejected' ? 'Please provide a more specific location so the team can investigate.' : null,
                'resolved_at' => $status === 'resolved' ? now()->subDay() : null,
            ]);

            ComplaintActivity::create([
                'complaint_id' => $complaint->id,
                'actor_id' => $status === 'pending' ? $complaint->resident_id : $admin->id,
                'action' => $status === 'pending' ? 'submitted' : $status,
                'from_status' => $status === 'pending' ? null : 'pending',
                'to_status' => $status,
                'description' => $status === 'pending' ? 'Complaint submitted by resident.' : 'Sample complaint status: '.$status.'.',
            ]);
        }

        BarangayInformation::query()->firstOrCreate([], ['name' => 'Barangay Poblacion']);
    }
}
