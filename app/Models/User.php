<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Searchable;
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fcm_token',
        'user_name',
        'email',
        'password',
        'employee_id',
        'full_name',
        'branch_id',
        'department_id',
        'role_id',
        'active'
    ];

    /**
     * Get the user that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Get all of the purchases for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'purchased_by', 'id');
    }

    /**
     * Get all of the requests for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'requester_id', 'id');
    }

    /**
     * Get all of the sparePartRequests for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sparePartRequests(): HasMany
    {
        return $this->hasMany(SparePartRequest::class, 'requester_id', 'id');
    }

    /**
     * Get the branch that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'branch_id', 'id');
    }

    /**
     * Get all of the assets for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'owner_id', 'id');
    }

    /**
     * Get all of the handledTickets for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function handledTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'handler_id', 'id');
    }

    /**
     * Get all of the tickets for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by_id', 'id');
    }

    /**
     * Get the department that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function toSearchableArray(): array
    {
        return [
            'user_name' => '',
            'full_name' => '',
            'employee_id' => '',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
        // return $this->getDeviceTokens();
    }
}
