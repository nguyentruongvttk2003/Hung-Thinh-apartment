<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_date',
        'time_ago',
    ];

    /**
     * Relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entity that this log refers to
     */
    public function entity()
    {
        if ($this->entity_type && $this->entity_id) {
            $modelClass = 'App\\Models\\' . $this->entity_type;
            if (class_exists($modelClass)) {
                return $modelClass::find($this->entity_id);
            }
        }
        return null;
    }

    /**
     * Get formatted date attribute
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Get time ago attribute
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope for filtering by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by entity
     */
    public function scopeByEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);
        
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay()
        ]);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Static method to log activity
     */
    public static function logActivity($action, $entityType = null, $entityId = null, $description = null, $properties = [])
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Static method to log login activity
     */
    public static function logLogin($userId, $successful = true)
    {
        return self::create([
            'user_id' => $userId,
            'action' => $successful ? 'login_success' : 'login_failed',
            'description' => $successful ? 'User logged in successfully' : 'Failed login attempt',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Static method to log logout activity
     */
    public static function logLogout($userId)
    {
        return self::create([
            'user_id' => $userId,
            'action' => 'logout',
            'description' => 'User logged out',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Static method to log CRUD operations
     */
    public static function logCrud($action, $model, $description = null)
    {
        $entityType = class_basename($model);
        $entityId = $model->id ?? null;
        
        if (!$description) {
            $description = ucfirst($action) . ' ' . $entityType;
        }

        return self::logActivity($action, $entityType, $entityId, $description, [
            'model_data' => $model->toArray()
        ]);
    }

    /**
     * Get activity icon based on action
     */
    public function getIconAttribute()
    {
        $icons = [
            'login_success' => 'login',
            'login_failed' => 'login-failed',
            'logout' => 'logout',
            'create' => 'plus',
            'update' => 'edit',
            'delete' => 'trash',
            'view' => 'eye',
            'download' => 'download',
            'upload' => 'upload',
            'send' => 'send',
            'approve' => 'check',
            'reject' => 'x',
            'cancel' => 'x-circle',
            'complete' => 'check-circle',
        ];

        return $icons[$this->action] ?? 'activity';
    }

    /**
     * Get activity color based on action
     */
    public function getColorAttribute()
    {
        $colors = [
            'login_success' => 'green',
            'login_failed' => 'red',
            'logout' => 'gray',
            'create' => 'blue',
            'update' => 'yellow',
            'delete' => 'red',
            'view' => 'gray',
            'download' => 'indigo',
            'upload' => 'purple',
            'send' => 'blue',
            'approve' => 'green',
            'reject' => 'red',
            'cancel' => 'orange',
            'complete' => 'green',
        ];

        return $colors[$this->action] ?? 'gray';
    }
}
