<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalTaskTest extends TestCase
{
    use RefreshDatabase;

    private function member(string $email = 'member@example.test'): Member
    {
        $this->seed(MembershipSeeder::class);
        $user = User::factory()->create(['email' => $email]);
        $member = Member::create(['membership_number' => 'MEM-'.$user->id, 'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => $email, 'membership_category_id' => MembershipCategory::first()->id, 'membership_level_id' => MembershipLevel::first()->id]);
        $member->user()->associate($user);
        $member->save();

        return $member;
    }

    public function test_member_can_manage_personal_tasks_without_changing_ownership(): void
    {
        $member = $this->member();
        $other = $this->member('other@example.test');
        $this->actingAs($member->user)->post(route('portal.tasks.store'), [
            'task_title' => 'My groceries', 'description' => 'Milk and bread', 'due_date' => '2026-12-01',
            'member_id' => $other->id, 'created_by' => $other->user_id, 'is_personal' => false, 'completed' => true,
        ])->assertRedirect(route('portal.tasks'))->assertSessionHasNoErrors();
        $task = Task::where('task_title', 'My groceries')->firstOrFail();
        $this->assertTrue($task->is_personal);
        $this->assertSame($member->id, $task->member_id);
        $this->assertSame($member->user_id, (int) $task->created_by);
        $this->assertFalse($task->completed);
        $this->get(route('portal.tasks.edit', $task))->assertOk()->assertSee('My groceries');
        $this->put(route('portal.tasks.update', $task), [
            'task_title' => 'Weekly groceries', 'member_id' => $other->id, 'created_by' => $other->user_id, 'is_personal' => false,
        ])->assertSessionHasNoErrors()->assertRedirect(route('portal.tasks'));
        $task->refresh();
        $this->assertTrue($task->is_personal);
        $this->assertSame($member->id, $task->member_id);
        $this->assertSame($member->user_id, (int) $task->created_by);
        foreach ([1, 1, 0] as $completed) {
            $this->patch(route('portal.tasks.completion', $task), ['completed' => $completed])->assertSessionHasNoErrors();
            $this->assertSame((bool) $completed, $task->fresh()->completed);
        }
        $this->get('/portal/tasks')->assertOk()->assertSee('Weekly groceries')->assertSee('Personal');
        $this->delete(route('portal.tasks.destroy', $task))->assertRedirect(route('portal.tasks'));
        $this->assertModelMissing($task);
    }

    public function test_personal_tasks_are_private_from_other_members_and_admin_task_management(): void
    {
        $member = $this->member();
        $other = $this->member('other@example.test');
        $task = new Task(['task_title' => 'PRIVATE personal activity', 'member_id' => $member->id]);
        $task->created_by = $member->user_id;
        $task->is_personal = true;
        $task->save();
        $this->actingAs($other->user)->get('/portal/tasks')->assertOk()->assertDontSee($task->task_title);
        $this->get(route('portal.tasks.edit', $task))->assertForbidden();
        $this->put(route('portal.tasks.update', $task), ['task_title' => 'Tampered'])->assertForbidden();
        $this->patch(route('portal.tasks.completion', $task), ['completed' => 1])->assertForbidden();
        $this->delete(route('portal.tasks.destroy', $task))->assertForbidden();
        $this->get('/portal/profile')->assertViewHas('taskCount', 0);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/tasks')->assertOk()->assertDontSee($task->task_title);
        $this->get(route('tasks.edit', $task))->assertForbidden();
        $this->put(route('tasks.update', $task), ['task_title' => 'Tampered'])->assertForbidden();
        $this->patch(route('tasks.completion', $task), ['completed' => 1])->assertForbidden();
        $this->delete(route('tasks.destroy', $task))->assertForbidden();
        $this->assertSame('PRIVATE personal activity', $task->fresh()->task_title);
    }

    public function test_member_personal_task_endpoints_cannot_modify_administrator_tasks(): void
    {
        $member = $this->member();
        $this->actingAs($member->user);
        foreach ([null, $member->id] as $assigned) {
            $task = Task::create(['task_title' => 'Administrator activity', 'member_id' => $assigned]);
            $this->get(route('portal.tasks.edit', $task))->assertForbidden();
            $this->put(route('portal.tasks.update', $task), ['task_title' => 'Tampered'])->assertForbidden();
            $this->patch(route('portal.tasks.completion', $task), ['completed' => 1])->assertForbidden();
            $this->delete(route('portal.tasks.destroy', $task))->assertForbidden();
            $this->assertModelExists($task);
        }
        $this->get('/portal/profile')->assertViewHas('taskCount', 2);
    }

    public function test_personal_task_validation_and_disabled_account_protection(): void
    {
        $member = $this->member();
        $this->post('/portal/tasks', ['task_title' => 'Guest task'])->assertRedirect('/login');
        $this->actingAs($member->user)->from('/portal/tasks')->post('/portal/tasks', [
            'task_title' => '', 'description' => 'Keep this text', 'due_date' => 'invalid',
        ])->assertSessionHasErrors(['task_title', 'due_date'])->assertSessionHasInput('description', 'Keep this text');
        $this->post('/portal/tasks', ['task_title' => str_repeat('a', 256)])->assertSessionHasErrors('task_title');
        $this->assertDatabaseCount('tasks', 0);
        $this->post('/portal/tasks', ['task_title' => 'Valid task'])->assertSessionHasNoErrors();
        $task = Task::firstOrFail();
        $this->patch(route('portal.tasks.completion', $task), ['completed' => 'invalid'])->assertSessionHasErrors('completed');
        $this->get('/portal/tasks/99999/edit')->assertNotFound();
        $member->update(['is_active' => false]);
        $this->post('/portal/tasks',['task_title' => 'Disabled task'])->assertForbidden();
        $this->delete(route('portal.tasks.destroy',$task))->assertForbidden();
    }
}
