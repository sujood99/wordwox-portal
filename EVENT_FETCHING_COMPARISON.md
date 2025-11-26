# Event Fetching: Yii vs Laravel Comparison

## Overview
This document compares how events are fetched in the Yii project (`wodworx-customer-portal-yii`) versus how to fetch them directly from the database in the Laravel project (`wodworx-portal-laravel`).

---

## Yii Project Implementation

### How It Works
The Yii project uses an **API client** to fetch events from a backend API endpoint.

**Location**: `/Users/macbook1993/wodworx-customer-portal-yii/frontend/controllers/EventController.php`

### Key Code
```php
// EventController.php - actionIndex()
public function actionIndex($view = null, $venue = null, $date = null)
{
    if($date == 'now') {
        $date = null;
    }
    
    // Makes API call to /event endpoint
    $response = Yii::$app->apiClient->request('/event', 'GET', [
        'date' => $date, 
        'venue' => $venue
    ]);
    
    if($response->statusCode == 200) {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $response->data['data']['events']
        ]);
        $dataProvider->pagination = false;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateString' => $response->data['data']['dateString'],
            'dateNext' => $response->data['data']['dateNext'],
            'datePrev' => $response->data['data']['datePrev']
        ]);
    }
}
```

### API Client Configuration
**Location**: `/Users/macbook1993/wodworx-customer-portal-yii/common/components/APIClient.php`

The API client makes HTTP requests to a backend API with:
- Organization API key header
- User authentication token
- JSON request/response format

---

## Laravel Project Implementation

### Direct Database Query Approach

In Laravel, you can fetch events **directly from the database** using the `Event` model without needing an API call.

### Basic Event Query Examples

#### 1. Get Events for a Specific Date
```php
use App\Models\Event;
use Carbon\Carbon;

// Get events for today
$date = request('date', now()->format('Y-m-d'));
$startOfDay = Carbon::parse($date)->startOfDay();
$endOfDay = Carbon::parse($date)->endOfDay();

$events = Event::where('org_id', $orgId)
    ->whereBetween('startDateTimeLoc', [$startOfDay, $endOfDay])
    ->where('isActive', true)
    ->where('isCanceled', false)
    ->where('isDeleted', false)
    ->with(['program', 'orgLocation', 'instructors'])
    ->orderBy('startDateTimeLoc', 'asc')
    ->get();
```

#### 2. Get Events for a Date Range (Week/Month)
```php
use App\Models\Event;
use Carbon\Carbon;

// Get events for current week
$startOfWeek = Carbon::now()->startOfWeek();
$endOfWeek = Carbon::now()->endOfWeek();

$events = Event::where('org_id', $orgId)
    ->whereBetween('startDateTimeLoc', [$startOfWeek, $endOfWeek])
    ->where('isActive', true)
    ->where('isCanceled', false)
    ->where('isDeleted', false)
    ->with(['program', 'orgLocation', 'instructors'])
    ->orderBy('startDateTimeLoc', 'asc')
    ->get();
```

#### 3. Get Events with Navigation (Previous/Next Date)
```php
use App\Models\Event;
use Carbon\Carbon;

$date = request('date', now()->format('Y-m-d'));
$currentDate = Carbon::parse($date);
$datePrev = $currentDate->copy()->subDay()->format('Y-m-d');
$dateNext = $currentDate->copy()->addDay()->format('Y-m-d');
$dateString = $currentDate->format('l, F j, Y'); // e.g., "Wednesday, November 26, 2025"

$startOfDay = $currentDate->copy()->startOfDay();
$endOfDay = $currentDate->copy()->endOfDay();

$events = Event::where('org_id', $orgId)
    ->whereBetween('startDateTimeLoc', [$startOfDay, $endOfDay])
    ->where('isActive', true)
    ->where('isCanceled', false)
    ->where('isDeleted', false)
    ->with(['program', 'orgLocation', 'instructors'])
    ->orderBy('startDateTimeLoc', 'asc')
    ->get();

// Return data similar to Yii API response
return [
    'events' => $events,
    'dateString' => $dateString,
    'datePrev' => $datePrev,
    'dateNext' => $dateNext
];
```

#### 4. Get Events Filtered by Venue/Location
```php
use App\Models\Event;
use Carbon\Carbon;

$date = request('date', now()->format('Y-m-d'));
$venue = request('venue'); // orgLocation_id

$startOfDay = Carbon::parse($date)->startOfDay();
$endOfDay = Carbon::parse($date)->endOfDay();

$query = Event::where('org_id', $orgId)
    ->whereBetween('startDateTimeLoc', [$startOfDay, $endOfDay])
    ->where('isActive', true)
    ->where('isCanceled', false)
    ->where('isDeleted', false);

// Filter by venue if provided
if ($venue) {
    $query->where('orgLocation_id', $venue);
}

$events = $query->with(['program', 'orgLocation', 'instructors'])
    ->orderBy('startDateTimeLoc', 'asc')
    ->get();
```

#### 5. Using Model Scopes (Recommended)
```php
use App\Models\Event;
use Carbon\Carbon;

$date = request('date', now()->format('Y-m-d'));
$startOfDay = Carbon::parse($date)->startOfDay();
$endOfDay = Carbon::parse($date)->endOfDay();

$events = Event::forOrg($orgId)
    ->active()
    ->notDeleted()
    ->whereBetween('startDateTimeLoc', [$startOfDay, $endOfDay])
    ->where('isCanceled', false)
    ->with(['program', 'orgLocation', 'instructors'])
    ->orderBy('startDateTimeLoc', 'asc')
    ->get();
```

---

## Complete Controller Example

Here's a complete controller method similar to the Yii `actionIndex()`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display events for a specific date
     * Similar to Yii's EventController::actionIndex()
     */
    public function index(Request $request)
    {
        $orgId = auth()->user()->org_id ?? $request->user()->org_id ?? 8; // Get from auth or default
        $date = $request->input('date', now()->format('Y-m-d'));
        $venue = $request->input('venue'); // orgLocation_id
        
        // Handle 'now' date parameter
        if ($date === 'now') {
            $date = now()->format('Y-m-d');
        }
        
        $currentDate = Carbon::parse($date);
        $startOfDay = $currentDate->copy()->startOfDay();
        $endOfDay = $currentDate->copy()->endOfDay();
        
        // Build query
        $query = Event::forOrg($orgId)
            ->active()
            ->notDeleted()
            ->whereBetween('startDateTimeLoc', [$startOfDay, $endOfDay])
            ->where('isCanceled', false);
        
        // Filter by venue if provided
        if ($venue) {
            $query->where('orgLocation_id', $venue);
        }
        
        // Get events with relationships
        $events = $query->with(['program', 'orgLocation', 'instructors'])
            ->orderBy('startDateTimeLoc', 'asc')
            ->get();
        
        // Format date strings for navigation
        $dateString = $currentDate->format('l, F j, Y'); // "Wednesday, November 26, 2025"
        $datePrev = $currentDate->copy()->subDay()->format('Y-m-d');
        $dateNext = $currentDate->copy()->addDay()->format('Y-m-d');
        
        return view('events.index', [
            'events' => $events,
            'dateString' => $dateString,
            'datePrev' => $datePrev,
            'dateNext' => $dateNext,
            'currentDate' => $date
        ]);
    }
}
```

---

## Key Differences

| Aspect | Yii Project | Laravel Project |
|--------|-------------|-----------------|
| **Data Source** | API endpoint (`/event`) | Direct database query |
| **Authentication** | API key + Bearer token | Laravel auth + Tenantable trait |
| **Filtering** | API parameters (`date`, `venue`) | Eloquent query builder |
| **Relationships** | Included in API response | Eager loading with `with()` |
| **Date Handling** | API handles timezone | Carbon for date manipulation |

---

## Important Notes

### 1. Multi-Tenancy
The Laravel `Event` model uses the `Tenantable` trait, which automatically filters by `org_id`. Always ensure you're working within the correct organization context.

### 2. Timezone Handling
- Use `startDateTimeLoc` for display (local timezone)
- Use `startDateTime` for system calculations (UTC)
- The model casts these as Carbon datetime objects

### 3. Event Status
- `isActive`: Whether the event is active
- `isCanceled`: Whether the event is canceled
- `isDeleted`: Soft delete flag (use `notDeleted()` scope)

### 4. Relationships Available
- `program`: The program/class type (e.g., "CrossFit", "Yoga")
- `orgLocation`: The location/venue
- `instructors`: All instructors for the event
- `instructor`: Single instructor (first one)

---

## Example: Creating a Route and View

### Route (routes/web.php)
```php
Route::get('event', [EventController::class, 'index'])->name('event.index');
```

### View (resources/views/events/index.blade.php)
```blade
<div class="event-index">
    <h3>Schedule</h3>
    <hr />
    
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h3>{{ $dateString }}</h3>
                </div>
                <div class="col-lg-12 text-center">
                    <a href="{{ route('event.index', ['date' => $datePrev]) }}">← Previous</a>
                    |
                    <a href="{{ route('event.index', ['date' => 'now']) }}">Today</a>
                    |
                    <a href="{{ route('event.index', ['date' => $dateNext]) }}">Next →</a>
                </div>
            </div>
            <hr/>
            
            @forelse($events as $event)
                <div class="event-item">
                    <h5>{{ $event->formatted_class_name }}</h5>
                    <div>
                        {{ $event->startDateTimeLoc->format('g:i A') }} - 
                        {{ $event->endDateTimeLoc->format('g:i A') }}
                    </div>
                    @if($event->program)
                        <div>Program: {{ $event->program->name }}</div>
                    @endif
                    @if($event->orgLocation)
                        <div>Location: {{ $event->orgLocation->name }}</div>
                    @endif
                </div>
            @empty
                <p>No events scheduled for this date.</p>
            @endforelse
            
            <hr/>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{ route('event.index', ['date' => $datePrev]) }}">← Previous</a>
                    |
                    <a href="{{ route('event.index', ['date' => 'now']) }}">Today</a>
                    |
                    <a href="{{ route('event.index', ['date' => $dateNext]) }}">Next →</a>
                </div>
                <div class="col-lg-12 text-center">
                    <h3>{{ $dateString }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

---

## Fetching Events from Schedules (Same as Yii Project)

The Yii project's `/event` API endpoint generates events from **recurring schedules** for a given date. In Laravel, we can do the same using the `Schedule` model and `ScheduleEventsService`.

### Schedule Model

**Location**: `app/Models/Schedule.php`

The `Schedule` model represents recurring class schedules with:
- Day of week flags (`mon`, `tue`, `wed`, etc.)
- Start/end times (`localStartTime`, `localEndTime`)
- Capacity per day (`capacityMon`, `capacityTue`, etc.)
- Venue type (`venue`: GEO, TELE, or ALL)

### Using ScheduleEventsService

**Location**: `app/Services/ScheduleEventsService.php`

This service mimics the Yii project's `/event` API endpoint behavior:

```php
use App\Services\ScheduleEventsService;
use App\Models\Schedule;

$service = new ScheduleEventsService();
$orgId = 8; // Your organization ID
$date = '2025-11-26'; // or null for today
$venue = Schedule::VENUE_ALL; // or VENUE_GEO, VENUE_TELE

$result = $service->getEventsForDate($orgId, $date, $venue);

// Returns:
// [
//     'events' => Collection of events (from both event table and generated from schedules),
//     'dateString' => "Wednesday, November 26, 2025",
//     'datePrev' => "2025-11-25",
//     'dateNext' => "2025-11-27",
//     'date' => "2025-11-26"
// ]
```

### Complete Controller Example with Schedules

```php
<?php

namespace App\Http\Controllers;

use App\Services\ScheduleEventsService;
use App\Models\Schedule;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $scheduleEventsService;
    
    public function __construct(ScheduleEventsService $scheduleEventsService)
    {
        $this->scheduleEventsService = $scheduleEventsService;
    }
    
    /**
     * Display events for a specific date
     * Same behavior as Yii's EventController::actionIndex()
     * and ApiController::actionEvents()
     */
    public function index(Request $request)
    {
        $orgId = auth()->user()->org_id ?? $request->user()->org_id ?? 8;
        $date = $request->input('date', 'now');
        $venue = $request->input('venue', Schedule::VENUE_ALL);
        
        // Get events (from both event table and schedules)
        $result = $this->scheduleEventsService->getEventsForDate($orgId, $date, $venue);
        
        return view('events.index', [
            'events' => $result['events'],
            'dateString' => $result['dateString'],
            'datePrev' => $result['datePrev'],
            'dateNext' => $result['dateNext'],
            'currentDate' => $result['date']
        ]);
    }
}
```

### How It Works

1. **Fetches Actual Events**: Gets events from the `event` table for the specified date
2. **Generates from Schedules**: Gets active schedules and generates events for the date based on:
   - Day of week (mon, tue, wed, etc.)
   - Schedule's start/end times
   - Schedule's capacity for that day
3. **Merges Results**: Combines actual events and generated events
4. **Sorts by Time**: Orders all events by start time
5. **Returns Formatted Data**: Same structure as Yii API response

### Schedule Model Methods

```php
use App\Models\Schedule;
use Carbon\Carbon;

$schedule = Schedule::find(1);

// Check if schedule runs on a specific day
$schedule->runsOnDay('mon'); // true/false

// Get capacity for a specific day
$schedule->getCapacityForDay('mon'); // integer or null

// Generate an event for a specific date
$date = Carbon::parse('2025-11-26');
$event = $schedule->generateEventForDate($date);
// Returns event object or null if schedule doesn't run on that day
```

### Venue Filtering

```php
use App\Models\Schedule;

// Filter schedules by venue type
$schedules = Schedule::forOrg($orgId)
    ->active()
    ->byVenue(Schedule::VENUE_GEO)  // In-person only
    ->get();

$schedules = Schedule::forOrg($orgId)
    ->active()
    ->byVenue(Schedule::VENUE_TELE) // Virtual only
    ->get();

$schedules = Schedule::forOrg($orgId)
    ->active()
    ->byVenue(Schedule::VENUE_ALL)  // All venues (default)
    ->get();
```

---

## Summary

**Yii Project**: 
- Fetches events via API call to `/event` endpoint
- API generates events from schedules for the requested date
- Returns formatted events with date navigation

**Laravel Project**: 
- Fetches events directly from database using Eloquent
- Uses `ScheduleEventsService` to generate events from schedules (same as Yii API)
- Combines actual events and schedule-generated events
- Returns same structure as Yii API response

Both approaches work identically - the Laravel implementation replicates the Yii API behavior but queries the database directly instead of making HTTP requests.

