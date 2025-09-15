<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
protected $fillable = [
    'name',
    'email',
    'cedula',
    'password',
    'security_question_1',
    'security_answer_1',
    'security_question_2',
    'security_answer_2',
    'security_question_3',
    'security_answer_3',
    'is_first_login',
];
    protected $guarded = [];
    // Hashear la respuesta de seguridad al guardar
    protected static function booted()
    {
        static::creating(function ($user) {
            if (isset($user->security_answer_1)) {
                $user->security_answer_1 = bcrypt($user->security_answer_1);
            }
            if (isset($user->security_answer_2)) {
                $user->security_answer_2 = bcrypt($user->security_answer_2);
            }
            if (isset($user->security_answer_3)) {
                $user->security_answer_3 = bcrypt($user->security_answer_3);
            }
        });

    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'security_answer_1',
        'security_answer_2',
        'security_answer_3',
    ];

  protected $casts = [
    'security_answer_1' => 'hashed',
    'security_answer_2' => 'hashed',
    'security_answer_3' => 'hashed',
     'is_first_login' => 'boolean'
];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // 👈 este debe estar presente
        ];
    }

    /* public function hasRole($role)
     {
         return $this->role === $role; // ajusta si usas Spatie u otro sistema de roles
     }*/

    /* public function getRoleNamesAttribute()
     {
         return $this->getRoleNames();
     }*/

}
