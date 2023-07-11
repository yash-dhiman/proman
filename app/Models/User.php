<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable     = [
                                'first_name',
                                'last_name',
                                'email',
                                'password',
                                'company_id',
                                'role_id',
                            ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden       = [
                                'password',
                                'remember_token'
                            ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function find_users(int $company_id, int $user_id = null, array $filter = array())
    {
        $query  = User::select('users.*')->join('company', 'company.company_id', 'users.company_id');

        if($user_id)
        {
            $query  = $query->where('users.user_id', $user_id);
        }

        return $query->where('users.company_id', $company_id)->get()->toArray();
    }

    public static function update_user(int $company_id, int $user_id, array $user_data)
    {
        return User::where('users.user_id', $user_id)
            ->where('users.company_id', $company_id)
            ->update($user_data);
    }
}
