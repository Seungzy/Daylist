<label for="task_title">Task Title *</label>
<input id="task_title" name="task_title" maxlength="255" value="{{ old('task_title',$task->task_title) }}" placeholder="What needs to get done?" required>
<label for="description">Description <span class="muted">(optional)</span></label>
<textarea id="description" name="description" maxlength="5000" rows="4" placeholder="A few details, if you need them…">{{ old('description',$task->description) }}</textarea>
<label for="due_date">Due Date <span class="muted">(optional)</span></label>
<input id="due_date" name="due_date" type="date" value="{{ old('due_date',$task->due_date?->format('Y-m-d')) }}">
