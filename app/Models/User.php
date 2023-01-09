<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $admin
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Role[] $roles
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';

    protected $casts = [
        'admin' => 'bool'
    ];

    protected $dates = [
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'admin',
        'email_verified_at',
        'password',
        'remember_token'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('active', 'assign_by')
            ->withTimestamps();
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}