<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class MemberPortalController extends Controller
{
    public function profile(Request $request)
    {
        $member = $request->user()->member->load('category', 'level');
        $transactions = $member->transactions()->with('level', 'payments')->latest('id')->paginate(8);
        $taskCount = Task::visibleToMember($member)->count();

        return view('portal.profile', compact('member', 'transactions', 'taskCount'));
    }

    public function tasks(Request $request)
    {
        $tasks = Task::visibleToMember($request->user()->member)->orderBy('completed')->latest('id')->paginate(12);

        return view('portal.tasks', compact('tasks'));
    }
}
