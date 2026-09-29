<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Specialization extends Model
{
    protected $fillable = ['name','slug','description','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function lawyers(): BelongsToMany
    {
        return $this->belongsToMany(LawyerProfile::class, 'lawyer_specializations');
    }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
}
