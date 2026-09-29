<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['role_id','name','email','phone','password','is_active'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['is_active'=>'boolean','email_verified_at'=>'datetime'];
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function isSuperAdmin(): bool { return $this->role?->name === 'super_admin'; }
    public function isLawyer(): bool { return $this->role?->name === 'lawyer'; }
    public function isCustomer(): bool { return $this->role?->name === 'customer'; }
    public function lawyerProfile(): HasOne { return $this->hasOne(LawyerProfile::class); }
    public function customerProfile(): HasOne { return $this->hasOne(CustomerProfile::class); }
    public function notifications(): HasMany { return $this->hasMany(Notification::class); }
    public function news(): HasMany { return $this->hasMany(News::class, 'author_id'); }
}
