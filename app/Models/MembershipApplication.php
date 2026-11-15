<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipApplication extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'location_consent_at' => 'datetime', 'verification_queued_at' => 'datetime', 'verification_sent_at' => 'datetime', 'email_verified_at' => 'datetime'];
    }

    public function category()
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function level()
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
