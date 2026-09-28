<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active', 'phone', 'avatar', 'ic_number_hash',
        'nationality_type', 'passport_number', 'address_line_1', 'address_line_2',
        'postcode', 'city', 'state', 'country',
    ];

    /**
     * Interact with the user's name: always store and format as uppercase.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? mb_strtoupper($value, 'UTF-8') : $value,
            set: fn ($value) => $value ? mb_strtoupper(trim($value), 'UTF-8') : $value,
        );
    }

    // ──────────────────────────────────────────────────
    // IC (MyKad) Helpers
    // ──────────────────────────────────────────────────

    /**
     * Normalise a Malaysian IC string to 12 digits (strip dashes/spaces).
     */
    public static function normaliseIc(string $ic): string
    {
        return preg_replace('/[^0-9]/', '', $ic);
    }

    /**
     * Hash an IC number for secure storage (SHA-256).
     */
    public static function hashIc(string $ic): string
    {
        return hash('sha256', self::normaliseIc($ic));
    }

    /**
     * Store a hashed IC number on this user.
     */
    public function setIcNumber(string $ic): void
    {
        $this->ic_number_hash = self::hashIc($ic);
    }

    /**
     * Check if the given IC matches the stored hash.
     */
    public function matchesIc(string $ic): bool
    {
        return $this->ic_number_hash && hash_equals($this->ic_number_hash, self::hashIc($ic));
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'         => 'hashed',
            'is_active'        => 'boolean',
        ];
    }

    /**
     * Scope: users explicitly deactivated by admin (is_active = false).
     */
    public function scopeDeactivated($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Whether this user's account has been admin-deactivated.
     */
    public function isDeactivated(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Comprehensive check whether this account is active.
     * Takes into account users.is_active, student.status, and teacher.application_status.
     */
    public function isAccountActive(): bool
    {
        if ($this->is_active === false) {
            return false;
        }

        if ($this->role === 'student' && $this->studentProfile && $this->studentProfile->status === 'inactive') {
            return false;
        }

        if ($this->role === 'teacher' && $this->teacherProfile && in_array($this->teacherProfile->application_status, ['inactive', 'deactivated'])) {
            return false;
        }

        return true;
    }

    /**
     * Comprehensive check whether this account is inactive or deactivated.
     */
    public function isAccountInactive(): bool
    {
        return ! $this->isAccountActive();
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isTeacher(): bool  { return $this->role === 'teacher'; }
    public function isStudent(): bool  { return $this->role === 'student'; }
    public function isParent(): bool   { return $this->role === 'parent'; }

    public function hasRole(string|array ...$roles): bool
    {
        $flattened = collect($roles)->flatten()->all();
        return in_array($this->role, $flattened);
    }

    public function getDashboardRoute(): string
    {
        return match($this->role) {
            'admin'   => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent'  => 'parent.dashboard',
            default   => 'student.dashboard',
        };
    }

    public function teacherProfile() { return $this->hasOne(Teacher::class); }
    public function student()        { return $this->hasOne(Student::class); }  // alias for studentProfile
    public function studentProfile() { return $this->hasOne(Student::class); }
    /** Legacy: children linked via parent_user_id — kept for backward compatibility */
    public function children()       { return $this->hasMany(Student::class, 'parent_user_id'); }
    /** Pivot-based: all students linked via parent_student pivot */
    public function studentChildren()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_user_id', 'student_id')
                    ->withPivot('relationship', 'is_primary', 'status')
                    ->withTimestamps();
    }
    /**
     * Authorized children for billing: students linked via parent_student pivot
     * (regardless of is_primary). Used by billing controllers to scope invoice access.
     */
    public function authorizedChildren()
    {
        return $this->studentChildren();
    }
    public function payments()       { return $this->hasMany(Payment::class); }
    public function blogPosts()      { return $this->hasMany(BlogPost::class); }
    public function sentMessages()   { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(Message::class, 'receiver_id'); }
}
