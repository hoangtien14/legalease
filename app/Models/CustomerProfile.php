<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class CustomerProfile extends Model
{
    protected $fillable = ['user_id','location_id','avatar','date_of_birth','gender','address','notes'];
    protected $casts = ['date_of_birth'=>'date'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
}
