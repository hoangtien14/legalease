<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AvailabilitySlot extends Model
{
    protected $fillable = ['lawyer_profile_id','available_date','start_time','end_time','status'];
    protected $casts = ['available_date'=>'date'];
    public function lawyer(): BelongsTo { return $this->belongsTo(LawyerProfile::class, 'lawyer_profile_id'); }
}
