<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class LawyerProfile extends Model
{
    protected $fillable = ['user_id','location_id','license_number','avatar','title','bio','education','experience','experience_years','consultation_fee','verification_status'];
    protected $casts = ['consultation_fee'=>'decimal:2','experience_years'=>'integer'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function specializations(): BelongsToMany { return $this->belongsToMany(Specialization::class, 'lawyer_specializations'); }
    public function availabilitySlots(): HasMany { return $this->hasMany(AvailabilitySlot::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
}
