<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Review extends Model
{
    protected $fillable = ['appointment_id','customer_profile_id','lawyer_profile_id','rating','comment','is_visible'];
    protected $casts = ['is_visible'=>'boolean'];
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function customer(): BelongsTo { return $this->belongsTo(CustomerProfile::class); }
    public function lawyer(): BelongsTo { return $this->belongsTo(LawyerProfile::class); }
}
