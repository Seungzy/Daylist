<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeleteMemberController extends Controller
{
    public function __invoke(Request $request, Member $member)
    {
        $request->validate([
            'confirm_membership_number' => ['required', 'string', Rule::in([$member->membership_number])],
            'remove_history' => ['sometimes', 'boolean'],
        ], [
            'confirm_membership_number.in' => 'Enter the exact membership number to confirm permanent deletion.',
        ]);

        DB::transaction(function () use ($member, $request) {
            $member = Member::lockForUpdate()->findOrFail($member->id);
            $user = $member->user_id ? User::lockForUpdate()->find($member->user_id) : null;
            if ($user && ($user->is_admin || $user->id === $request->user()->id)) {
                throw ValidationException::withMessages(['deletion' => 'This member is linked to an administrator account and cannot be permanently deleted.']);
            }

            if ($member->transactions()->exists() && ! $request->boolean('remove_history')) {
                throw ValidationException::withMessages(['remove_history' => 'This member has transaction history. Select the checkbox to remove their transactions, receipts and report totals, or deactivate the member to preserve that history.']);
            }

            // The same member lock is used when creating payments and renewals.
            if ($request->boolean('remove_history')) {
                Payment::whereIn('membership_transaction_id', $member->transactions()->select('id'))->delete();
                $member->transactions()->delete();
            }

            if ($user && (MembershipTransaction::where('created_by', $user->id)->orWhere('approved_by', $user->id)->exists()
                || Payment::where('recorded_by', $user->id)->exists())) {
                throw ValidationException::withMessages(['deletion' => 'This login is referenced as the administrator of other financial records. Deactivate this member instead.']);
            }

            // Remove assignments rather than turning them into public community tasks.
            $member->tasks()->delete();
            MembershipApplication::where('member_id', $member->id)->delete();
            $member->delete();
            $user?->delete();
        });

        return redirect()->route('members.index')->with('success', 'Member permanently deleted, including their login, assigned and personal tasks, and linked application. Any selected transaction and payment history was removed from receipts and report totals.');
    }
}
