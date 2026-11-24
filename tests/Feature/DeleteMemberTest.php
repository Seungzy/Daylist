<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
use App\Services\MembershipService;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipSeeder::class);
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    private function member(): Member
    {
        $user = User::factory()->create();
        $member = Member::create([
            'membership_number' => 'MEM-'.$user->id, 'first_name' => 'Ana', 'last_name' => 'Santos',
            'email' => $user->email, 'membership_category_id' => MembershipCategory::first()->id,
            'membership_level_id' => MembershipLevel::where('name', 'Basic')->first()->id,
        ]);
        $member->user()->associate($user)->save();

        return $member;
    }

    private function paidTransaction(Member $member)
    {
        $service = app(MembershipService::class);
        $transaction = $service->create($member, $member->level, 'application', $this->admin->id);
        $payment = $service->pay($transaction, ['amount' => '500.00', 'method' => 'cash', 'paid_at' => today()->format('Y-m-d')], $this->admin->id);

        return [$transaction, $payment];
    }

    public function test_history_requires_explicit_checkbox_and_exact_confirmation(): void
    {
        $member = $this->member();
        [$transaction, $payment] = $this->paidTransaction($member);
        $this->actingAs($this->admin)->get(route('members.show', $member))
            ->assertOk()->assertSee('Permanently Delete Member')->assertSee('name="remove_history"', false);
        $this->from(route('members.show', $member))->delete(route('members.permanent-destroy', $member), [
            'confirm_membership_number' => $member->membership_number,
        ])->assertSessionHasErrors('remove_history');
        $this->delete(route('members.permanent-destroy', $member), [
            'confirm_membership_number' => 'WRONG', 'remove_history' => 1,
        ])->assertSessionHasErrors('confirm_membership_number');
        $this->assertModelExists($member);
        $this->assertModelExists($transaction);
        $this->assertModelExists($payment);
        $this->assertModelExists($member->user);
    }

    public function test_checked_deletion_removes_only_selected_member_history_and_updates_totals(): void
    {
        $member = $this->member();
        $user = $member->user;
        [$transaction, $payment] = $this->paidTransaction($member);
        $other = $this->member();
        [$otherTransaction, $otherPayment] = $this->paidTransaction($other);
        $this->actingAs($this->admin)->get('/dashboard')->assertViewHas('collected', '1000');
        $this->delete(route('members.permanent-destroy', $member), [
            'confirm_membership_number' => $member->membership_number, 'remove_history' => 1,
        ])->assertRedirect(route('members.index'))->assertSessionHasNoErrors();
        foreach ([$member, $user, $transaction, $payment] as $record) {
            $this->assertModelMissing($record);
        }
        foreach ([$other, $other->user, $otherTransaction, $otherPayment, $this->admin] as $record) {
            $this->assertModelExists($record);
        }
        $this->get('/dashboard')->assertViewHas('collected', '500');
        $this->get(route('payments.show', $payment))->assertNotFound();
        $this->get(route('payments.show', $otherPayment))->assertOk();
    }

    public function test_no_history_deletion_cleans_up_login_tasks_and_application_without_checkbox(): void
    {
        $member = $this->member();
        $user = $member->user;
        $assigned = Task::create(['task_title' => 'Only this member', 'member_id' => $member->id]);
        $personal = new Task(['task_title' => 'Private activity', 'member_id' => $member->id]);
        $personal->is_personal = true;
        $personal->created_by = $user->id;
        $personal->save();
        $community = Task::create(['task_title' => 'Everyone']);
        $application = MembershipApplication::create([
            'membership_category_id' => $member->membership_category_id, 'membership_level_id' => $member->membership_level_id,
            'member_id' => $member->id, 'reference_no' => 'APP-TEST', 'full_name' => $member->full_name,
            'email' => $member->email, 'fee' => '500.00', 'status' => 'registered',
        ]);
        $this->actingAs($this->admin)->delete(route('members.permanent-destroy', $member), [
            'confirm_membership_number' => $member->membership_number,
        ])->assertRedirect(route('members.index'))->assertSessionHasNoErrors();
        foreach ([$member, $user, $assigned, $personal, $application] as $record) {
            $this->assertModelMissing($record);
        }
        $this->assertModelExists($community);
        $this->post('/logout')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_guests_and_members_cannot_permanently_delete_members(): void
    {
        $member = $this->member();
        $data = ['confirm_membership_number' => $member->membership_number, 'remove_history' => 1];
        $this->delete(route('members.permanent-destroy', $member), $data)->assertRedirect('/login');
        $this->actingAs($member->user)->delete(route('members.permanent-destroy', $member), $data)->assertForbidden();
        $this->assertModelExists($member);
    }

    public function test_administrator_links_and_other_record_audit_references_prevent_deletion(): void
    {
        $member = $this->member();
        [$transaction, $payment] = $this->paidTransaction($member);
        $other = $this->member();
        [$otherTransaction] = $this->paidTransaction($other);
        $otherTransaction->update(['created_by' => $member->user_id]);
        $this->actingAs($this->admin)->delete(route('members.permanent-destroy', $member), [
            'confirm_membership_number' => $member->membership_number, 'remove_history' => 1,
        ])->assertSessionHasErrors('deletion');
        foreach ([$member, $member->user, $transaction, $payment, $otherTransaction] as $record) {
            $this->assertModelExists($record);
        }
        $member->user->forceFill(['is_admin' => true])->save();
        $this->delete(route('members.permanent-destroy', $member), [
            'confirm_membership_number' => $member->membership_number, 'remove_history' => 1,
        ])->assertSessionHasErrors('deletion');
        $this->assertModelExists($member);
    }
}
