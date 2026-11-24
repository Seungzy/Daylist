<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Optional fictional members for demonstrating directory pagination. */
class PaginationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->firstOrFail();
        $service = app(MembershipService::class);

        DB::transaction(function () use ($admin, $service) {
            foreach ([
                ['Ava', 'Santos', 'Student', 'Basic'],
                ['Ben', 'Reyes', 'Regular', 'Silver'],
                ['Chloe', 'Ramos', 'Student', 'Gold'],
                ['Ethan', 'Cruz', 'Corporate', 'Platinum'],
                ['Isla', 'Garcia', 'Senior Citizen', 'Silver'],
                ['Liam', 'Mendoza', 'Regular', 'Basic'],
                ['Nora', 'Flores', 'Senior Citizen', 'Gold'],
                ['Theo', 'Navarro', 'Corporate', 'Platinum'],
            ] as [$first, $last, $category, $level]) {
                $email = strtolower($first.'.'.$last).'@gmail.com';
                if (Member::whereIn('email', [$email, 'pagination.'.strtolower($first).'.demo@example.test'])->exists()) {
                    continue;
                }

                $member = Member::create([
                    'membership_number' => MembershipService::reference('MEM'),
                    'first_name' => $first, 'last_name' => $last,
                    'email' => $email, 'address' => 'Fictional sample member for the classroom pagination demonstration.',
                    'membership_category_id' => MembershipCategory::where('name', $category)->firstOrFail()->id,
                    'membership_level_id' => MembershipLevel::where('name', $level)->firstOrFail()->id,
                ]);
                $service->create($member, $member->level, 'application', $admin->id);
            }
        });
    }
}
