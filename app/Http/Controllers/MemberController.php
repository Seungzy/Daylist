<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:100', 'category' => 'nullable|integer', 'level' => 'nullable|integer']);
        $members = Member::with('category', 'level')->withExists(['transactions as membership_active' => fn ($q) => $q->where('status', 'approved')->whereDate('valid_from', '<=', today())->whereDate('valid_until', '>=', today())])
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('membership_number', 'like', "%$term%")
                        ->orWhere(function ($names) use ($term) {
                            foreach (preg_split('/\s+/', trim($term)) as $part) {
                                $names->where(fn ($name) => $name->where('first_name', 'like', "%$part%")->orWhere('last_name', 'like', "%$part%"));
                            }
                        });
                });
            })
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('membership_category_id', $id))->when($filters['level'] ?? null, fn ($q, $id) => $q->where('membership_level_id', $id))->latest('id')->paginate(12)->withQueryString();

        return view('membership.members.index', compact('members') + $this->choices());
    }

    private function choices(): array
    {
        return ['categories' => MembershipCategory::orderBy('name')->get(), 'levels' => MembershipLevel::orderBy('fee')->get()];
    }

    public function create(Request $request)
    {
        $application = $request->filled('application') ? MembershipApplication::whereNull('member_id')->findOrFail($request->integer('application')) : null;
        $member = new Member;
        if ($application) {
            $names = explode(' ', $application->full_name, 2);
            $member->fill(['first_name' => $names[0], 'last_name' => $names[1] ?? '', 'email' => $application->email, 'phone' => $application->phone, 'address' => $application->address, 'membership_category_id' => $application->membership_category_id, 'membership_level_id' => $application->membership_level_id, 'latitude' => $application->latitude, 'longitude' => $application->longitude, 'location_consent_at' => $application->location_consent_at]);
        }

        return view('membership.members.form', compact('member', 'application') + $this->choices());
    }

    private function validated(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:100', 'last_name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('members')->ignore($member?->id), Rule::unique('users')->ignore($member?->user_id)],
            'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:2000',
            'membership_category_id' => 'required|exists:membership_categories,id', 'membership_level_id' => 'required|exists:membership_levels,id',
            'membership_number' => ['sometimes', 'required', 'string', 'max:40', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('members')->ignore($member?->id)],
            'password' => 'nullable|string|min:6|max:128|confirmed',
            'application_id' => 'nullable|exists:membership_applications,id',
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'location_consent' => 'nullable|required_with:latitude,longitude|in:1,on',
        ]);
    }

    public function store(Request $request, MembershipService $service)
    {
        $data = $this->validated($request);
        $password = $data['password'] ?? Str::password(14);
        $applicationId = $data['application_id'] ?? null;
        unset($data['password'],$data['application_id'],$data['location_consent']);
        $member = DB::transaction(function () use ($data, $password, $applicationId, $request, $service) {
            $application = $applicationId ? MembershipApplication::lockForUpdate()->findOrFail($applicationId) : null;
            if ($application?->member_id) {
                throw ValidationException::withMessages(['application_id' => 'This application is already registered.']);
            }
            $user = User::create(['name' => $data['first_name'].' '.$data['last_name'], 'email' => $data['email'], 'password' => $password]);
            $member = Member::create(['membership_number' => MembershipService::reference('MEM'), 'location_consent_at' => isset($data['latitude']) ? now() : null] + $data);
            $member->user()->associate($user);
            $member->save();
            $service->create($member, $member->level, 'application', $request->user()->id);
            if ($application) {
                $application->update(['member_id' => $member->id, 'status' => 'registered']);
            }

            return $member;
        });

        return redirect()->route('members.show', $member)->with('access_credentials', ['email' => $member->email, 'password' => $password])->with('success', 'Member registered. A membership application transaction and member login have been created.');
    }

    public function show(Member $member)
    {
        $member->load('category', 'level');
        $transactions = $member->transactions()->with('level', 'payments')->latest('id')->paginate(10);

        return view('membership.members.show', compact('member', 'transactions'));
    }

    public function edit(Member $member)
    {
        return view('membership.members.form', compact('member') + $this->choices());
    }

    public function update(Request $request, Member $member)
    {
        $data = $this->validated($request, $member);
        unset($data['password'],$data['application_id'],$data['location_consent']);
        if (array_key_exists('latitude', $data)) {
            $data['location_consent_at'] = isset($data['latitude']) ? now() : null;
        }
        DB::transaction(function () use ($member, $data) {
            $member->update($data);
            $member->user?->update(['name' => $member->full_name, 'email' => $member->email]);
        });

        return redirect()->route('members.show', $member)->with('success', 'Member details updated.');
    }

    public function destroy(Member $member)
    {
        DB::transaction(function () use ($member) {
            $member = Member::lockForUpdate()->findOrFail($member->id);
            $member->update(['is_active' => false]);
        });

        return back()->with('success', 'Member deactivated. Transaction and payment history is preserved.');
    }

    public function reactivate(Member $member)
    {
        $member->update(['is_active' => true]);

        return back()->with('success', 'Member reactivated.');
    }
}
