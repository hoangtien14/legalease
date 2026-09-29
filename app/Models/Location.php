<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Location extends Model
{
    protected $fillable = ['province','district','address','description','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function lawyerProfiles(): HasMany { return $this->hasMany(LawyerProfile::class); }
    public function customerProfiles(): HasMany { return $this->hasMany(CustomerProfile::class); }
}
