<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use App\Services\SmtpStatusChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $mailer = config('mail.default');
        $settings = config("mail.mailers.$mailer", []);
        $smtpCheck = $request->session()->get('smtp_status');
        if (($smtpCheck['configuration'] ?? null) !== $this->configurationFingerprint()) {
            $smtpCheck = null;
        }

        return view('membership.integrations', [
            'mailer' => $mailer, 'settings' => $settings, 'smtpCheck' => $smtpCheck,
            'waiting' => DB::table('jobs')->whereNull('reserved_at')->count(),
            'processing' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'lastMail' => MembershipApplication::whereNotNull('verification_sent_at')->latest('verification_sent_at')->first()?->verification_sent_at,
        ]);
    }

    public function smtp(Request $request, SmtpStatusChecker $checker)
    {
        $request->session()->put('smtp_status', $checker->check() + [
            'checked_at' => now()->format('M d, Y H:i:s'),
            'configuration' => $this->configurationFingerprint(),
        ]);

        return redirect()->route('status.index');
    }

    private function configurationFingerprint(): string
    {
        // Store only a hash in the session so a changed password invalidates an old result.
        $mailer = config('mail.default');

        return hash('sha256', json_encode([$mailer, config("mail.mailers.$mailer"), config('mail.from')]));
    }
}
