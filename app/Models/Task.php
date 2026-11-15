<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'task_title',
        'description',
        'completed', 'member_id', 'due_date',
    ];

    protected function casts(): array
    {
        return ['completed' => 'boolean', 'due_date' => 'date', 'is_personal' => 'boolean'];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeAdministrative($query)
    {
        return $query->where('is_personal', false);
    }

    public function scopeVisibleToMember($query, Member $member)
    {
        return $query->where(function ($query) use ($member) {
            $query->where(function ($shared) use ($member) {
                $shared->administrative()->where(fn ($assigned) => $assigned->whereNull('member_id')->orWhere('member_id', $member->id));
            })->orWhere(function ($personal) use ($member) {
                $personal->where('is_personal', true)->where('member_id', $member->id)->where('created_by', $member->user_id);
            });
        });
    }
}
