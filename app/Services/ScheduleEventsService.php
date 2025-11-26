<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service to generate events from schedules, similar to Yii project's API endpoint
 * 
 * This service mimics the behavior of the Yii project's /event API endpoint
 * which generates events from recurring schedules for a given date.
 */
class ScheduleEventsService
{
    /**
     * Get events for a specific date, generated from schedules and actual events
     * Similar to Yii's /event API endpoint
     * 
     * @param int $orgId Organization ID
     * @param string|null $date Date string (Y-m-d format) or null for today
     * @param int|null $venue Venue filter (Schedule::VENUE_GEO, VENUE_TELE, or VENUE_ALL)
     * @return array Array with events, dateString, datePrev, dateNext
     */
    public function getEventsForDate($orgId, $date = null, $venue = Schedule::VENUE_ALL)
    {
        // Handle 'now' date parameter (same as Yii)
        if ($date === 'now' || $date === null) {
            $date = now()->format('Y-m-d');
        }
        
        $currentDate = Carbon::parse($date);
        $startOfDay = $currentDate->copy()->startOfDay();
        $endOfDay = $currentDate->copy()->endOfDay();
        
        // Get actual events from event table for this date
        $actualEvents = Event::forOrg($orgId)
            ->active()
            ->notDeleted()
            ->whereBetween('startDateTimeLoc', [$startOfDay, $endOfDay])
            ->where('isCanceled', false)
            ->with(['program', 'orgLocation', 'instructors'])
            ->orderBy('startDateTimeLoc', 'asc')
            ->get();
        
        // Get schedules and generate events for this date
        $scheduleQuery = Schedule::forOrg($orgId)
            ->active()
            ->notDeleted();
        
        // Filter by venue if specified
        if ($venue !== Schedule::VENUE_ALL) {
            $scheduleQuery->byVenue($venue);
        }
        
        $schedules = $scheduleQuery->with(['program', 'orgLocation', 'instructors'])
            ->get();
        
        // Generate events from schedules
        $generatedEvents = collect();
        foreach ($schedules as $schedule) {
            $event = $schedule->generateEventForDate($currentDate);
            if ($event) {
                $generatedEvents->push($event);
            }
        }
        
        // Merge actual events and generated events
        // Convert generated events to match Event model structure
        $allEvents = $actualEvents->map(function ($event) {
            return $this->formatEvent($event);
        })->merge($generatedEvents->map(function ($event) {
            return $this->formatGeneratedEvent($event);
        }));
        
        // Sort by start time
        $allEvents = $allEvents->sortBy(function ($event) {
            return $event->startDateTime ?? $event->startDateTimeLoc ?? null;
        })->values();
        
        // Format date strings for navigation
        $dateString = $currentDate->format('l, F j, Y'); // "Wednesday, November 26, 2025"
        $datePrev = $currentDate->copy()->subDay()->format('Y-m-d');
        $dateNext = $currentDate->copy()->addDay()->format('Y-m-d');
        
        return [
            'events' => $allEvents,
            'dateString' => $dateString,
            'datePrev' => $datePrev,
            'dateNext' => $dateNext,
            'date' => $date
        ];
    }
    
    /**
     * Format an Event model for display
     * 
     * @param Event $event
     * @return object
     */
    protected function formatEvent(Event $event)
    {
        $formatted = new \stdClass();
        $formatted->id = $event->id;
        $formatted->uuid = $event->uuid;
        $formatted->schedule_id = $event->schedule_id;
        $formatted->name = $event->name;
        $formatted->note = $event->note;
        $formatted->startDateTime = $event->startDateTimeLoc;
        $formatted->endDateTime = $event->endDateTimeLoc;
        $formatted->startDateTimeLoc = $event->startDateTimeLoc;
        $formatted->endDateTimeLoc = $event->endDateTimeLoc;
        $formatted->capacity = $event->capacity;
        $formatted->program = $event->program;
        $formatted->orgLocation = $event->orgLocation;
        // Always set instructor property, even if null
        $formatted->instructor = $event->instructors()->first() ?? null;
        $formatted->instructors = $event->instructors;
        $formatted->formatted_class_name = $event->formatted_class_name;
        $formatted->longName = $this->getLongName($event);
        $formatted->timesText = $this->getTimesText($event);
        $formatted->isFromSchedule = false;
        $formatted->isActualEvent = true;
        
        return $formatted;
    }
    
    /**
     * Format a generated event from schedule
     * 
     * @param object $event Generated event object
     * @return object
     */
    protected function formatGeneratedEvent($event)
    {
        $formatted = new \stdClass();
        $formatted->id = $event->id;
        $formatted->uuid = null;
        $formatted->schedule_id = $event->schedule_id;
        $formatted->name = $event->name;
        $formatted->note = $event->note;
        $formatted->startDateTime = $event->startDateTime;
        $formatted->endDateTime = $event->endDateTime;
        $formatted->startDateTimeLoc = $event->startDateTime;
        $formatted->endDateTimeLoc = $event->endDateTime;
        $formatted->capacity = $event->capacity;
        $formatted->program = $event->program;
        $formatted->orgLocation = $event->orgLocation;
        // Always set instructor property, even if null
        $formatted->instructor = $event->instructor ?? null;
        $formatted->instructors = $event->instructor ? collect([$event->instructor]) : collect();
        $formatted->formatted_class_name = $this->getFormattedClassName($event);
        $formatted->longName = $this->getLongNameFromGenerated($event);
        $formatted->timesText = $this->getTimesTextFromGenerated($event);
        $formatted->isFromSchedule = true;
        $formatted->isActualEvent = false;
        
        return $formatted;
    }
    
    /**
     * Get formatted class name (time - program name)
     */
    protected function getFormattedClassName($event)
    {
        $startTime = '';
        if (isset($event->startDateTime)) {
            $startTime = $event->startDateTime->format('g:i A');
        }
        
        $programName = $event->program?->name ?? 'Unknown Program';
        
        return $startTime ? "{$startTime} - {$programName}" : $programName;
    }
    
    /**
     * Get long name for Event model (similar to Yii's getLongName())
     */
    protected function getLongName(Event $event)
    {
        $startTime = $event->startDateTimeLoc->format('g:i A');
        $programName = $event->program?->name ?? '';
        
        if (empty($event->name)) {
            return "{$startTime} - {$programName}";
        } else {
            return "{$startTime} - {$event->name} - {$programName}";
        }
    }
    
    /**
     * Get long name for generated event
     */
    protected function getLongNameFromGenerated($event)
    {
        $startTime = $event->startDateTime->format('g:i A');
        $programName = $event->program?->name ?? '';
        
        if (empty($event->name)) {
            return "{$startTime} - {$programName}";
        } else {
            return "{$startTime} - {$event->name} - {$programName}";
        }
    }
    
    /**
     * Get times text for Event model
     */
    protected function getTimesText(Event $event)
    {
        $start = $event->startDateTimeLoc->format('g:i A');
        $end = $event->endDateTimeLoc->format('g:i A');
        return "{$start} - {$end}";
    }
    
    /**
     * Get times text for generated event
     */
    protected function getTimesTextFromGenerated($event)
    {
        $start = $event->startDateTime->format('g:i A');
        $end = $event->endDateTime->format('g:i A');
        return "{$start} - {$end}";
    }
}

