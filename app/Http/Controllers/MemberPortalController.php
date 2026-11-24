<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class MemberPortalController extends Controller
{
    public function profile(Request $request)
    {
        $member = $request->user()->member->load('category', 'level');
        $approved = $member->transactions()->where('status', 'approved')->with('level');
        $currentMembership = (clone $approved)->whereDate('valid_from', '<=', today())->whereDate('valid_until', '>=', today())->orderByDesc('valid_until')->orderByDesc('id')->first();
        $upcomingMembership = $currentMembership ? null : (clone $approved)->whereDate('valid_from', '>', today())->orderBy('valid_from')->orderBy('id')->first();
        $cardMembership = $currentMembership ?? $upcomingMembership ?? (clone $approved)->orderByDesc('valid_until')->orderByDesc('id')->first();
        $cardStatus = $currentMembership ? 'Active' : ($upcomingMembership ? 'Upcoming' : ($cardMembership ? 'Expired' : 'Pending Activation'));
        $transactions = $member->transactions()->with('level', 'payments')->latest('id')->paginate(8);
        $taskCount = Task::visibleToMember($member)->count();

        return view('portal.profile', compact('member', 'transactions', 'taskCount', 'cardMembership', 'cardStatus'));
    }

    public function tasks(Request $request)
    {
        $tasks = Task::visibleToMember($request->user()->member)->orderBy('completed')->latest('id')->paginate(12);

        return view('portal.tasks', compact('tasks'));
    }
}
