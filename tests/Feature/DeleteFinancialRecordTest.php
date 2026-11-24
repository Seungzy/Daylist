<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User;
use App\Services\MembershipService;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteFinancialRecordTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private MembershipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipSeeder::class);
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->service = app(MembershipService::class);
    }

    private function member(): Member
    {
        $user = User::factory()->create();
        $member = Member::create([
            'membership_number' => MembershipService::reference('MEM'), 'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => $user->email,
            'membership_category_id' => MembershipCategory::first()->id,
            'membership_level_id' => MembershipLevel::where('name', 'Basic')->first()->id,
        ]);
        $member->user()->associate($user)->save();

        return $member;
    }

    private function transaction(Member $member, string $level = 'Basic', string $type = 'application'): MembershipTransaction
    {
        return $this->service->create($member, MembershipLevel::where('name', $level)->firstOrFail(), $type, $this->admin->id);
    }

    private function pay(MembershipTransaction $transaction, string $amount = '500.00'): Payment
    {
        return $this->service->pay($transaction->fresh(), ['amount' => $amount, 'method' => 'cash', 'paid_at' => today()->toDateString()], $this->admin->id);
    }

    public function test_deleting_an_approved_transaction_removes_only_its_payments_and_keeps_the_member_and_tasks(): void
    {
        $member = $this->member();
        $user = $member->user;
        $transaction = $this->transaction($member);
        $payment = $this->pay($transaction, '200.00');
        $secondPayment = $this->pay($transaction, '300.00');
        $this->service->approve($transaction, $this->admin->id);
        $task = Task::create(['task_title' => 'Keep this task', 'member_id' => $member->id]);
        $other = $this->member();
        $otherTransaction = $this->transaction($other);
        $otherPayment = $this->pay($otherTransaction);

        $this->actingAs($this->admin)->get(route('transactions.show', $transaction))->assertOk()->assertSee('Delete Transaction');
        $this->delete(route('transactions.destroy', $transaction), ['confirm_delete' => 1])->assertRedirect(route('transactions.index'))->assertSessionHasNoErrors();
        foreach ([$transaction, $payment, $secondPayment] as $record) {
            $this->assertModelMissing($record);
        }
        foreach ([$member, $user, $task, $other, $otherTransaction, $otherPayment] as $record) {
            $this->assertModelExists($record);
        }
        $this->assertTrue($member->fresh()->is_active);
        $this->assertFalse(Member::withActiveMembership()->whereKey($member->id)->exists());
        $this->get('/dashboard')->assertViewHas('collected', '500');
        $this->get(route('transactions.show', $transaction))->assertNotFound();
        $this->get(route('payments.show', $payment))->assertNotFound();
    }

    public function test_deleting_a_renewal_restores_the_remaining_approved_level_and_period(): void
    {
        $member = $this->member();
        $original = $this->transaction($member);
        $originalPayment = $this->pay($original);
        $this->service->approve($original, $this->admin->id);
        $original = $original->fresh();
        $renewal = $this->transaction($member, 'Gold', 'renewal');
        $renewalPayment = $this->pay($renewal, '2000.00');
        $this->service->approve($renewal, $this->admin->id);
        $this->assertSame('Gold', $member->fresh()->level->name);

        $this->actingAs($this->admin)->delete(route('transactions.destroy', $renewal), ['confirm_delete' => 1])->assertSessionHasNoErrors();
        $this->assertModelMissing($renewal);
        $this->assertModelMissing($renewalPayment);
        $this->assertModelExists($originalPayment);
        $this->assertSame('Basic', $member->fresh()->level->name);
        $this->assertTrue($original->fresh()->valid_until->equalTo($original->valid_until));
        $this->assertTrue(Member::withActiveMembership()->whereKey($member->id)->exists());
    }

    public function test_payment_deletion_reopens_the_balance_without_deleting_other_payments_or_the_transaction(): void
    {
        $member = $this->member();
        $transaction = $this->transaction($member);
        $remaining = $this->pay($transaction, '200.00');
        $payment = $this->pay($transaction, '300.00');
        $this->actingAs($this->admin)->get(route('payments.show', $payment))->assertOk()->assertSee('Delete Payment');
        $this->delete(route('payments.destroy', $payment), ['confirm_delete' => 1, 'membership_transaction_id' => 999999])
            ->assertRedirect(route('transactions.show', $transaction))->assertSessionHasNoErrors();
        $this->assertModelMissing($payment);
        foreach ([$member, $member->user, $transaction, $remaining] as $record) {
            $this->assertModelExists($record);
        }
        $this->assertSame('pending', $transaction->fresh()->status);
        $this->assertSame(20000, $transaction->fresh()->paid_cents);
        $this->assertSame(30000, $transaction->fresh()->balance_cents);
        $this->get('/dashboard')->assertViewHas('collected', '200');
        $replacement = $this->pay($transaction, '300.00');
        $this->assertModelExists($replacement);
        $this->assertSame('paid', $transaction->fresh()->status);
    }

    public function test_deleting_an_approved_payment_clears_approval_and_period_and_preserves_older_membership(): void
    {
        $member = $this->member();
        $original = $this->transaction($member);
        $this->pay($original);
        $this->service->approve($original, $this->admin->id);
        $renewal = $this->transaction($member, 'Gold', 'renewal');
        $payment = $this->pay($renewal, '2000.00');
        $this->service->approve($renewal, $this->admin->id);

        $this->actingAs($this->admin)->delete(route('payments.destroy', $payment), ['confirm_delete' => 1])->assertSessionHasNoErrors();
        $renewal->refresh();
        $this->assertSame('pending', $renewal->status);
        foreach (['approved_by', 'approved_at', 'valid_from', 'valid_until'] as $field) {
            $this->assertNull($renewal->$field);
        }
        $this->assertSame('Basic', $member->fresh()->level->name);
        $this->assertSame('approved', $original->fresh()->status);
        $this->assertTrue(Member::withActiveMembership()->whereKey($member->id)->exists());
    }

    public function test_expired_approval_is_also_removed_when_its_payment_is_deleted(): void
    {
        $member = $this->member();
        $transaction = $this->transaction($member);
        $payment = $this->pay($transaction);
        $this->service->approve($transaction, $this->admin->id);
        $this->travel(13)->months();
        $this->assertSame('expired', $transaction->fresh()->status);
        $this->actingAs($this->admin)->delete(route('payments.destroy', $payment), ['confirm_delete' => 1])->assertSessionHasNoErrors();
        $this->assertSame('pending', $transaction->fresh()->status);
        $this->assertNull($transaction->fresh()->valid_until);
        $this->assertModelExists($member);
    }

    public function test_payment_correction_cannot_create_two_unfinished_transactions(): void
    {
        $member = $this->member();
        $approved = $this->transaction($member);
        $payment = $this->pay($approved);
        $this->service->approve($approved, $this->admin->id);
        $renewal = $this->transaction($member, 'Gold', 'renewal');
        $this->actingAs($this->admin)->from(route('payments.show', $payment))
            ->delete(route('payments.destroy', $payment), ['confirm_delete' => 1])->assertSessionHasErrors('deletion');
        $this->assertModelExists($payment);
        $this->assertSame('approved', $approved->fresh()->status);
        $this->delete(route('transactions.destroy', $renewal), ['confirm_delete' => 1])->assertSessionHasNoErrors();
        $this->delete(route('payments.destroy', $payment), ['confirm_delete' => 1])->assertSessionHasNoErrors();
        $this->assertSame('pending', $approved->fresh()->status);
    }

    public function test_financial_record_deletion_is_admin_only_and_requires_explicit_confirmation(): void
    {
        $member = $this->member();
        $transaction = $this->transaction($member);
        $payment = $this->pay($transaction);
        foreach ([route('transactions.destroy', $transaction), route('payments.destroy', $payment)] as $url) {
            $this->delete($url, ['confirm_delete' => 1])->assertRedirect('/login');
            $this->actingAs($member->user)->delete($url, ['confirm_delete' => 1])->assertForbidden();
            $this->actingAs($this->admin)->delete($url)->assertSessionHasErrors('confirm_delete');
            $this->delete($url, ['confirm_delete' => 0])->assertSessionHasErrors('confirm_delete');
            $this->post('/logout');
        }
        $this->assertModelExists($payment);
        $this->assertModelExists($transaction);
        $this->assertModelExists($member);
    }
}
