<?php

namespace Tests\Feature;

use App\Models\MembershipCategory;
use App\Models\User;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsyncWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private array $htmlHeaders = ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'text/html'];

    public function test_async_requests_keep_authentication_and_admin_protection(): void
    {
        $this->get(route('dashboard'), $this->htmlHeaders)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        $this->get(route('dashboard'), $this->htmlHeaders)->assertForbidden();
    }

    public function test_async_form_validation_returns_the_existing_html_error_flow(): void
    {
        $this->seed(MembershipSeeder::class);
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->from(route('categories.index'))->post(route('categories.store'), [
            'name' => MembershipCategory::firstOrFail()->name,
        ], $this->htmlHeaders)->assertRedirect(route('categories.index'))->assertSessionHasErrors('name');
        $this->get(route('categories.index'), $this->htmlHeaders)
            ->assertOk()->assertSee('Please check the following:')->assertSee('data-live-region', false);
    }
}
