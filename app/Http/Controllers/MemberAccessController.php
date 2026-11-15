<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberAccessController extends Controller
{
    public function __invoke(Request $request, Member $member)
    {
        $data = $request->validate(['password' => 'nullable|string|min:6|max:128|confirmed', 'email' => ['sometimes', 'email', Rule::in([$member->email])]]);
        $password = $data['password'] ?? Str::password(14);
        DB::transaction(function () use ($member, $password) {
            $member = Member::lockForUpdate()->findOrFail($member->id);
            validator(['email' => $member->email], ['email' => Rule::unique('users')->ignore($member->user_id)])->validate();
            $user = $member->user ?? new User;
            abort_if($user->is_admin, 403);
            $user->fill(['name' => $member->full_name, 'email' => $member->email, 'password' => $password]);
            $user->save();
            $member->user()->associate($user);
            $member->save();
        });

        return redirect()->route('members.show', $member)->with('access_credentials', ['email' => $member->email, 'password' => $password])->with('success', 'Member login saved. Share the credentials privately with this member.');
    }
}
