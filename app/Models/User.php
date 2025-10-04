<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pnph_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The column name of the "remember me" token.
     *
     * @var string
     */
    protected $rememberTokenName = 'token';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_fname',
        'user_lname',
        'user_mInitial',
        'user_suffix',
        'gender',
        'user_email',
        'user_role',
        'user_password',
        'status',
        'is_temp_password',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_password',
        'remember_token',
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_temp_password' => 'boolean',
    ];

    /**
     * Get the email for authentication.
     */
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        // Must match the model's primary key so the session guard can
        // retrieve the user on subsequent requests.
        return 'user_id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        // Store the primary key in the session for proper retrieval.
        return $this->user_id;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPasswordName()
    {
        return 'user_password';
    }



    /**
     * Get the user's full name.
     */
    public function getNameAttribute()
    {
        $name = $this->user_fname . ' ';
        if ($this->user_mInitial) {
            $name .= $this->user_mInitial . '. ';
        }
        $name .= $this->user_lname;
        if ($this->user_suffix) {
            $name .= ' ' . $this->user_suffix;
        }
        return $name;
    }

    /**
     * Get the user's email.
     */
    public function getEmailAttribute()
    {
        return $this->user_email;
    }

    /**
     * Get the user's role.
     */
    public function getRoleAttribute()
    {
        return $this->user_role;
    }

    /**
     * Get the user's password.
     */
    public function getPasswordAttribute()
    {
        return $this->user_password;
    }



    /**
     * Get the name of the column used for authentication.
     */
    public function username()
    {
        return 'user_email';
    }

    /**
     * Find the user instance for the given username.
     */
    public function findForPassport($username)
    {
        return $this->where('user_email', $username)->first();
    }

    /**
     * Get the dashboard route based on user role.
     *
     * @return string
     */
    public function getDashboardRoute()
    {
        return match($this->user_role) {
            'admin' => 'cook.dashboard',
            'student' => 'student.dashboard',
            'cook' => 'cook.dashboard',
            'kitchen' => 'kitchen.dashboard',
            default => 'cook.dashboard',
        };
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->user_role === $role;
    }

    /**
     * Check if user is an admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->user_role === 'admin';
    }

    /**
     * Check if user can manage other users
     *
     * @return bool
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage system settings
     *
     * @return bool
     */
    public function canManageSettings(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can view all data
     *
     * @return bool
     */
    public function canViewAllData(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Get student details relationship
     */
    public function studentDetails()
    {
        return $this->hasOne(StudentDetails::class, 'user_id', 'user_id');
    }

    /**
     * Get feedback submitted by this user (if student)
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'student_id', 'user_id');
    }

    /**
     * Get pre-orders made by this user (if student)
     */
    public function preOrders()
    {
        return $this->hasMany(PreOrder::class, 'user_id', 'user_id');
    }
}
