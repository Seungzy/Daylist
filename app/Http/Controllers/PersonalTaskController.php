<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class PersonalTaskController extends Controller
{
    private function authorizeOwner(Request $request, Task $task): void
    {
        abort_unless($task->is_personal && (int) $task->created_by === (int) $request->user()->id && (int) $task->member_id === (int) $request->user()->member->id, 403);
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'task_title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'nullable|date_format:Y-m-d',
        ]);
    }

    public function store(Request $request)
    {
        $task = new Task($this->data($request));
        $task->is_personal = true;
        $task->member_id = $request->user()->member->id;
        $task->created_by = $request->user()->id;
        $task->save();

        return redirect()->route('portal.tasks')->with('success', 'Personal task added successfully.');
    }

    public function edit(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);

        return view('portal.task-edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);
        $task->update($this->data($request));

        return redirect()->route('portal.tasks')->with('success', 'Personal task updated.');
    }

    public function completion(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);
        $task->update($request->validate(['completed' => 'required|boolean']));

        return redirect()->route('portal.tasks')->with('success', $task->completed ? 'Personal task marked as complete.' : 'Personal task marked as incomplete.');
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);
        $task->delete();

        return redirect()->route('portal.tasks')->with('success', 'Personal task deleted.');
    }
}
