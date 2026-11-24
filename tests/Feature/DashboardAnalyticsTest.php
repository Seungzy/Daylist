<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Services\MembershipService;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-24 12:00:00'));
        $this->seed(MembershipSeeder::class);
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    }

    private function transaction(string $level = 'Basic'): MembershipTransaction
    {
        $reference = MembershipService::reference('MEM');
        $member = Member::create([
            'membership_number' => $reference, 'first_name' => 'Chart', 'last_name' => 'Member',
            'email' => strtolower($reference).'@example.test',
            'membership_category_id' => MembershipCategory::first()->id,
            'membership_level_id' => MembershipLevel::where('name', $level)->firstOrFail()->id,
        ]);

        return app(MembershipService::class)->create($member, $member->level, 'application', $this->admin->id);
    }

    private function payment(MembershipTransaction $transaction, string $date, string $amount): void
    {
        $transaction->payments()->create([
            'receipt_number' => MembershipService::reference('RCT'), 'recorded_by' => $this->admin->id,
            'method' => 'cash', 'paid_at' => $date, 'amount' => $amount,
        ]);
    }

    public function test_payment_chart_uses_payment_dates_includes_zero_months_and_changes_period(): void
    {
        $transaction = $this->transaction('Platinum');
        $this->payment($transaction, '2026-05-31', '100.00');
        $this->payment($transaction, '2026-06-01', '125.50');
        $this->payment($transaction, '2026-08-15', '100.00');
        $this->payment($transaction, '2026-11-24', '249.50');
        $response = $this->get('/dashboard?months=6')->assertOk()->assertSee('Payment Overview')->assertSee('View Monthly Figures');
        $this->assertSame(['2026-06', '2026-07', '2026-08', '2026-09', '2026-10', '2026-11'], $response->viewData('paymentSeries')->pluck('month')->all());
        $this->assertSame([12550, 0, 10000, 0, 0, 24950], $response->viewData('paymentSeries')->pluck('cents')->all());
        $this->assertSame(47500, $response->viewData('periodCents'));
        $this->assertSame(3, $response->viewData('periodPayments'));
        $this->assertEquals(575, $response->viewData('collected'));
        foreach ($response->viewData('chartPoints') as $point) {
            $this->assertGreaterThanOrEqual(30, $point['y']);
            $this->assertLessThanOrEqual(232, $point['y']);
        }
        $response = $this->get('/dashboard?months=12')->assertOk();
        $this->assertCount(12, $response->viewData('paymentSeries'));
        $this->assertSame('2025-12', $response->viewData('paymentSeries')->first()['month']);
        $this->assertSame(57500, $response->viewData('periodCents'));
        $this->assertSame(4, $response->viewData('periodPayments'));
    }

    public function test_future_payments_are_excluded_from_the_current_reporting_period(): void
    {
        $transaction = $this->transaction();
        $this->payment($transaction, '2026-11-24', '25.25');
        $this->payment($transaction, '2026-11-25', '50.00');
        $this->get('/dashboard')->assertOk()->assertViewHas('periodCents', 2525)->assertViewHas('periodPayments', 1);
    }

    public function test_level_distribution_is_real_and_links_to_filtered_members_and_activity_is_last(): void
    {
        $this->transaction('Basic');
        $this->transaction('Basic');
        $this->transaction('Gold');
        $response = $this->get('/dashboard')->assertOk();
        $levels = $response->viewData('levelDistribution')->keyBy('name');
        $this->assertCount(2, $levels);
        $this->assertSame(2, $levels['Basic']['count']);
        $this->assertSame(1, $levels['Gold']['count']);
        $this->assertEquals(66.7, $levels['Basic']['percent']);
        $this->assertSame(3, $levels->sum('count'));
        $response->assertSee(route('members.index', ['level' => $levels['Gold']['id']]), false)
            ->assertSeeInOrder(['Membership Levels', 'Member Directory', 'LATEST ACTIVITY', 'Recent Transactions']);
        $this->get(route('members.index', ['level' => $levels['Gold']['id']]))->assertOk()
            ->assertViewHas('members', fn ($members) => $members->total() === 1);
    }

    public function test_empty_dashboard_has_honest_empty_states_and_rejects_invalid_periods(): void
    {
        $this->get('/dashboard')->assertOk()->assertSee('No Payments in This Period')->assertSee('Your Community Starts Here')
            ->assertViewHas('periodCents', 0)->assertViewHas('periodPayments', 0)->assertViewHas('total', 0)
            ->assertDontSee('class="dash-line-chart"', false);
        $this->get('/dashboard?months=invalid')->assertSessionHasErrors('months');
        $this->get('/dashboard?months=48')->assertSessionHasErrors('months');
    }
}
