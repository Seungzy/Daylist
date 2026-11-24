<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Task;
use App\Models\User;
use App\Services\MembershipService;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class MembershipCardAndListsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipSeeder::class);
    }

    private function member(string $name = 'Ana'): Member
    {
        $user = User::factory()->create();
        $member = Member::create([
            'membership_number' => 'MEM-'.$user->id, 'first_name' => $name, 'last_name' => 'List', 'email' => $user->email,
            'membership_category_id' => MembershipCategory::where('name', 'Student')->first()->id,
            'membership_level_id' => MembershipLevel::where('name', 'Basic')->first()->id,
        ]);
        $member->user()->associate($user)->save();

        return $member;
    }

    private function approved(Member $member, User $admin, string $level, $from, $until): MembershipTransaction
    {
        return $member->transactions()->create([
            'reference_number' => MembershipService::reference('TXN'), 'membership_level_id' => MembershipLevel::where('name', $level)->first()->id,
            'type' => 'renewal', 'amount' => 500, 'validity_months' => 12, 'status' => 'approved',
            'valid_from' => $from, 'valid_until' => $until, 'created_by' => $admin->id, 'approved_by' => $admin->id, 'approved_at' => now(),
        ]);
    }

    public function test_card_is_private_and_does_not_claim_an_unapproved_membership_is_active(): void
    {
        $member = $this->member();
        $other = $this->member('Private Other');
        $this->get('/portal/profile')->assertRedirect('/login');
        $this->actingAs($member->user)->get('/portal/profile')->assertOk()
            ->assertSee('Your Membership Card')->assertSee('Pending Activation')->assertSee('Not active yet')
            ->assertSee($member->membership_number)->assertDontSee($other->membership_number)
            ->assertViewHas('cardMembership', null);
        $this->get('/portal/profile/999')->assertNotFound();
    }

    public function test_card_uses_current_approved_period_instead_of_future_renewal_or_paginated_history(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = $this->member();
        $current = $this->approved($member, $admin, 'Gold', today()->subMonth(), today()->addMonth());
        $this->approved($member, $admin, 'Platinum', today()->addMonth()->addDay(), today()->addYear());
        for ($i = 0; $i < 9; $i++) {
            $this->approved($member, $admin, 'Silver', today()->subYears(2), today()->subYear())->update(['status' => 'cancelled']);
        }
        $this->actingAs($member->user)->get('/portal/profile')->assertOk()
            ->assertViewHas('cardStatus', 'Active')->assertViewHas('cardMembership', fn ($card) => $card->id === $current->id)
            ->assertSee('Gold')->assertSee($current->valid_until->format('M d, Y'));
        $this->get('/portal/profile?page=2')->assertViewHas('cardMembership', fn ($card) => $card->id === $current->id);
    }

    public function test_card_shows_expired_and_upcoming_memberships_truthfully(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = $this->member();
        $this->approved($member, $admin, 'Silver', today()->subYear(), today()->subDay());
        $this->actingAs($member->user)->get('/portal/profile')->assertViewHas('cardStatus', 'Expired')->assertSee('This membership period has ended.');
        $upcoming = $this->approved($member, $admin, 'Gold', today()->addDay(), today()->addYear());
        $this->get('/portal/profile')->assertViewHas('cardStatus', 'Upcoming')
            ->assertViewHas('cardMembership', fn ($card) => $card->id === $upcoming->id);
    }

    public function test_member_sorting_paginates_and_preserves_all_filters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $members = collect(range(0, 16))->map(fn ($i) => $this->member(sprintf('Person %02d', 16 - $i)));
        $outside = $this->member('Outside');
        $outside->update(['membership_category_id' => MembershipCategory::where('name', 'Regular')->first()->id]);
        $filters = ['sort' => 'name_asc', 'q' => 'List', 'category' => $members[0]->membership_category_id, 'level' => $members[0]->membership_level_id];
        $response = $this->actingAs($admin)->get(route('members.index', $filters))->assertOk();
        $page = $response->viewData('members');
        $this->assertSame(17, $page->total());
        $this->assertSame('Person 00', $page->first()->first_name);
        $this->assertCount(12, $page);
        parse_str(parse_url($page->nextPageUrl(), PHP_URL_QUERY), $next);
        foreach ($filters as $key => $value) {
            $this->assertEquals($value, $next[$key]);
        }
        $this->get($page->nextPageUrl())->assertOk()->assertViewHas('members', fn ($records) => $records->count() === 5 && $records->first()->first_name === 'Person 12');
        $this->get(route('members.index', [...$filters, 'sort' => 'name_desc']))->assertViewHas('members', fn ($records) => $records->first()->first_name === 'Person 16');
    }

    public function test_all_admin_list_pages_have_stable_alphabetical_sorting_and_pagination(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $service = app(MembershipService::class);
        for ($i = 16; $i >= 0; $i--) {
            $name = sprintf('Person %02d', $i);
            $member = $this->member($name);
            $transaction = $service->create($member, $member->level, 'application', $admin->id);
            $service->pay($transaction, ['amount' => 500, 'method' => 'cash', 'paid_at' => today()->format('Y-m-d')], $admin->id);
            Task::create(['task_title' => $name]);
            MembershipCategory::create(['name' => $name]);
            MembershipLevel::create(['name' => $name, 'fee' => 100, 'validity_months' => 12]);
            MembershipApplication::create(['reference_no' => 'APP-'.$i, 'full_name' => $name, 'email' => $member->email, 'membership_category_id' => $member->membership_category_id, 'membership_level_id' => $member->membership_level_id, 'fee' => 500]);
        }
        $this->actingAs($admin);
        foreach (['tasks' => 'tasks', 'applications' => 'applications', 'categories' => 'items', 'levels' => 'items', 'transactions' => 'transactions', 'payments' => 'payments'] as $path => $variable) {
            $response = $this->get('/'.$path.'?sort=name_asc')->assertOk()->assertSee('Sort By')->assertSee('aria-current="page"', false);
            $records = $response->viewData($variable);
            $this->assertInstanceOf(LengthAwarePaginator::class, $records);
            $this->assertTrue($records->hasMorePages());
            $first = $records->first();
            $actual = match ($path) {
                'transactions' => $first->member->first_name,
                'payments' => $first->transaction->member->first_name,
                'applications' => $first->full_name,
                'tasks' => $first->task_title,
                'categories' => $first->name,
                'levels' => $first->name,
            };
            $this->assertSame(match ($path) {
                'categories' => 'Corporate', 'levels' => 'Basic', default => 'Person 00'
            }, $actual);
            $this->get($records->nextPageUrl())->assertOk()->assertViewHas($variable, fn ($page) => $page->currentPage() === 2);
            $this->get('/'.$path.'?sort=name_desc')->assertOk()->assertViewHas($variable, fn ($page) => $page->first()->id !== $first->id);
            $this->get('/'.$path.'?sort=invalid')->assertSessionHasErrors('sort');
        }
    }

    public function test_public_login_copy_is_neutral_and_both_roles_still_sign_in(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in with your account')
            ->assertDontSee('administrator')->assertDontSee('admin account');
        $this->get('/')->assertDontSee('Member &amp; admin login', false)->assertSee('Sign In to Your Account');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->get('/login')->assertRedirect(route('dashboard'));
        $this->post('/logout');
        $member = $this->member();
        $this->post('/login', ['email' => $member->email, 'password' => 'password'])->assertRedirect(route('portal.profile'));
    }
}
