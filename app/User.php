<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'phone', 'status', 'profile_image', 'role_id', 'vendor_id',
        'last_login_at'
    ];

//    public function role()
//    {
//        return $this->belongsTo('App\Role');
//    }

    /**
     * Determines if the User is a Super admin
     * @return null
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('super-admin');
    }

    public function isCurator()
    {
        return $this->hasRole('Curator');
    }

    public function isVendor()
    {
        return $this->hasRole('Vendor');
    }

    public function isManager()
    {
        return $this->hasRole('Manager');
    }

    public function isDeveloper()
    {
        return $this->hasRole('Developer');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo('App\Vendor', 'vendor_id');
    }

    public function publications()
    {
        return $this->hasMany('App\Publication','created_by');
    }

    public function connectedProducts()
    {
        return $this->hasMany('App\PublicationVendorProductConnectionLog','user_id');
    }
}
