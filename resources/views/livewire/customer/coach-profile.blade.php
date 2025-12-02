<div>
    <div class="org-user-view">
        <div class="container my-5">
            @if($coach)
                <div class="row">
                    <!-- Coach Photo -->
                    <div class="col-md-4">
                        <div class="profile-picture">
                            @if($coach->profileImageUrl)
                                <img src="{{ $coach->profileImageUrl }}" 
                                     alt="{{ $coach->fullName }}"
                                     class="img-fluid">
                            @elseif($coach->portraitImageUrl)
                                <img src="{{ $coach->portraitImageUrl }}" 
                                     alt="{{ $coach->fullName }}"
                                     class="img-fluid">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light" 
                                     style="width: 100%; height: 400px;">
                                    <i class="fas fa-user-tie fa-5x text-muted"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Coach Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <h3 class="card-title">{{ $coach->fullName }}</h3>
                                        
                                        <p class="card-text coach-bio">
                                            <h6>Bio:</h6>
                                            <p>{{ $coach->bio ?? 'No bio available.' }}</p>
                                        </p>
                                        
                                        <p class="card-text coach-bio">
                                            <h6>Favorite Quote:</h6>
                                            <p>{{ $coach->favoriteQuote ?? 'No favorite quote available.' }}</p>
                                        </p>
                                        
                                        <p class="card-text coach-bio">
                                            <h6>Certificates:</h6>
                                            <p>
                                                @if($coach->certificates)
                                                    @php
                                                        // Handle certificates - could be JSON string, array, or plain string
                                                        $certificates = $coach->certificates;
                                                        if (is_string($certificates)) {
                                                            $decoded = json_decode($certificates, true);
                                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                                $certificates = $decoded;
                                                            } else {
                                                                $certificates = [$certificates];
                                                            }
                                                        }
                                                        if (!is_array($certificates)) {
                                                            $certificates = [$certificates];
                                                        }
                                                    @endphp
                                                    @if(count($certificates) > 0)
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($certificates as $certificate)
                                                                <li><i class="fas fa-certificate me-2 text-primary"></i>{{ $certificate }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        {{ $coach->certificates }}
                                                    @endif
                                                @else
                                                    No certificates available.
                                                @endif
                                            </p>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                            <h3 class="h4 fw-bold text-muted mb-2">Coach Not Found</h3>
                            <p class="text-muted mb-4">The coach profile you're looking for doesn't exist or is no longer available.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary package-btn">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Force dark text color for all content in org-user-view, override any inherited white */
        .org-user-view {
            padding: 40px 0;
            color: #000000 !important;
        }
        
        .org-user-view * {
            color: inherit;
        }
        
        .org-user-view .profile-picture {
            margin-bottom: 20px;
        }
        
        .org-user-view .profile-picture img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .org-user-view .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #ffffff !important;
        }
        
        .org-user-view .card-body {
            color: #000000 !important;
        }
        
        .org-user-view .card-title {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #000000 !important;
        }
        
        .org-user-view .coach-bio {
            margin-bottom: 1.5rem;
            color: #000000 !important;
        }
        
        .org-user-view .coach-bio h6 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--fitness-primary, #ff6b6b) !important;
            margin-bottom: 0.5rem;
        }
        
        .org-user-view .coach-bio p {
            color: #333333 !important;
            line-height: 1.6;
            margin-bottom: 0;
        }
        
        .org-user-view .coach-bio ul {
            padding-left: 0;
        }
        
        .org-user-view .coach-bio li {
            padding: 0.25rem 0;
            color: #333333 !important;
        }
        
        .org-user-view .card-text {
            color: #000000 !important;
        }
        
        .org-user-view .text-muted {
            color: #6c757d !important;
        }
        
        @media (max-width: 768px) {
            .org-user-view {
                padding: 20px 0;
            }
            
            .org-user-view .card-title {
                font-size: 1.5rem;
            }
        }
    </style>
</div>
