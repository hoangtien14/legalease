<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Appointment extends Model
{
    protected $fillable = ['customer_profile_id','lawyer_profile_id','specialization_id','availability_slot_id','appointment_at','consultation_type','location','problem_description','attachment','status','customer_note','lawyer_note'];
    protected $casts = ['appointment_at'=>'datetime'];
    public function customer(): BelongsTo { return $this->belongsTo(CustomerProfile::class, 'customer_profile_id'); }
    public function lawyer(): BelongsTo { return $this->belongsTo(LawyerProfile::class, 'lawyer_profile_id'); }
    public function specialization(): BelongsTo { return $this->belongsTo(Specialization::class); }
    public function availabilitySlot(): BelongsTo { return $this->belongsTo(AvailabilitySlot::class); }
    public function review(): HasOne { return $this->hasOne(Review::class); }
    public function isActive(): bool { return in_array($this->status, ['pending','confirmed','rescheduled'], true); }
    public function canBeReviewed(): bool { return $this->status === 'completed' && !$this->review()->exists(); }
}
