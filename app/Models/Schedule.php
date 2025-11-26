<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory, Tenantable, SoftDeletes;

    protected $table = 'schedule';

    /**
     * Venue constants (same as Yii project)
     */
    const VENUE_GEO = 1;  // In-person/geographic location
    const VENUE_TELE = 2; // Teleconferencing/virtual
    const VENUE_ALL = 99; // All venues

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'org_id',
        'orgLocation_id',
        'program_id',
        'room_id',
        'name',
        'note',
        'localStartTime',
        'localEndTime',
        'timezone_long',
        'timezone_offset',
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'sat',
        'sun',
        'capacityMon',
        'capacityTue',
        'capacityWed',
        'capacityThu',
        'capacityFri',
        'capacitySat',
        'capacitySun',
        'venue',
        'status',
        'isActive',
        'isDeleted',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'org_id' => 'integer',
        'orgLocation_id' => 'integer',
        'program_id' => 'integer',
        'room_id' => 'integer',
        'mon' => 'boolean',
        'tue' => 'boolean',
        'wed' => 'boolean',
        'thu' => 'boolean',
        'fri' => 'boolean',
        'sat' => 'boolean',
        'sun' => 'boolean',
        'capacityMon' => 'integer',
        'capacityTue' => 'integer',
        'capacityWed' => 'integer',
        'capacityThu' => 'integer',
        'capacityFri' => 'integer',
        'capacitySat' => 'integer',
        'capacitySun' => 'integer',
        'venue' => 'integer',
        'status' => 'integer',
        'isActive' => 'boolean',
        'isDeleted' => 'boolean',
    ];

    /**
     * Indicates if the model should use timestamps.
     */
    public $timestamps = false;

    /**
     * Get the organization that owns this schedule.
     */
    public function org()
    {
        return $this->belongsTo(Org::class, 'org_id');
    }

    /**
     * Get the program associated with this schedule.
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Get the organization location where this schedule takes place.
     */
    public function orgLocation()
    {
        return $this->belongsTo(OrgLocation::class, 'orgLocation_id');
    }

    /**
     * Get the instructors assigned to this schedule.
     * Note: scheduleAssignment table doesn't have a role column,
     * so we just filter by isDeleted = false
     */
    public function instructors()
    {
        return $this->belongsToMany(OrgUser::class, 'scheduleAssignment', 'schedule_id', 'orgUser_id')
                    ->wherePivot('isDeleted', false);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrg($query, $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to exclude soft deleted records.
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('isDeleted', false);
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', true)->where('status', 1);
    }

    /**
     * Scope to filter by venue.
     */
    public function scopeByVenue($query, $venue)
    {
        if ($venue == self::VENUE_ALL) {
            return $query;
        }
        return $query->where('venue', $venue);
    }

    /**
     * Check if schedule runs on a specific day of week.
     * 
     * @param string $dayOfWeek Lowercase day name (mon, tue, wed, etc.)
     * @return bool
     */
    public function runsOnDay($dayOfWeek)
    {
        $dayField = strtolower($dayOfWeek);
        return !empty($this->$dayField);
    }

    /**
     * Get capacity for a specific day of week.
     * 
     * @param string $dayOfWeek Lowercase day name (mon, tue, wed, etc.)
     * @return int|null
     */
    public function getCapacityForDay($dayOfWeek)
    {
        $dayField = 'capacity' . ucfirst(strtolower($dayOfWeek));
        return $this->$dayField ?? null;
    }

    /**
     * Generate events from this schedule for a specific date.
     * 
     * @param Carbon $date The date to generate events for
     * @return \stdClass|null Event object or null if schedule doesn't run on this day
     */
    public function generateEventForDate(Carbon $date)
    {
        $dayOfWeek = strtolower($date->format('D')); // mon, tue, wed, etc.
        
        // Check if schedule runs on this day
        if (!$this->runsOnDay($dayOfWeek)) {
            return null;
        }

        // Check if times are set
        if (!$this->localStartTime || !$this->localEndTime) {
            return null;
        }

        // Create event object
        $event = new \stdClass();
        $event->id = $this->id . '_' . $date->format('Y-m-d');
        $event->schedule_id = $this->id;
        $event->name = $this->name;
        $event->note = $this->note;
        
        // Parse time and create datetime
        $event->startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $this->localStartTime);
        $event->endDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $this->localEndTime);
        
        // Get capacity for this day
        $event->capacity = $this->getCapacityForDay($dayOfWeek);
        
        // Load relationships
        $event->program = $this->program;
        $event->orgLocation = $this->orgLocation;
        
        // Load instructor (always set, even if null)
        $instructor = $this->instructors()->first();
        if ($instructor) {
            $event->instructor = (object)[
                'id' => $instructor->id,
                'fullName' => trim(($instructor->fname ?? '') . ' ' . ($instructor->lname ?? '')),
                'email' => $instructor->email ?? '',
            ];
        } else {
            $event->instructor = null;
        }
        
        return $event;
    }
}

