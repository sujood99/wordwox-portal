{{-- Schedule Section Partial --}}
@if($isFitness)
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

        {{-- Dynamic Schedule Navigation --}}
        <div class="row mb-3 mb-md-4">
            <div class="col-12">
                <ul class="nav nav-pills justify-content-center schedule-nav-pills" id="schedule-tabs" role="tablist">
                    @foreach($showDays as $index => $day)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link schedule-nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ $day }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $day }}" type="button" role="tab">
                                {{ ucfirst($day) }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Dynamic Schedule Content --}}
        <div class="tab-content" id="schedule-content">
            @foreach($showDays as $index => $day)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $day }}" role="tabpanel">
                    @if(isset($scheduleByDay[$day]) && $scheduleByDay[$day]->count() > 0)
                        <div class="row g-3 g-md-4">
                            @foreach($scheduleByDay[$day] as $event)
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="card schedule-card border-0 shadow-sm h-100">
                                        <div class="card-body p-3 p-md-4">
                                            <div class="d-flex justify-content-between align-items-start mb-2 mb-md-3">
                                                <h5 class="card-title mb-0 schedule-card-title">{{ $event->name ?: ($event->program ? $event->program->name : 'Class') }}</h5>
                                                <span class="badge bg-primary schedule-badge">Class</span>
                                            </div>
                                            <p class="text-muted mb-2 schedule-info"><i class="fas fa-clock me-2"></i>{{ $event->startDateTime->format('g:i A') }} - {{ $event->endDateTime->format('g:i A') }}</p>
                                            @if($showInstructor && $event->instructor)
                                                <p class="text-muted mb-2 schedule-info"><i class="fas fa-user me-2"></i>{{ $event->instructor->fullName }}</p>
                                            @endif
                                            @if($showCapacity && $event->capacity)
                                                @php
                                                    // For schedule-generated events, we can't count bookings from event_id
                                                    // since they don't exist in the event table yet
                                                    // Only count for actual events that exist in the database
                                                    $bookedCount = 0;
                                                    if (isset($event->isActualEvent) && $event->isActualEvent && isset($event->id)) {
                                                        // Use status field and STATUS_BOOKED constant
                                                    $bookedCount = \App\Models\EventSubscriber::where('event_id', $event->id)
                                                            ->where('status', \App\Models\EventSubscriber::STATUS_BOOKED)
                                                            ->where('isDeleted', false)
                                                        ->count();
                                                    }
                                                    $availableSpots = $event->capacity ? ($event->capacity - $bookedCount) : null;
                                                @endphp
                                                @if($event->capacity)
                                                    <p class="text-muted mb-2 mb-md-3 schedule-info">
                                                        <i class="fas fa-users me-2"></i>{{ $bookedCount }}/{{ $event->capacity }} booked
                                                        @if($availableSpots !== null)
                                                            ({{ $availableSpots }} spots left)
                                                        @endif
                                                    </p>
                                                @endif
                                            @endif
                                            @if($event->note)
                                                <p class="card-text small mb-2 mb-md-3 schedule-note">{{ Str::limit($event->note, 120) }}</p>
                                            @endif
                                            @if($showBookButton)
                                                @if($availableSpots > 0)
                                                    <button class="btn btn-outline-primary btn-sm schedule-btn w-100">{{ $bookButtonText }}</button>
                                                @else
                                                    <button class="btn btn-outline-secondary btn-sm schedule-btn w-100" disabled>Fully Booked</button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 py-md-5 schedule-empty">
                            <i class="fas fa-calendar-alt schedule-empty-icon text-muted mb-3"></i>
                            <p class="text-muted schedule-empty-text">No classes scheduled for {{ ucfirst($day) }}.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
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
        
        /* Responsive Navigation Pills */
        .schedule-nav-pills {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .schedule-nav-link {
            border-radius: 25px;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .schedule-nav-link.active {
            background: linear-gradient(135deg, #ff6b6b 0%, #4ecdc4 100%);
            border: none;
            color: white;
        }
        
        .schedule-nav-link:not(.active) {
            background: #f8f9fa;
            color: #666;
        }
        
        .schedule-nav-link:not(.active):hover {
            background: #e9ecef;
            color: #333;
        }
        
        /* Schedule Cards */
        .schedule-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
        }
        
        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1)!important;
        }
        
        .schedule-card-title {
            font-size: 1rem;
            line-height: 1.3;
        }
        
        .schedule-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .schedule-info {
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .schedule-note {
            font-size: 0.8rem;
            line-height: 1.5;
        }
        
        .schedule-btn {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
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
            .schedule-nav-link {
                font-size: 0.9rem;
                padding: 0.625rem 1.25rem;
            }
            .schedule-card-title {
                font-size: 1.1rem;
            }
            .schedule-info {
                font-size: 0.9rem;
            }
            .schedule-btn {
                font-size: 0.9rem;
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
            .schedule-nav-link {
                font-size: 1rem;
                padding: 0.625rem 1.25rem;
                margin: 0 0.3125rem;
            }
            .schedule-card-title {
                font-size: 1.25rem;
            }
            .schedule-badge {
                font-size: 0.75rem;
                padding: 0.35rem 0.65rem;
            }
            .schedule-info {
                font-size: 0.95rem;
            }
            .schedule-note {
                font-size: 0.875rem;
            }
            .schedule-btn {
                font-size: 0.95rem;
                padding: 0.625rem 1.25rem;
            }
        }
        
        @media (min-width: 992px) {
            .schedule-section {
                padding: 80px 29px;
            }
        }
        
        /* Mobile-specific adjustments */
        @media (max-width: 575px) {
            .schedule-nav-pills {
                justify-content: flex-start;
            }
            .schedule-nav-link {
                font-size: 0.8rem;
                padding: 0.45rem 0.85rem;
                margin: 0.2rem;
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
                                                        <h5 class="text-primary mb-1">{{ $event->name }}</h5>
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
                                            <h3 class="text-xl font-semibold">{{ $event->name ?: ($event->program ? $event->program->name : 'Class') }}</h3>
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