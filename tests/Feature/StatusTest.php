<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SmtpStatusChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_and_smtp_checks_are_admin_only(): void
    {
        $this->get('/status')->assertRedirect('/login');
        $this->post('/status/smtp')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/status')->assertForbidden();
        $this->post('/status/smtp')->assertForbidden();
    }

    public function test_status_shows_settings_without_running_smtp_or_exposing_secrets(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.password' => 'PRIVATE-PASSWORD', 'mail.mailers.smtp.username' => 'private@example.test', 'services.openweather.key' => 'PRIVATE-WEATHER-KEY']);
        $this->mock(SmtpStatusChecker::class)->shouldNotReceive('check');
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/status')->assertOk()->assertSee('Not checked yet')->assertSee('Current Mailer')->assertSee('Worker availability is not monitored')->assertDontSee('PRIVATE-PASSWORD')->assertDontSee('PRIVATE-WEATHER-KEY');
        $this->get('/integrations')->assertRedirect(route('status.index'));
    }

    public function test_smtp_check_result_is_displayed_and_invalidated_when_settings_change(): void
    {
        config(['mail.default' => 'smtp']);
        $this->mock(SmtpStatusChecker::class)->shouldReceive('check')->once()->andReturn(['status' => 'working', 'message' => 'SMTP connection and configured authentication succeeded. No email was sent.']);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post('/status/smtp')->assertRedirect(route('status.index'));
        $this->get('/status')->assertOk()->assertSee('SMTP connected')->assertSee('No email was sent.');
        config(['mail.mailers.smtp.password' => 'changed-password']);
        $this->get('/status')->assertOk()->assertSee('Not checked yet')->assertDontSee('SMTP connected');
    }

    public function test_log_mailer_is_not_reported_as_connected_smtp(): void
    {
        config(['mail.default' => 'log']);
        $result = app(SmtpStatusChecker::class)->check();
        $this->assertSame('not_applicable', $result['status']);
    }

    public function test_smtp_service_opens_and_closes_connection_without_sending(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.transport' => 'smtp']);
        $transport = Mockery::mock(SmtpTransport::class);
        $transport->shouldReceive('getStream')->once()->andReturn(new SocketStream);
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('stop')->once();
        $transport->shouldNotReceive('send');
        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('getSymfonyTransport')->once()->andReturn($transport);
        Mail::shouldReceive('mailer')->with('smtp')->once()->andReturn($mailer);
        $this->assertSame('working', app(SmtpStatusChecker::class)->check()['status']);
    }

    public function test_smtp_failure_does_not_expose_server_error_or_credentials(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.transport' => 'smtp']);
        Mail::shouldReceive('mailer')->andThrow(new \RuntimeException('535 authenticate failed for private@example.test using SECRET-PASSWORD'));
        $result = app(SmtpStatusChecker::class)->check();
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('authentication failed', $result['message']);
        $this->assertStringNotContainsString('SECRET-PASSWORD', json_encode($result));
        $this->assertStringNotContainsString('private@example.test', json_encode($result));
    }
}
