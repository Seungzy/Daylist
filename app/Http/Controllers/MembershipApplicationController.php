<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Notifications\MembershipApplicationSubmitted;
use App\Services\MembershipService;
use App\Support\AdminListSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MembershipApplicationController extends Controller
{
    public function create()
    {
        return view('applications.create', ['categories' => MembershipCategory::orderBy('name')->get(), 'levels' => MembershipLevel::orderBy('fee')->get()]);
    }

    public function store(StoreMembershipApplicationRequest $request)
    {
        $data = $request->safe()->except(['privacy_consent', 'location_consent']);
        $application = DB::transaction(function () use ($data) {
            $application = MembershipApplication::create($data + ['reference_no' => MembershipService::reference('APP'), 'fee' => MembershipLevel::findOrFail($data['membership_level_id'])->fee, 'location_consent_at' => isset($data['latitude']) ? now() : null, 'verification_queued_at' => now()]);
            Notification::route('mail', $application->email)->notify(new MembershipApplicationSubmitted($application));

            return $application;
        });

        return redirect()->route('membership.create')->with('success', 'Application saved. Confirmation email queued. Reference: '.$application->reference_no);
    }

    public function verify(Request $request, MembershipApplication $application, string $hash)
    {
        abort_unless(hash_equals(sha1($application->email), $hash), 403);
        if (! $application->email_verified_at) {
            $application->update(['email_verified_at' => now()]);
        }

        return view('applications.verified');
    }

    public function index(Request $request)
    {
        return view('applications.index', ['applications' => AdminListSort::apply(MembershipApplication::with('category', 'level', 'member'), $request, ['full_name'])->paginate(12)->withQueryString()]);
    }

    public function show(MembershipApplication $application)
    {
        $application->load('category', 'level', 'member');

        return view('applications.show', compact('application'));
    }
}
