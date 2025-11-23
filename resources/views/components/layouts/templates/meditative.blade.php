<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@stack('title', config('app.name', 'Meditative'))</title>
    
    @stack('meta')

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=EB+Garamond:400,400i,500,500i,600,600i,700,700i&display=swap" rel="stylesheet">

    <!-- CSS Dependencies from CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.10.0/jquery.timepicker.min.css">

    <!-- Scripts -->
    @vite(['resources/css/meditative.css', 'resources/js/meditative.js'])
    @livewireStyles

    @stack('head')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <div class="row m-auto">
                <div class="col-12 w-100 text-center">
                    <a class="navbar-brand w-100" href="/">{{ config('app.name', 'Meditative') }}</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="oi oi-menu"></span> Menu
                    </button>
                </div>
                <div class="col-12 w-100 text-center">
                    <div class="collapse navbar-collapse" id="ftco-nav">
                        <ul class="navbar-nav m-auto">
                            <!-- Home Link -->
                            <li class="nav-item {{ request()->is('/') || request()->is('home') || request()->is('site') ? 'active' : '' }}">
                                <a href="/site" class="nav-link">Home</a>
                            </li>
                            
                            <!-- Dynamic Navigation Pages (ordered by sort_order) -->
                            @if(isset($navigationPages) && $navigationPages->count() > 0)
                                @foreach($navigationPages as $navPage)
                                    <li class="nav-item {{ request()->is($navPage->slug) || request()->is($navPage->slug . '/*') ? 'active' : '' }}">
                                        <a href="/{{ $navPage->slug }}" class="nav-link">{{ $navPage->title }}</a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- END nav -->

    <!-- Page Content -->
    <div class="meditative-template">
        {{ $slot }}
    </div>

    <!-- Footer -->
    @php
        $footerData = \App\Services\CmsFooterService::getFooterData();
        $quote = $footerData['quote'] ?? [];
        $about = $footerData['about'] ?? [];
        $classes = $footerData['classes'] ?? [];
        $contact = $footerData['contact'] ?? [];
        $hours = $footerData['hours'] ?? [];
        $copyright = $footerData['copyright'] ?? [];
    @endphp
    <footer class="ftco-footer ftco-section bg-light">
        <div class="container">
            <div class="row d-flex">
                @if(($about['is_active'] ?? true) && (!empty($about['title']) || !empty($about['description'])))
                <div class="col-md">
                    <div class="ftco-footer-widget mb-4">
                        @if(!empty($about['title']))
                        <h2 class="ftco-heading-2">{{ $about['title'] }}</h2>
                        @endif
                        @if(!empty($about['description']))
                        <p>{{ $about['description'] }}</p>
                        @endif
                        @if(!empty($about['social_links']) && is_array($about['social_links']))
                        <ul class="ftco-footer-social list-unstyled float-lft mt-3">
                            @foreach($about['social_links'] as $social)
                            <li class="ftco-animate">
                                <a href="{{ $social['url'] ?? '#' }}">
                                    <span class="{{ $social['icon'] ?? 'icon-instagram' }}"></span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(($classes['is_active'] ?? true) && (!empty($classes['title']) || !empty($classes['items'])))
                <div class="col-md">
                    <div class="ftco-footer-widget mb-4 ml-md-4">
                        @if(!empty($classes['title']))
                        <h2 class="ftco-heading-2">{{ $classes['title'] }}</h2>
                        @endif
                        @if(!empty($classes['items']) && is_array($classes['items']))
                        <ul class="list-unstyled">
                            @foreach($classes['items'] as $item)
                            <li><a href="#">{{ $item }}</a></li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="col-md">
                    <div class="ftco-footer-widget mb-4">
                        <h2 class="ftco-heading-2">Quick Links</h2>
                        <ul class="list-unstyled">
                            <li><a href="/site">Home</a></li>
                            @if(isset($navigationPages) && $navigationPages->count() > 0)
                                @foreach($navigationPages->take(4) as $navPage)
                                <li><a href="/{{ $navPage->slug }}">{{ $navPage->title }}</a></li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                
                @if(($contact['is_active'] ?? true) && (!empty($contact['title']) || !empty($contact['address']) || !empty($contact['phone']) || !empty($contact['email'])))
                <div class="col-md">
                    <div class="ftco-footer-widget mb-4">
                        <h2 class="ftco-heading-2">Have a Questions?</h2>
                        <div class="block-23 mb-3">
                            <ul>
                                @if(!empty($contact['address']))
                                <li><span class="icon icon-map-marker"></span><span class="text">{{ $contact['address'] }}</span></li>
                                @endif
                                @if(!empty($contact['phone']))
                                <li><a href="tel:{{ $contact['phone'] }}"><span class="icon icon-phone"></span><span class="text">{{ $contact['phone'] }}</span></a></li>
                                @endif
                                @if(!empty($contact['email']))
                                <li><a href="mailto:{{ $contact['email'] }}"><span class="icon icon-envelope"></span><span class="text">{{ $contact['email'] }}</span></a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">
                        Copyright &copy; {{ $copyright['year'] ?? date('Y') }} {{ config('app.name', 'Meditative') }}. 
                        @if(!empty($copyright['text']))
                        {{ $copyright['text'] }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- jQuery and Bootstrap from CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    
    <!-- Third-party plugins from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.stellar/1.2.0/jquery.stellar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-animateNumber/0.0.14/jquery.animateNumber.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.10.0/jquery.timepicker.min.js"></script>
    
    <!-- Scrollax from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/scrollax@1.0.0/scrollax.min.js"></script>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
