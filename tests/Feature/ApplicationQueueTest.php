<?php

namespace Tests\Feature;

use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Database\Seeders\MembershipSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationQueueTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'database', 'mail.default' => 'array']);
        $this->seed(MembershipSeeder::class);
    }

    private function data(): array
    {
        return ['full_name' => 'Queue Test', 'email' => 'queue@example.test', 'membership_category_id' => MembershipCategory::first()->id, 'membership_level_id' => MembershipLevel::first()->id, 'privacy_consent' => '1'];
    }

    public function test_email_job_is_processed_and_delivery_timestamp_is_not_premature(): void
    {
        $this->post('/membership/apply', $this->data())->assertSessionHasNoErrors();
        $application = MembershipApplication::firstOrFail();
        $this->assertDatabaseCount('jobs', 1);
        $this->assertNull($application->verification_sent_at);
        Artisan::call('queue:work', ['--once' => true, '--tries' => 1]);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertNotNull($application->fresh()->verification_sent_at);
    }

    public function test_rolled_back_application_does_not_leave_an_email_job(): void
    {
        try {
            DB::transaction(function () {
                $this->post('/membership/apply', $this->data())->assertSessionHasNoErrors();
                $this->assertDatabaseCount('membership_applications', 1);
                $this->assertDatabaseCount('jobs', 0);
                throw new \RuntimeException('Deliberate rollback');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Deliberate rollback', $exception->getMessage());
        }
        $this->assertDatabaseCount('membership_applications', 0);
        $this->assertDatabaseCount('jobs', 0);
    }
}
