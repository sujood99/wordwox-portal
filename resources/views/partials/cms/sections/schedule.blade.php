{{-- Schedule Section Partial --}}
@if($isFitness)
    @php
        $scheduleSettings = is_string($section->settings) ? json_decode($section->settings, true) : ($section->settings ?? []);
        $cardTitleFontSize = $scheduleSettings['card_title_font_size'] ?? '';
        $cardTitleStyle = '';
        if (!empty($cardTitleFontSize)) {
            $numericValue = is_numeric($cardTitleFontSize) ? (float) $cardTitleFontSize : (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', $cardTitleFontSize, $matches) ? (float) $matches[1] : null);
            if ($numericValue && $numericValue >= 1) {
                $cardTitleStyle = 'font-size: ' . $numericValue . 'px;';
            }
        }
    @endphp
    <div class="container my-4 my-md-5 schedule-section">
        @if($section->title)
        <div class="text-center mb-4 mb-md-5">
            <h2 class="section-heading">{{ $section->title }}</h2>
            @if($section->subtitle)
            <p class="text-muted schedule-subtitle">{{ $section->subtitle }}</p>
            @endif
        </div>
        @endif

        @if($section->content)
        <div class="text-center mb-4 mb-md-5 schedule-content">{!! $section->content !!}</div>
        @endif

        {{-- Weekly Timetable View --}}
        @php
            // Collect all events across all days
            $allEvents = collect();
            foreach ($showDays as $day) {
                if (isset($scheduleByDay[$day])) {
                    $allEvents = $allEvents->merge($scheduleByDay[$day]);
                }
            }
            
            // Get unique time slots from all events
            $timeSlots = $allEvents->flatMap(function($event) {
                $slots = [];
                $start = $event->startDateTime->copy()->startOfHour();
                $end = $event->endDateTime->copy();
                
                while ($start < $end) {
                    $slots[] = $start->format('g:i A');
                    $start->addHour();
                }
                return $slots;
            })->unique()->sort()->values();
            
            // If no events, show empty state
            if ($allEvents->isEmpty() || $timeSlots->isEmpty()) {
                $hasSchedule = false;
            } else {
                $hasSchedule = true;
            }
            
            // Color palette for events
            $eventColors = [
                '#73c6cd', '#e3b26d', '#e4926e', '#62aed6', 
                '#cd7399', '#b78bcf', '#8aa8d4', '#96ceb4'
            ];
        @endphp

        @if($hasSchedule)
            {{-- Desktop Table View --}}
            <div class="schedule-table-wrapper d-none d-lg-block">
                <div class="table-responsive">
                    <table class="schedule-table table">
                        <thead>
                            <tr>
                                <th class="time-column">Time</th>
                                @foreach($showDays as $day)
                                    <th class="day-column">{{ ucfirst($day) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $timeIndex => $timeSlot)
                                <tr class="{{ $timeIndex % 2 === 0 ? 'row-even' : 'row-odd' }}">
                                    <td class="time-cell">{{ $timeSlot }}</td>
                                    @foreach($showDays as $dayIndex => $day)
                                        @php
                                            // Find events for this day and time
                                            $dayEvents = isset($scheduleByDay[$day]) ? $scheduleByDay[$day] : collect();
                                            $cellEvent = $dayEvents->first(function($event) use ($timeSlot) {
                                                $slotTime = \Carbon\Carbon::parse($timeSlot);
                                                return $event->startDateTime->format('g:i A') === $timeSlot;
                                            });
                                            
                                            // Check if this cell is part of a previous event's rowspan
                                            $isSpanned = false;
                                            if (!$cellEvent) {
                                                foreach ($dayEvents as $ev) {
                                                    $eventStart = $ev->startDateTime;
                                                    $eventEnd = $ev->endDateTime;
                                                    $currentSlot = \Carbon\Carbon::parse($timeSlot);
                                                    if ($currentSlot->gt($eventStart) && $currentSlot->lt($eventEnd)) {
                                                        $isSpanned = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @if($isSpanned)
                                            {{-- This cell is part of a rowspan, skip it --}}
                                        @elseif($cellEvent)
                                            @php
                                                // Calculate rowspan based on duration
                                                $duration = $cellEvent->startDateTime->diffInHours($cellEvent->endDateTime);
                                                $rowspan = max(1, $duration);
                                                $colorIndex = crc32($cellEvent->name) % count($eventColors);
                                                $bgColor = $eventColors[$colorIndex];
                                            @endphp
                                            <td class="event-cell" rowspan="{{ $rowspan }}" style="background-color: {{ $bgColor }};">
                                                <div class="event-content">
                                                    <div class="event-name" style="{{ $cardTitleStyle }}">{{ $cellEvent->name }}</div>
                                                    @if($cellEvent->program)
                                                        <div class="event-program">{{ $cellEvent->program->name }}</div>
                                                    @endif
                                                    <div class="event-time">
                                                        {{ $cellEvent->startDateTime->format('g:i A') }} - {{ $cellEvent->endDateTime->format('g:i A') }}
                                                    </div>
                                                    @if($showInstructor && $cellEvent->instructor)
                                                        <div class="event-instructor">{{ $cellEvent->instructor->fullName }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                        @else
                                            <td class="empty-cell"></td>
                                        @endif
                                    @endforeach
                                </tr>
                    @endforeach
                        </tbody>
                    </table>
                </div>
        </div>

            {{-- Mobile List View --}}
            <div class="schedule-list-wrapper d-lg-none">
                @foreach($showDays as $day)
                    <div class="day-section mb-4">
                        <h3 class="day-heading">{{ ucfirst($day) }}</h3>
                    @if(isset($scheduleByDay[$day]) && $scheduleByDay[$day]->count() > 0)
                            <ul class="event-list">
                            @foreach($scheduleByDay[$day] as $event)
                                    @php
                                        $colorIndex = crc32($event->name) % count($eventColors);
                                        $bgColor = $eventColors[$colorIndex];
                                    @endphp
                                    <li class="event-item" style="border-left-color: {{ $bgColor }};">
                                        <div class="event-item-content">
                                            <div class="event-item-name" style="{{ $cardTitleStyle }}">{{ $event->name }}</div>
                                            @if($event->program)
                                                <div class="event-item-program">{{ $event->program->name }}</div>
                                            @endif
                                            <div class="event-item-time">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $event->startDateTime->format('g:i A') }} - {{ $event->endDateTime->format('g:i A') }}
                                            </div>
                                            @if($showInstructor && $event->instructor)
                                                <div class="event-item-instructor">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $event->instructor->fullName }}
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No classes scheduled</p>
                        @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 py-md-5 schedule-empty">
                            <i class="fas fa-calendar-alt schedule-empty-icon text-muted mb-3"></i>
                <p class="text-muted schedule-empty-text">No classes scheduled this week.</p>
                        </div>
                    @endif
    </div>

    <style>
        /* Responsive Schedule Section */
        .schedule-section {
            padding: 40px 15px;
        }
        
        .schedule-subtitle {
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .schedule-content {
            font-size: 0.95rem;
            line-height: 1.7;
        }
        
        /* Table Layout Styles */
        .schedule-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            background-color: #fff;
        }
        
        .schedule-table thead th {
            background-color: #ffffff;
            color: #2c3e50;
            font-weight: 600;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 0.9rem;
        }
        
        .schedule-table tbody td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            text-align: center;
            font-size: 0.85rem;
        }
        
        .schedule-table .time-cell {
            font-weight: 500;
            background-color: #f8f9fa;
            white-space: nowrap;
            text-align: center;
            min-width: 80px;
        }
        
        .schedule-table .row-even {
            background-color: #ebecf2;
        }
        
        .schedule-table .row-odd {
            background-color: #ffffff;
        }
        
        .schedule-table .empty-cell {
            background-color: transparent;
        }
        
        .schedule-table .event-cell {
            color: #ffffff;
            cursor: pointer;
            transition: opacity 0.3s ease;
            padding: 10px;
        }
        
        .schedule-table .event-cell:hover {
            opacity: 0.85;
        }
        
        .schedule-table .event-content {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .schedule-table .event-name {
            font-weight: 600;
            font-size: 0.95rem;
            line-height: 1.2;
        }
        
        .schedule-table .event-program {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .schedule-table .event-time {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        
        .schedule-table .event-instructor {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        
        /* Mobile List View */
        .schedule-list-wrapper {
            width: 100%;
        }
        
        .schedule-list-wrapper .day-section {
            margin-bottom: 2rem;
        }
        
        .schedule-list-wrapper .day-heading {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--fitness-primary, #ff6b6b);
        }
        
        .schedule-list-wrapper .event-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .schedule-list-wrapper .event-item {
            padding: 12px 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .schedule-list-wrapper .event-item-content {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .schedule-list-wrapper .event-item-name {
            font-weight: 600;
            font-size: 1rem;
            color: #2c3e50;
        }
        
        .schedule-list-wrapper .event-item-program {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .schedule-list-wrapper .event-item-time {
            font-size: 0.85rem;
            color: #495057;
        }
        
        .schedule-list-wrapper .event-item-instructor {
            font-size: 0.85rem;
            color: #495057;
        }
        
        /* Empty State */
        .schedule-empty-icon {
            font-size: 2.5rem;
        }
        
        .schedule-empty-text {
            font-size: 0.9rem;
        }
        
        /* Responsive adjustments */
        @media (min-width: 576px) {
            .schedule-section {
                padding: 50px 20px;
            }
            .schedule-subtitle {
                font-size: 1rem;
            }
            .schedule-content {
                font-size: 1rem;
            }
            .schedule-empty-icon {
                font-size: 3rem;
            }
            .schedule-empty-text {
                font-size: 1rem;
            }
        }
        
        @media (min-width: 768px) {
            .schedule-section {
                padding: 60px 25px;
            }
            .schedule-subtitle {
                font-size: 1.125rem;
            }
            .schedule-content {
                font-size: 1.125rem;
            }
            .schedule-list-wrapper .day-heading {
                font-size: 1.5rem;
            }
        }
        
        @media (min-width: 992px) {
            .schedule-section {
                padding: 80px 29px;
            }
            .schedule-table thead th {
                padding: 15px 10px;
                font-size: 1rem;
            }
            .schedule-table tbody td {
                padding: 12px;
                font-size: 0.9rem;
            }
            .schedule-table .event-name {
                font-size: 1rem;
            }
            .schedule-table .event-program,
            .schedule-table .event-time,
            .schedule-table .event-instructor {
                font-size: 0.85rem;
            }
        }
    </style>

@elseif($isMeditative)
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center pb-5 mb-3">
                <div class="col-md-7 heading-section text-center ftco-animate">
                    @if($section->title)
                    <h2>{{ $section->title }}</h2>
                    @endif
                    @if($section->subtitle)
                    <span class="subheading">{{ $section->subtitle }}</span>
                    @endif
                    @if($section->content)
                    <div class="mt-3">{!! $section->content !!}</div>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    @if($scheduleByDay && collect($scheduleByDay)->flatten()->count() > 0)
                        {{-- Dynamic Schedule Cards --}}
                        <div class="row">
                            @foreach($showDays as $day)
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white text-center">
                                            <h4 class="mb-0">{{ ucfirst($day) }}</h4>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($scheduleByDay[$day]) && $scheduleByDay[$day]->count() > 0)
                                                @foreach($scheduleByDay[$day] as $event)
                                                    <div class="mb-3 p-3 border-left border-primary">
                                                        <h5 class="text-primary mb-1" style="{{ $cardTitleStyle }}">{{ $event->name }}</h5>
                                                        <p class="text-muted mb-1">
                                                            <i class="icon-clock-o mr-2"></i>
                                                            {{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}
                                                        </p>
                                                        @if($showInstructor && $event->instructor)
                                                            <p class="text-muted mb-1">
                                                                <i class="icon-user mr-2"></i>
                                                                {{ $event->instructor->fullName }}
                                                            </p>
                                                        @endif
                                                        @if($event->orgLocation)
                                                            <span class="address">{{ $event->orgLocation->name ?? 'Main Studio' }}</span>
                                                        @endif
                                                        @if($showCapacity && $event->capacity)
                                                            @php
                                                                $bookedCount = \App\Models\EventSubscriber::where('event_id', $event->id)
                                                                    ->where('booking_status', \App\Models\EventSubscriber::STATUS_BOOKED)
                                                                    ->count();
                                                                $availableSpots = $event->capacity - $bookedCount;
                                                            @endphp
                                                            <small class="text-muted d-block">{{ $bookedCount }}/{{ $event->capacity }} spots filled</small>
                                                        @endif
                                                    </div>
                                                    @if(!$loop->last)<hr>@endif
                                                @endforeach
                                            @else
                                                <div class="text-center py-4">
                                                    <span class="off-day text-muted">No classes scheduled</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($loop->iteration % 2 == 0)
                                    </div><div class="row">
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="icon-calendar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No classes scheduled for this week. Please check back later.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@else
    {{-- Default Schedule for Modern Template --}}
    <div class="max-w-7xl mx-auto">
        @if($section->title)
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4">{{ $section->title }}</h2>
            @if($section->subtitle)
            <p class="text-xl text-gray-600">{{ $section->subtitle }}</p>
            @endif
        </div>
        @endif

        @if($section->content)
        <div class="text-center mb-8">{!! $section->content !!}</div>
        @endif

        {{-- Dynamic Weekly Schedule Tabs --}}
        <div class="mb-8">
            <div class="flex flex-wrap justify-center space-x-4 mb-6">
                @foreach($showDays as $index => $day)
                    <button class="schedule-tab {{ $index === 0 ? 'active bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }} px-4 py-2 rounded-lg" data-day="{{ $day }}">
                        {{ ucfirst($day) }}
                    </button>
                @endforeach
            </div>

            {{-- Dynamic Schedule Content --}}
            <div id="schedule-content">
                @foreach($showDays as $index => $day)
                    <div class="schedule-day {{ $index === 0 ? 'active' : 'hidden' }}" id="{{ $day }}-schedule">
                        @if(isset($scheduleByDay[$day]) && $scheduleByDay[$day]->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($scheduleByDay[$day] as $event)
                                    @php
                                        $bookedCount = \App\Models\EventSubscriber::where('event_id', $event->id)
                                            ->where('booking_status', \App\Models\EventSubscriber::STATUS_BOOKED)
                                            ->count();
                                        $availableSpots = $event->capacity ? $event->capacity - $bookedCount : null;
                                    @endphp
                                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                                        <div class="flex justify-between items-start mb-3">
                                            <h3 class="text-xl font-semibold" style="{{ $cardTitleStyle }}">{{ $event->name ?: ($event->program ? $event->program->name : 'Class') }}</h3>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Class</span>
                                        </div>
                                        <p class="text-gray-600 mb-2"><i class="fas fa-clock mr-2"></i>{{ $event->startDateTime->format('g:i A') }} - {{ $event->endDateTime->format('g:i A') }}</p>
                                        @if($showInstructor && $event->instructor)
                                            <p class="text-gray-600 mb-2"><i class="fas fa-user mr-2"></i>{{ $event->instructor->fullName }}</p>
                                        @endif
                                        @if($showCapacity && $event->capacity)
                                            <p class="text-gray-600 mb-4"><i class="fas fa-users mr-2"></i>{{ $bookedCount }}/{{ $event->capacity }} booked</p>
                                        @endif
                                        @if($event->note)
                                            <p class="text-gray-500 mb-4 text-sm">{{ Str::limit($event->note, 100) }}</p>
                                        @endif
                                        @if($showBookButton)
                                            @if($availableSpots === null || $availableSpots > 0)
                                                <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">{{ $bookButtonText }}</button>
                                            @else
                                                <button class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed" disabled>Fully Booked</button>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500">No classes scheduled for {{ ucfirst($day) }}.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif