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
            [
                'resident' => 0,
                'title' => 'Boundary marker dispute along market road',
                'category' => 'civil',
                'case_subject' => 'Property boundary dispute',
                'respondents' => 'Adjacent property owner',
                'description' => 'The boundary marker between the adjoining properties was moved, and both parties disagree about the property line.',
                'relief_sought' => 'Request mediation and clarification of the property boundary.',
                'status' => 'pending',
            ],
            [
                'resident' => 1,
                'title' => 'Unpaid repair expense dispute',
                'category' => 'civil',
                'case_subject' => 'Unpaid obligation',
                'respondents' => 'A neighbor involved in the repair agreement',
                'description' => 'The agreed share of the repair expense remains unpaid despite several attempts to settle the matter.',
                'relief_sought' => 'Request assistance to agree on a fair payment arrangement.',
                'status' => 'approved',
            ],
            [
                'resident' => 2,
                'title' => 'Missing equipment from covered court',
                'category' => 'criminal_case',
                'case_subject' => 'Alleged theft',
                'respondents' => 'Person identified by the complainant',
                'incident_date' => now()->subDays(4)->toDateString(),
                'description' => 'Several pieces of event equipment were missing after the covered court was used for a community activity.',
                'witnesses' => 'A caretaker may have information about who entered the storage area.',
                'status' => 'resolved',
            ],
            [
                'resident' => 0,
                'title' => 'Damage to a community drainage area',
                'category' => 'criminal_case',
                'case_subject' => 'Alleged property damage',
                'respondents' => 'Person reported by nearby residents',
                'incident_date' => now()->subDays(2)->toDateString(),
                'description' => 'A section of the drainage cover was damaged and left unsafe for people walking along the road.',
                'witnesses' => 'Nearby residents reported seeing the damage occur.',
                'status' => 'rejected',
            ],
        ];

        foreach ($examples as $index => $example) {
            $status = $example['status'];
            $complaint = Complaint::create([
                'reference' => 'BRGY-DEMO-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'resident_id' => $residents[$example['resident']]->id,
                'assigned_staff_id' => $status === 'approved' ? $staff[0]->id : null,
                'category' => $example['category'],
                'title' => $example['title'],
                'respondents' => $example['respondents'],
                'case_subject' => $example['case_subject'],
                'incident_date' => $example['incident_date'] ?? null,
                'description' => $example['description'],
                'relief_sought' => $example['relief_sought'] ?? null,
                'witnesses' => $example['witnesses'] ?? null,
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
