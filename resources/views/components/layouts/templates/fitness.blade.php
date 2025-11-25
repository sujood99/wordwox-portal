<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@stack('title', config('app.name', 'Fitness Gym'))</title>
    
    @stack('meta')

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Fitness Template CSS -->
    @vite(['resources/css/fitness.css', 'resources/js/fitness.js'])
    @livewireStyles

    @stack('head')

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }
        
        .fitness-template {
            min-height: 100vh;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #ff6b6b 0%, #4ecdc4 100%);
            color: white;
            padding: 100px 0;
        }
        
        .section-padding {
            padding: 80px 0;
        }
        
        .btn-fitness {
            background: linear-gradient(135deg, #ff6b6b 0%, #4ecdc4 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .btn-fitness:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .card-fitness {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card-fitness:hover {
            transform: translateY(-5px);
        }
        
        .navbar-fitness {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .footer-fitness {
            background: #2c3e50;
            color: white;
            padding: 50px 0 20px 0;
        }
        
        @php
            // Determine page type for specific styling
            $pageType = $page->type ?? 'default';
            $pageSlug = $page->slug ?? 'home';
        @endphp
    </style>
</head>
<body class="fitness-template">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-fitness fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="/">
                <i class="fas fa-dumbbell text-danger me-2"></i>
                Wodworx
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Home Link -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') || request()->is('home') ? 'active fw-bold' : '' }}" 
                           href="/">Home</a>
                    </li>
                    
                    <!-- Dynamic Navigation Pages -->
                    @if(isset($navigationPages) && $navigationPages->count() > 0)
                        @foreach($navigationPages as $navPage)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is($navPage->slug) || request()->is($navPage->slug . '/*') ? 'active fw-bold' : '' }}" 
                                   href="/{{ $navPage->slug }}">{{ $navPage->title }}</a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="fitness-content" style="margin-top: 80px;">
        @php
            // Add page-specific classes and styling based on page type
            $pageClasses = match($pageType) {
                'home' => 'home-page',
                'about' => 'about-page',
                'contact' => 'contact-page',
                'blog' => 'blog-page',
                'coaches' => 'coaches-page',
                'packages' => 'packages-page',
                'schedule' => 'schedule-page',
                default => 'default-page'
            };
        @endphp
        
        <div class="{{ $pageClasses }}">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    @php
        $footerData = \App\Services\CmsFooterService::getFooterData();
        $about = $footerData['about'] ?? [];
        $contact = $footerData['contact'] ?? [];
        $classes = $footerData['classes'] ?? [];
        $copyright = $footerData['copyright'] ?? [];
    @endphp
    <footer class="footer-fitness">
        <div class="container">
            <div class="row">
                @if(($about['is_active'] ?? true) && (!empty($about['title']) || !empty($about['description'])))
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        @if(!empty($about['title']))
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-dumbbell text-danger me-2"></i>
                            {{ $about['title'] }}
                        </h5>
                        @endif
                        @if(!empty($about['description']))
                        <p class="mb-3">{{ $about['description'] }}</p>
                        @endif
                        @if(!empty($about['social_links']) && is_array($about['social_links']))
                        <div class="social-links">
                            @foreach($about['social_links'] as $social)
                            <a href="{{ $social['url'] ?? '#' }}" class="text-white me-3 fs-4">
                                <i class="{{ $social['icon'] ?? 'fab fa-facebook' }}"></i>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(($classes['is_active'] ?? true) && (!empty($classes['title']) || !empty($classes['items'])))
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        @if(!empty($classes['title']))
                        <h5 class="fw-bold mb-3">{{ $classes['title'] }}</h5>
                        @endif
                        @if(!empty($classes['items']) && is_array($classes['items']))
                        <ul class="list-unstyled">
                            @foreach($classes['items'] as $item)
                            <li class="mb-2">
                                <a href="#" class="text-white-50 text-decoration-none">
                                    <i class="fas fa-chevron-right me-2"></i>{{ $item }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="fw-bold mb-3">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="/" class="text-white-50 text-decoration-none">
                                    <i class="fas fa-chevron-right me-2"></i>Home
                                </a>
                            </li>
                            @if(isset($navigationPages) && $navigationPages->count() > 0)
                                @foreach($navigationPages->take(4) as $navPage)
                                <li class="mb-2">
                                    <a href="/{{ $navPage->slug }}" class="text-white-50 text-decoration-none">
                                        <i class="fas fa-chevron-right me-2"></i>{{ $navPage->title }}
                                    </a>
                                </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                
                @if(($contact['is_active'] ?? true) && (!empty($contact['address']) || !empty($contact['phone']) || !empty($contact['email'])))
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="fw-bold mb-3">Contact Info</h5>
                        <div class="contact-info">
                            @if(!empty($contact['address']))
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                {{ $contact['address'] }}
                            </p>
                            @endif
                            @if(!empty($contact['phone']))
                            <p class="mb-2">
                                <i class="fas fa-phone me-2 text-danger"></i>
                                <a href="tel:{{ $contact['phone'] }}" class="text-white-50 text-decoration-none">
                                    {{ $contact['phone'] }}
                                </a>
                            </p>
                            @endif
                            @if(!empty($contact['email']))
                            <p class="mb-2">
                                <i class="fas fa-envelope me-2 text-danger"></i>
                                <a href="mailto:{{ $contact['email'] }}" class="text-white-50 text-decoration-none">
                                    {{ $contact['email'] }}
                                </a>
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <hr class="my-4 border-secondary">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0 text-white-50">
                        &copy; {{ $copyright['year'] ?? date('Y') }} {{ config('app.name', 'Fitness Gym') }}. 
                        @if(!empty($copyright['text']))
                        {{ $copyright['text'] }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @stack('scripts')
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-fitness');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
    </script>
</body>
</html>