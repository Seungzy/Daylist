<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Task;
use App\Support\AdminListSort;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        return view('tasks.index', ['tasks' => AdminListSort::apply(Task::administrative()->with('member'), $request, ['task_title'])->paginate(12)->withQueryString()]);
    }

    public function create()
    {
        return view('tasks.form', ['task' => new Task, 'members' => Member::where('is_active', true)->orderBy('last_name')->get()]);
    }

    public function edit(Task $task)
    {
        abort_if($task->is_personal, 403);

        return view('tasks.form', compact('task') + ['members' => Member::orderBy('last_name')->get()]);
    }

    private function data(Request $request): array
    {
        return $request->validate(['task_title' => 'required|string|max:255', 'description' => 'nullable|string|max:5000', 'member_id' => 'nullable|exists:members,id', 'due_date' => 'nullable|date_format:Y-m-d']);
    }

    public function store(Request $request)
    {
        $task = new Task($this->data($request));
        $task->created_by = $request->user()->id;
        $task->save();

        return redirect()->route('tasks.index')->with('success', 'Task added successfully.');
    }

    public function update(Request $request, Task $task)
    {
        abort_if($task->is_personal, 403);
        $task->update($this->data($request));

        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        abort_if($task->is_personal, 403);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function updateCompletion(Request $request, Task $task)
    {
        abort_if($task->is_personal, 403);
        $task->update($request->validate(['completed' => 'required|boolean']));

        return redirect()->route('tasks.index')->with('success', $task->completed ? 'Task marked as complete.' : 'Task marked as incomplete.');
    }
}
