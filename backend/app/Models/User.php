<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'role',
        'status',
        'password',
    ];

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

    /**
     * Set the password attribute (automatically hash it).
     * Only hash if the value is not already hashed
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        // Only hash if not already hashed (check if it starts with bcrypt hash format)
        if (!preg_match('/^\$2[ayb]\$/', $value)) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the apartments owned by this user.
     */
    public function ownedApartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    /**
     * Get the apartments where this user is a resident.
     */
    public function residences()
    {
        return $this->hasMany(Resident::class);
    }

    /**
     * Get the notifications created by this user.
     */
    public function createdNotifications()
    {
        return $this->hasMany(Notification::class, 'created_by');
    }

    /**
     * Get the notifications received by this user.
     */
    public function receivedNotifications()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    /**
     * Get the feedbacks submitted by this user.
     */
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the feedbacks assigned to this user (for technicians).
     */
    public function assignedFeedbacks()
    {
        return $this->hasMany(Feedback::class, 'assigned_to');
    }

    /**
     * Get the payments made by this user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the payments processed by this user.
     */
    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    /**
     * Get the devices assigned to this user (for technicians).
     */
    public function assignedDevices()
    {
        return $this->hasMany(Device::class, 'responsible_technician');
    }

    /**
     * Get the maintenances assigned to this user (for technicians).
     */
    public function assignedMaintenances()
    {
        return $this->hasMany(Maintenance::class, 'assigned_technician');
    }

    /**
     * Get the events created by this user.
     */
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Get the votes created by this user.
     */
    public function createdVotes()
    {
        return $this->hasMany(Vote::class, 'created_by');
    }

    /**
     * Get the vote responses by this user.
     */
    public function voteResponses()
    {
        return $this->hasMany(VoteResponse::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is resident.
     */
    public function isResident()
    {
        return $this->role === 'resident';
    }

    /**
     * Check if user is accountant.
     */
    public function isAccountant()
    {
        return $this->role === 'accountant';
    }

    /**
     * Check if user is technician.
     */
    public function isTechnician()
    {
        return $this->role === 'technician';
    }

    /**
     * Check if user is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }
} 