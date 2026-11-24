<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MembershipSeeder::class);
    }

    private function member(string $email = 'member@example.test'): Member
    {
        $user = User::factory()->create(['email' => $email]);
        $member = Member::create(['membership_number' => 'MEM-'.str_replace(['@', '.'], '', $email), 'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => $email, 'membership_category_id' => MembershipCategory::first()->id, 'membership_level_id' => MembershipLevel::first()->id]);
        $member->user()->associate($user);
        $member->save();

        return $member;
    }

    private function applicationData(array $extra = []): array
    {
        return $extra + ['membership_category_id' => MembershipCategory::first()->id, 'membership_level_id' => MembershipLevel::first()->id, 'full_name' => 'Ana Cruz', 'email' => 'applicant@example.test', 'privacy_consent' => '1'];
    }

    public function test_index_is_public_and_links_to_application_and_login(): void
    {
        $this->get('/')->assertOk()->assertSee('Your Community.')->assertSee(route('membership.create'))->assertSee(route('login'));
        $this->get('/membership/apply')->assertOk()->assertSee('Location & Live Weather', false);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/')->assertOk()->assertSee('Your Community.');
    }

    public function test_member_login_redirects_to_own_profile_and_rejects_admin_access(): void
    {
        $member = $this->member();
        $this->post('/login', ['email' => $member->email, 'password' => 'password'])->assertRedirect(route('portal.profile'));
        $this->get('/portal/profile')->assertOk()->assertSee('My Profile')->assertSee($member->membership_number);
        $this->get('/login')->assertRedirect(route('portal.profile'));
        foreach (['/dashboard', '/members', '/tasks', '/applications', '/status', '/payments'] as $path) {
            $this->get($path)->assertForbidden();
        }
        $this->post('/tasks', ['task_title' => 'Not allowed'])->assertForbidden();
        $this->get('/portal/profile/999')->assertNotFound();
    }

    public function test_member_only_sees_community_and_own_tasks_and_cannot_modify_them(): void
    {
        $member = $this->member();
        $other = $this->member('other@example.test');
        $public = Task::create(['task_title' => 'Community meeting']);
        $own = Task::create(['task_title' => 'Your activity', 'member_id' => $member->id]);
        $private = Task::create(['task_title' => 'Another members private activity', 'member_id' => $other->id]);
        $this->actingAs($member->user)->get('/portal/tasks')->assertOk()->assertSee('Community meeting')->assertSee('Your activity')->assertDontSee($private->task_title);
        $this->patch(route('tasks.completion', $own), ['completed' => 1])->assertForbidden();
        $this->put(route('tasks.update', $public), ['task_title' => 'Tampered'])->assertForbidden();
        $this->delete(route('tasks.destroy', $private))->assertForbidden();
    }

    public function test_disabled_member_and_orphan_user_cannot_login(): void
    {
        $member = $this->member();
        $member->update(['is_active' => false]);
        $this->post('/login', ['email' => $member->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($member->user)->get('/portal/profile')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/portal/tasks')->assertForbidden();
    }

    public function test_admin_registers_a_member_with_a_working_linked_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/members', ['first_name' => 'New', 'last_name' => 'Member', 'email' => 'new@example.test', 'membership_category_id' => MembershipCategory::first()->id, 'membership_level_id' => MembershipLevel::first()->id, 'password' => 'member123', 'password_confirmation' => 'member123'])->assertSessionHasNoErrors()->assertSessionHas('access_credentials.email', 'new@example.test');
        $member = Member::where('email', 'new@example.test')->firstOrFail();
        $this->assertNotNull($member->user_id);
        $this->assertFalse($member->user->is_admin);
        $this->post('/logout');
        $this->post('/login', ['email' => $member->email, 'password' => 'member123'])->assertRedirect(route('portal.profile'));
    }

    public function test_admin_can_create_access_for_older_members_and_sync_profile_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = $this->member();
        $user = $member->user;
        $member->user()->dissociate();
        $member->save();
        $user->delete();
        $this->actingAs($admin)->post(route('members.access', $member), ['password' => 'newpass123', 'password_confirmation' => 'newpass123'])->assertSessionHasNoErrors()->assertSessionHas('access_credentials');
        $member->refresh();
        $this->put(route('members.update', $member), ['first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => 'changed@example.test', 'membership_category_id' => $member->membership_category_id, 'membership_level_id' => $member->membership_level_id])->assertSessionHasNoErrors();
        $this->assertSame('changed@example.test', $member->fresh()->user->email);
    }

    public function test_admin_has_complete_task_crud_and_assignment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = $this->member();
        $this->actingAs($admin)->post('/tasks', ['task_title' => 'Assigned activity', 'member_id' => $member->id, 'due_date' => '2026-12-01'])->assertSessionHasNoErrors();
        $task = Task::firstOrFail();
        $this->assertSame($member->id, $task->member_id);
        $this->get(route('tasks.edit', $task))->assertOk()->assertSee('Assigned activity');
        $this->put(route('tasks.update', $task), ['task_title' => 'Community activity', 'member_id' => ''])->assertSessionHasNoErrors();
        $this->assertNull($task->fresh()->member_id);
        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        $this->assertModelMissing($task);
    }

    public function test_application_is_stored_with_consent_and_queued_notification(): void
    {
        Notification::fake();
        $this->post(route('membership.store'), $this->applicationData(['latitude' => '14.5995', 'longitude' => '120.9842', 'location_consent' => '1']))->assertRedirect(route('membership.create'))->assertSessionHasNoErrors();
        $application = MembershipApplication::firstOrFail();
        $this->assertSame('pending', $application->status);
        $this->assertNotNull($application->verification_queued_at);
        $this->assertNull($application->verification_sent_at);
        $this->assertNull($application->email_verified_at);
        $this->assertSame('14.5995000', $application->latitude);
        Notification::assertSentOnDemand(MembershipApplicationSubmitted::class, fn ($notification) => $notification->afterCommit === true);
    }

    public function test_application_validates_coordinates_consent_email_and_category(): void
    {
        Notification::fake();
        $this->post('/membership/apply', $this->applicationData(['latitude' => 91, 'longitude' => 200]))->assertSessionHasErrors(['latitude', 'longitude', 'location_consent']);
        $this->post('/membership/apply', $this->applicationData(['email' => 'bad', 'privacy_consent' => 0, 'membership_category_id' => 9999]))->assertSessionHasErrors(['email', 'privacy_consent', 'membership_category_id']);
        $this->assertDatabaseCount('membership_applications', 0);
        Notification::assertNothingSent();
    }

    public function test_email_confirmation_is_signed_and_does_not_approve_membership(): void
    {
        Notification::fake();
        $this->post('/membership/apply', $this->applicationData());
        $application = MembershipApplication::firstOrFail();
        $url = URL::temporarySignedRoute('membership.verify', now()->addDay(), ['application' => $application->id, 'hash' => sha1($application->email)]);
        $this->get($url)->assertOk()->assertSee('Email Confirmed');
        $this->assertNotNull($application->fresh()->email_verified_at);
        $this->assertSame('pending', $application->fresh()->status);
        $this->get($url.'&tamper=1')->assertForbidden();
        $this->travel(2)->days();
        $this->get($url)->assertForbidden();
        $this->travelBack();
    }

    public function test_admin_can_convert_an_application_only_once(): void
    {
        Notification::fake();
        $this->post('/membership/apply', $this->applicationData());
        $application = MembershipApplication::firstOrFail();
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/members/create?application='.$application->id)->assertOk()->assertSee('applicant@example.test');
        $data = ['first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => $application->email, 'membership_category_id' => $application->membership_category_id, 'membership_level_id' => $application->membership_level_id, 'application_id' => $application->id];
        $this->post('/members', $data)->assertSessionHasNoErrors();
        $this->assertSame('registered', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->member_id);
        $this->get('/members/create?application='.$application->id)->assertNotFound();
        $this->assertDatabaseCount('members', 1);
    }

    public function test_weather_is_protected_validated_cached_and_does_not_expose_key(): void
    {
        config(['services.openweather.key' => 'secret-weather-key']);
        Http::fake(['api.openweathermap.org/*' => Http::response(['name' => 'Manila', 'main' => ['temp' => 29, 'humidity' => 70], 'weather' => [['description' => 'clear sky']]])]);
        $this->getJson('/weather?lat=14&lon=120')->assertForbidden();
        $url = URL::temporarySignedRoute('weather.show', now()->addHour());
        $this->getJson($url.'&lat=91&lon=120')->assertUnprocessable();
        $this->getJson($url.'&lat=14.5995&lon=120.9842')->assertOk()->assertJsonPath('temperature', 29)->assertDontSee('secret-weather-key');
        $this->getJson($url.'&lat=14.5995&lon=120.9842')->assertOk();
        Http::assertSentCount(1);
        $this->get('/membership/apply')->assertOk()->assertDontSee('secret-weather-key');
    }

    public function test_weather_and_maps_fail_gracefully_when_unconfigured(): void
    {
        config(['services.openweather.key' => null, 'services.google_maps.key' => null]);
        Http::fake();
        $url = URL::temporarySignedRoute('weather.show', now()->addHour());
        $this->getJson($url.'&lat=14&lon=120')->assertStatus(503)->assertJsonPath('message', 'Weather is not configured yet. Add OPENWEATHER_KEY to the server settings.');
        Http::assertNothingSent();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/status')->assertOk()->assertSee('Needs browser key')->assertSee('Needs server key');
    }

    public function test_weather_provider_error_is_sanitized(): void
    {
        config(['services.openweather.key' => 'secret-test-key']);
        Http::fake(['*' => Http::response(['message' => 'Invalid key secret-test-key'], 401)]);
        $url = URL::temporarySignedRoute('weather.show', now()->addHour());
        $this->getJson($url.'&lat=15&lon=121')->assertStatus(502)->assertDontSee('secret-test-key');
    }

    public function test_sidebar_has_real_role_specific_navigation(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/dashboard')->assertOk()->assertSee('workspace-sidebar')->assertSee(route('tasks.index'))->assertSee(route('applications.index'))->assertSee(route('status.index'));
        $member = $this->member();
        $this->actingAs($member->user)->get('/portal/profile')->assertOk()->assertSee('My Tasks')->assertSee('My Profile')->assertDontSee('Email, maps &amp; weather', false);
    }
}
