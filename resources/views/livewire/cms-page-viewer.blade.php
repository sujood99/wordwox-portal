<div>
    @if($page)
        {{-- Set page title and meta tags --}}
        @push('title')
            {{ $page->seo_title ?? $page->title }}
        @endpush

        @if($page->seo_description)
            @push('meta')
                <meta name="description" content="{{ $page->seo_description }}">
            @endpush
        @endif

        @if($page->seo_keywords)
            @push('meta')
                <meta name="keywords" content="{{ $page->seo_keywords }}">
            @endpush
        @endif

        {{-- Open Graph Meta Tags --}}
        @push('meta')
            <meta property="og:title" content="{{ $page->seo_title ?? $page->title }}">
            <meta property="og:description" content="{{ $page->seo_description ?? $page->description ?? '' }}">
            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ url()->current() }}">
            @if($page->featured_image)
            <meta property="og:image" content="{{ asset('storage/' . $page->featured_image) }}">
            @endif
            
            {{-- Twitter Card --}}
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $page->seo_title ?? $page->title }}">
            <meta name="twitter:description" content="{{ $page->seo_description ?? $page->description ?? '' }}">
            @if($page->featured_image)
            <meta name="twitter:image" content="{{ asset('storage/' . $page->featured_image) }}">
            @endif
            
            {{-- Structured Data (JSON-LD) --}}
            @php
                $jsonLd = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $page->title,
                    'description' => $page->seo_description ?? $page->description ?? '',
                    'url' => url()->current()
                ];
                if ($page->featured_image) {
                    $jsonLd['image'] = asset('storage/' . $page->featured_image);
                }
            @endphp
            <script type="application/ld+json">
            {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endpush

        @php
            $currentTemplate = $template ?? ($page->template ?? env('CMS_DEFAULT_THEME', 'modern'));
            $isMeditative = $currentTemplate === 'meditative';
        @endphp
        
        <div class="cms-page" data-page-type="{{ $page->type }}" data-page-id="{{ $page->id }}" data-template="{{ $currentTemplate }}">
            
            {{-- Page Header (only show for non-home pages without hero sections) --}}
            @if($page->type !== 'home' && !$this->hasHeroSection())
                @if($isMeditative)
                    {{-- Meditative Template Hero Header --}}
                    <section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('images/bg_3.jpg') }}');" data-stellar-background-ratio="0.5">
                        <div class="overlay"></div>
                        <div class="container">
                            <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
                                <div class="col-md-9 ftco-animate text-center">
                                    <h1 class="mb-3 bread">{{ $page->title }}</h1>
                                    @if($page->description)
                                    <p class="breadcrumbs"><span class="mr-2"><a href="/">Home</a></span> <span>{{ $page->title }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                @else
                    {{-- Default Template Header --}}
                    <div class="page-header bg-gray-50 py-16">
                        <div class="container mx-auto px-6 text-center">
                            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ $page->title }}</h1>
                            @if($page->description)
                                <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ $page->description }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            {{-- Page Sections --}}
            @if($page->sections && $page->sections->count() > 0)
                <div class="cms-sections">
                    @foreach($page->sections->where('is_active', true) as $section)
                        @php
                            // Get section customization settings
                            $sectionSettings = is_string($section->settings_json) ? 
                                json_decode($section->settings_json, true) : 
                                ($section->settings_json ?? []);
                            
                            // Extract layout, spacing, background, typography settings
                            $layout = $sectionSettings['layout'] ?? [];
                            $spacing = $sectionSettings['spacing'] ?? [];
                            $background = $sectionSettings['background'] ?? [];
                            $typography = $sectionSettings['typography'] ?? [];
                            
                            // Build CSS classes
                            $sectionClasses = ['cms-section', 'cms-section-' . $section->type];
                            $containerClasses = [];
                            $sectionStyles = [];
                            
                            // Layout classes
                            if ($isMeditative) {
                                // Meditative template uses Bootstrap 4
                                switch ($layout['width'] ?? 'container') {
                                    case 'full':
                                        $containerClasses[] = 'container-fluid';
                                        break;
                                    case 'narrow':
                                        $containerClasses[] = 'container';
                                        break;
                                    default: // container
                                        $containerClasses[] = 'container';
                                        break;
                                }
                            } else {
                                // Other templates use Tailwind CSS
                                switch ($layout['width'] ?? 'container') {
                                    case 'full':
                                        $containerClasses[] = 'w-full';
                                        break;
                                    case 'narrow':
                                        $containerClasses[] = 'max-w-4xl mx-auto px-6';
                                        break;
                                    default: // container
                                        $containerClasses[] = 'container mx-auto px-6';
                                        break;
                                }
                            }
                            
                            // Spacing classes
                            $paddingMap = [
                                'none' => '0',
                                'xs' => '0.5rem',
                                'sm' => '1rem', 
                                'md' => '2rem',
                                'lg' => '3rem',
                                'xl' => '4rem',
                                '2xl' => '6rem'
                            ];
                            
                            $topPadding = $spacing['padding_top'] ?? 'md';
                            $bottomPadding = $spacing['padding_bottom'] ?? 'md';
                            $sectionStyles[] = 'padding-top: ' . ($paddingMap[$topPadding] ?? $paddingMap['md']);
                            $sectionStyles[] = 'padding-bottom: ' . ($paddingMap[$bottomPadding] ?? $paddingMap['md']);
                            
                            // Background styles
                            switch ($background['type'] ?? 'color') {
                                case 'gradient':
                                    $gradientClass = $background['gradient'] ?? 'bg-gradient-to-r from-blue-500 to-purple-600';
                                    $sectionClasses[] = $gradientClass;
                                    break;
                                case 'image':
                                    $sectionClasses[] = 'bg-cover bg-center';
                                    if (isset($background['image'])) {
                                        $sectionStyles[] = "background-image: url('{$background['image']}')";
                                    }
                                    break;
                                case 'none':
                                    // No background
                                    break;
                                default: // color
                                    $sectionStyles[] = 'background-color: ' . ($background['color'] ?? '#ffffff');
                                    break;
                            }
                            
                            // Typography
                            if ($isMeditative) {
                                // Bootstrap 4 text alignment classes
                                $alignmentMap = [
                                    'left' => 'text-left',
                                    'center' => 'text-center', 
                                    'right' => 'text-right',
                                    'justify' => 'text-justify'
                                ];
                            } else {
                                // Tailwind CSS text alignment classes
                                $alignmentMap = [
                                    'left' => 'text-left',
                                    'center' => 'text-center', 
                                    'right' => 'text-right',
                                    'justify' => 'text-justify'
                                ];
                            }
                            
                            $containerClasses[] = $alignmentMap[$typography['text_align'] ?? 'left'];
                            
                            if (isset($typography['text_color'])) {
                                $sectionStyles[] = 'color: ' . $typography['text_color'];
                            }
                            
                            // Add template-specific classes
                            $templateClasses = [];
                            if ($isMeditative) {
                                $templateClasses[] = 'ftco-section';
                                if ($section->type !== 'hero') {
                                    $templateClasses[] = 'ftco-animate';
                                }
                            }
                            $finalSectionClasses = array_merge($sectionClasses, $templateClasses);
                            
                            $sectionClassString = implode(' ', $finalSectionClasses);
                            $containerClassString = implode(' ', $containerClasses);
                            $sectionStyleString = implode('; ', $sectionStyles);
                        @endphp
                        
                        <div class="{{ $sectionClassString }}" 
                             id="section-{{ $section->id }}" 
                             style="{{ $sectionStyleString }}">
                            <div class="{{ $containerClassString }}">
                            @switch($section->type)
                                @case('hero')
                                    @if($isMeditative)
                                        {{-- Meditative Template Hero Slider --}}
                                        <section class="home-slider js-fullheight owl-carousel">
                                            <div class="slider-item js-fullheight" style="background-image:url({{ asset('images/bg_1.jpg') }});">
                                                <div class="overlay"></div>
                                                <div class="container">
                                                    <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
                                                        <div class="col-md-10 text ftco-animate text-center">
                                                            @if($section->title)
                                                            <h1 class="mb-4">{{ $section->title }}</h1>
                                                            @endif
                                                            @if($section->subtitle)
                                                            <h3 class="subheading">{{ $section->subtitle }}</h3>
                                                            @endif
                                                            @if($section->content)
                                                            <div class="mt-4">{!! $section->content !!}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    @else
                                    @php
                                        $settings = is_string($section->settings) ? json_decode($section->settings, true) : ($section->settings ?? []);
                                        $bgColor = $settings['background_color'] ?? '#1f2937';
                                        $textColor = $settings['text_color'] ?? '#ffffff';
                                    @endphp
                                    @if($section->title)
                                        <h1 class="text-5xl md:text-6xl font-bold mb-6">{{ $section->title }}</h1>
                                    @endif
                                    @if($section->subtitle)
                                        <p class="text-xl md:text-2xl mb-8 opacity-90">{{ $section->subtitle }}</p>
                                    @endif
                                    @if($section->content)
                                        <div class="text-lg mb-8 max-w-3xl mx-auto">{!! $section->content !!}</div>
                                    @endif
                                    @endif
                                    @break

                                @case('heading')
                                    <div class="heading-section py-8">
                                        <div class="container mx-auto px-6">
                                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $section->content }}</h2>
                                        </div>
                                    </div>
                                    @break

                                @case('paragraph')
                                    <div class="max-w-none text-base leading-relaxed text-gray-700 ck-content">
                                        {!! $section->content !!}
                                    </div>
                                    @break

                                @case('quote')
                                    <blockquote class="text-2xl md:text-3xl italic text-gray-700 mb-6">
                                        "{{ $section->content }}"
                                    </blockquote>
                                    @if($section->title)
                                        <cite class="text-lg text-gray-600">— {{ $section->title }}</cite>
                                    @endif
                                    @break

                                @case('list')
                                    @php
                                        $items = explode("\n", $section->content);
                                        $items = array_filter(array_map('trim', $items));
                                    @endphp
                                    <ul class="space-y-3 text-lg">
                                        @foreach($items as $item)
                                            @php
                                                $item = preg_replace('/^[•\-\*]\s*/', '', $item);
                                            @endphp
                                            <li class="flex items-start">
                                                <span class="text-blue-600 mr-3 mt-1">•</span>
                                                <span>{{ $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @break

                                @case('button')
                                    <a href="{{ $section->title ?: '#' }}" 
                                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-lg transition-colors">
                                        {{ $section->content ?: 'Click me' }}
                                    </a>
                                    @break

                                @case('spacer')
                                    @php
                                        $height = (int)($section->content ?: 50);
                                    @endphp
                                    <div class="spacer-section" style="height: {{ $height }}px;"></div>
                                    @break

                                @case('code')
                                    <pre class="bg-gray-900 text-green-400 p-6 rounded-lg overflow-x-auto"><code>{{ $section->content }}</code></pre>
                                    @break

                                @case('cta')
                                    @php
                                        $data = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        $buttons = $data['buttons'] ?? [];
                                    @endphp
                                    @if($section->title)
                                        <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ $section->title }}</h2>
                                    @endif
                                    @if($section->content)
                                        <p class="text-xl mb-8 opacity-90">{{ $section->content }}</p>
                                    @endif
                                            @if(!empty($buttons))
                                                <div class="flex flex-wrap justify-center gap-4">
                                                    @foreach($buttons as $button)
                                                        @php
                                                            $buttonClass = 'inline-block px-8 py-4 rounded-lg font-semibold transition-colors ';
                                                            switch($button['style'] ?? 'primary') {
                                                                case 'primary':
                                                                    $buttonClass .= 'bg-white text-blue-600 hover:bg-gray-100';
                                                                    break;
                                                                case 'secondary':
                                                                    $buttonClass .= 'bg-gray-600 text-white hover:bg-gray-700';
                                                                    break;
                                                                case 'outline':
                                                                    $buttonClass .= 'border-2 border-white text-white hover:bg-white hover:text-blue-600';
                                                                    break;
                                                            }
                                                        @endphp
                                                        <a href="{{ $button['url'] ?? '#' }}" class="{{ $buttonClass }}">
                                                            {{ $button['text'] ?? 'Button' }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                    @break

                                @case('banner')
                                    @php
                                        // Get banner data from block
                                        $bannerData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($bannerData)) {
                                            $bannerData = [];
                                        }
                                        $bannerImageUrl = $bannerData['image_url'] ?? '';
                                        $linkUrl = $bannerData['link_url'] ?? '';
                                        $altText = $bannerData['alt_text'] ?? '';
                                        $height = $bannerData['height'] ?? 'medium';
                                        
                                        $heightClass = match($height) {
                                            'small' => 'h-48 md:h-56',
                                            'medium' => 'h-64 md:h-72',
                                            'large' => 'h-80 md:h-96',
                                            'xl' => 'h-96 md:h-[32rem]',
                                            default => 'h-64 md:h-72'
                                        };
                                    @endphp
                                    <div class="banner-section">
                                        @if($bannerImageUrl)
                                            @if($linkUrl)
                                                <a href="{{ $linkUrl }}" class="block overflow-hidden">
                                                    <img src="{{ $bannerImageUrl }}" 
                                                         alt="{{ $altText ?: 'Banner image' }}" 
                                                         class="w-full {{ $heightClass }} object-cover hover:scale-105 transition-transform duration-300">
                                                </a>
                                            @else
                                                <div class="overflow-hidden">
                                                    <img src="{{ $bannerImageUrl }}" 
                                                         alt="{{ $altText ?: 'Banner image' }}" 
                                                         class="w-full {{ $heightClass }} object-cover">
                                                </div>
                                            @endif
                                        @else
                                            <!-- Placeholder when no image is set -->
                                            <div class="{{ $heightClass }} bg-gray-200 flex items-center justify-center">
                                                <div class="text-center text-gray-500">
                                                    <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <p class="text-lg font-medium">Banner Image</p>
                                                    <p class="text-sm">Configure in CMS editor</p>
                                                </div>
                                            @endif
                                    @break

                                @case('image')
                                    @php
                                        // Get image data from block
                                        $imageBlockData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($imageBlockData)) {
                                            $imageBlockData = [];
                                        }
                                        $imageUrl = $imageBlockData['image_url'] ?? $section->content ?? '';
                                        $altText = $imageBlockData['alt_text'] ?? 'Image';
                                        $height = $imageBlockData['height'] ?? 'auto';
                                        $width = $imageBlockData['width'] ?? 'full';
                                        
                                        // Convert height to CSS class
                                        $heightClass = match($height) {
                                            'small' => 'h-48 md:h-56',
                                            'medium' => 'h-64 md:h-72',
                                            'large' => 'h-80 md:h-96',
                                            'xl' => 'h-96 md:h-[32rem]',
                                            default => 'h-auto'
                                        };
                                        
                                        // Convert width to CSS class
                                        $widthClass = match($width) {
                                            'small' => 'w-1/4',
                                            'medium' => 'w-1/2',
                                            'large' => 'w-3/4',
                                            default => 'w-full'
                                        };
                                        
                                        $containerClass = $width === 'full' ? 'w-full' : 'flex justify-center';
                                    @endphp
                                    @if($imageUrl)
                                        <div class="{{ $containerClass }}">
                                            <div class="{{ $widthClass }}">
                                                <img src="{{ $imageUrl }}" 
                                                     alt="{{ $altText }}" 
                                                     class="w-full {{ $heightClass }} object-cover rounded-lg shadow-lg">
                                                @if($section->title)
                                                    <p class="text-center text-gray-600 mt-4 text-lg">{{ $section->title }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                                <div class="text-center">
                                                    <div class="bg-gray-200 rounded-lg p-12 mb-4 {{ $widthClass }} mx-auto">
                                                        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <p class="mt-4 text-gray-500">Image placeholder</p>
                                                    </div>
                                                    @if($section->title)
                                                        <p class="text-sm text-gray-600 italic">{{ $section->title }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                    @break

                                @case('content')
                                    @if($section->title)
                                        <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ $section->title }}</h2>
                                    @endif
                                    @if($section->subtitle)
                                        <p class="text-xl text-gray-600 mb-6">{{ $section->subtitle }}</p>
                                    @endif
                                    @if($section->content)
                                        <div class="prose prose-lg max-w-none">
                                            {!! $section->content !!}
                                        </div>
                                    @endif
                                    @break

                                @case('contact')
                                    @php
                                        // Get contact data from block
                                        $contactData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($contactData)) {
                                            $contactData = [];
                                        }
                                        $email = $contactData['email'] ?? '';
                                        $phone = $contactData['phone'] ?? '';
                                        $faxNumbers = $contactData['fax'] ?? [];
                                        if (!is_array($faxNumbers)) {
                                            $faxNumbers = [];
                                        }
                                    @endphp
                                    @if($section->title)
                                        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ $section->title }}</h2>
                                    @endif
                                    @if($section->subtitle)
                                        <p class="text-xl text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
                                    @endif
                                            
                                            <div class="max-w-4xl mx-auto">
                                                <div class="grid md:grid-cols-2 gap-8">
                                                    <!-- Contact Information -->
                                                    <div class="bg-white rounded-lg shadow-lg p-8">
                                                        <h3 class="text-2xl font-semibold text-gray-900 mb-6">Get in Touch</h3>
                                                        
                                                        @if($section->content)
                                                            <div class="prose text-gray-600 mb-6">
                                                                {!! $section->content !!}
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="space-y-4">
                                                            @if($email)
                                                                <div class="flex items-center space-x-3">
                                                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                                    </svg>
                                                                    <a href="mailto:{{ $email }}" class="text-gray-700 hover:text-blue-600">{{ $email }}</a>
                                                                </div>
                                                            @endif
                                                            
                                                            @if($phone)
                                                                <div class="flex items-center space-x-3">
                                                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                                    </svg>
                                                                    <a href="tel:{{ $phone }}" class="text-gray-700 hover:text-blue-600">{{ $phone }}</a>
                                                                </div>
                                                            @endif
                                                            
                                                            @if(count($faxNumbers) > 0)
                                                                @foreach($faxNumbers as $fax)
                                                                    @if($fax)
                                                                        <div class="flex items-center space-x-3">
                                                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V3a1 1 0 011 1v1M7 4V3a1 1 0 011-1m0 0V2m0 0h8" />
                                                                            </svg>
                                                                            <span class="text-gray-700">{{ $fax }} (Fax)</span>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Contact Form -->
                                                    <div class="bg-white rounded-lg shadow-lg p-8">
                                                        <h3 class="text-2xl font-semibold text-gray-900 mb-6">Send us a Message</h3>
                                                        @livewire('contact-form')
                                                    </div>
                                                </div>
                                            </div>
                                    @break

                                @case('video')
                                    @php
                                        // Get video data
                                        $videoData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($videoData)) {
                                            $videoData = [];
                                        }
                                        $videoUrl = $videoData['video_url'] ?? $section->content ?? '';
                                        $videoPath = $videoData['video_path'] ?? '';
                                        $isUploaded = !empty($videoPath);
                                        
                                        // Check if it's a YouTube or Vimeo URL
                                        $isYouTube = preg_match('/(youtube\.com|youtu\.be)/', $videoUrl);
                                        $isVimeo = preg_match('/vimeo\.com/', $videoUrl);
                                    @endphp
                                    <div class="video-section py-12">
                                        <div class="container mx-auto px-6">
                                            @if($section->title)
                                                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6 text-center">{{ $section->title }}</h2>
                                            @endif
                                            
                                            @if($videoUrl)
                                                @if($isYouTube || $isVimeo)
                                                    <!-- YouTube/Vimeo Embed -->
                                                    <div class="aspect-video w-full max-w-4xl mx-auto">
                                                        @if($isYouTube)
                                                            @php
                                                                // Extract YouTube video ID
                                                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches);
                                                                $youtubeId = $matches[1] ?? '';
                                                            @endphp
                                                            <iframe 
                                                                class="w-full h-full rounded-lg"
                                                                src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                                                frameborder="0"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                allowfullscreen
                                                            ></iframe>
                                                        @elseif($isVimeo)
                                                            @php
                                                                // Extract Vimeo video ID
                                                                preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                                                                $vimeoId = $matches[1] ?? '';
                                                            @endphp
                                                            <iframe 
                                                                class="w-full h-full rounded-lg"
                                                                src="https://player.vimeo.com/video/{{ $vimeoId }}"
                                                                frameborder="0"
                                                                allow="autoplay; fullscreen; picture-in-picture"
                                                                allowfullscreen
                                                            ></iframe>
                                                        @endif
                                                    </div>
                                                @else
                                                    <!-- Direct Video File -->
                                                    <div class="w-full max-w-4xl mx-auto">
                                                        <video 
                                                            controls 
                                                            class="w-full rounded-lg shadow-lg"
                                                            preload="metadata"
                                                        >
                                                            <source src="{{ $isUploaded ? asset('storage/' . $videoPath) : $videoUrl }}" type="video/mp4">
                                                            <source src="{{ $isUploaded ? asset('storage/' . $videoPath) : $videoUrl }}" type="video/webm">
                                                            <source src="{{ $isUploaded ? asset('storage/' . $videoPath) : $videoUrl }}" type="video/ogg">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    </div>
                                                @endif
                                            @endif
                                            
                                            @if($section->subtitle)
                                                <p class="text-gray-600 mt-4 text-center">{{ $section->subtitle }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @break

                                @case('packages')
                                    @php
                                        // Get packages block data
                                        $packagesData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($packagesData)) {
                                            $packagesData = [];
                                        }
                                        
                                        // Get organization ID from page or default
                                        $orgId = $page->org_id ?? env('CMS_DEFAULT_ORG_ID', 8);
                                        
                                        // Fetch active organization plans
                                        $plans = \App\Models\OrgPlan::where('org_id', $orgId)
                                            ->where('isActive', true)
                                            ->where('is_upcharge_plan', false) // Only main plans
                                            ->orderBy('name', 'asc') // Order by name since sortOrder column doesn't exist
                                            ->get();
                                        
                                        $layout = $packagesData['layout'] ?? 'grid';
                                        $columns = $packagesData['columns'] ?? 3;
                                        $showDescription = $packagesData['show_description'] ?? true;
                                        $showPrograms = $packagesData['show_programs'] ?? true;
                                        $buyButtonText = $packagesData['buy_button_text'] ?? 'Buy';
                                        $purchaseAtGymText = $packagesData['purchase_at_gym_text'] ?? 'Purchase at the Gym';
                                        
                                        $gridCols = match($columns) {
                                            2 => 'md:grid-cols-2',
                                            4 => 'md:grid-cols-4',
                                            default => 'md:grid-cols-3'
                                        };
                                    @endphp
                                    
                                    @if($section->title)
                                        @if($isMeditative)
                                            <div class="row justify-content-center mb-5 pb-3">
                                                <div class="col-md-12 heading-section ftco-animate text-center">
                                                    <h2 class="mb-1">{{ $section->title }}</h2>
                                                    @if($section->subtitle)
                                                    <p class="text-gray-600">{{ $section->subtitle }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 text-center">{{ $section->title }}</h2>
                                            @if($section->subtitle)
                                            <p class="text-xl text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
                                            @endif
                                        @endif
                                    @endif
                                    
                                    @if($plans->count() > 0)
                                        @if($isMeditative)
                                            {{-- Meditative Template: Use offer-deal cards --}}
                                            @php
                                                // Calculate Bootstrap 4 column class based on columns setting
                                                $bootstrapCols = match((int)$columns) {
                                                    2 => 'col-md-6 col-sm-6 col-12',
                                                    4 => 'col-md-3 col-sm-6 col-12',
                                                    default => 'col-md-4 col-sm-6 col-12' // 3 columns default
                                                };
                                            @endphp
                                            <div class="row">
                                                @foreach($plans as $plan)
                                                    <div class="{{ $bootstrapCols }} d-flex align-items-stretch mb-4">
                                                        <div class="offer-deal text-center ftco-animate w-100">
                                                            <div class="img" style="background-image: url({{ asset('images/classes-1.jpg') }}); height: 200px; background-size: cover; background-position: center;"></div>
                                                            <div class="text mt-4 p-3">
                                                                <h3 class="mb-3">{{ $plan->name }}</h3>
                                                                <p class="mb-3 text-muted">{{ $plan->duration_text }}</p>
                                                                @if($showDescription && $plan->description)
                                                                <p class="mb-3">{{ Str::limit($plan->description, 100) }}</p>
                                                                @endif
                                                                <h4 class="mb-3 text-primary font-weight-bold">{{ number_format($plan->price, 2) }} {{ $plan->currency }}</h4>
                                                                <p class="mb-0"><a href="/org-plan/index?plan={{ $plan->uuid ?? $plan->id }}" class="btn btn-white px-4 py-3">{{ $buyButtonText }} <span class="ion-ios-arrow-round-forward"></span></a></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="{{ $layout === 'grid' ? 'grid grid-cols-1 ' . $gridCols . ' gap-6' : 'space-y-6' }}">
                                                @foreach($plans as $plan)
                                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow card">
                                                    <div class="p-6">
                                                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                                                        <p class="text-gray-600 mb-4">{{ $plan->duration_text }}</p>
                                                        
                                                        @if($showDescription && $plan->description)
                                                            <p class="text-gray-700 mb-4 text-sm">{{ Str::limit($plan->description, 150) }}</p>
                                                        @endif
                                                        
                                                        @if($showPrograms)
                                                            {{-- Programs are typically loaded via API in Yii2 --}}
                                                            {{-- For now, we'll show plan type as a badge --}}
                                                            <div class="mb-4">
                                                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                    {{ $plan->type_label }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                                        <div class="text-center mb-4">
                                                            <h4 class="text-3xl font-bold text-gray-900">
                                                                {{ number_format($plan->price, 2) }} {{ $plan->currency }}
                                                            </h4>
                                                        </div>
                                                        
                                                        @php
                                                            // Check if plan can be sold online - check portal settings
                                                            // For now, default to true - you can add logic to check portal access settings
                                                            $canSellOnline = true;
                                                            $planUuid = $plan->uuid ?? $plan->id;
                                                        @endphp
                                                        
                                                        @if($canSellOnline)
                                                            <a href="/org-plan/index?plan={{ $planUuid }}" 
                                                               class="block w-full text-center bg-gray-900 hover:bg-gray-800 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                                                {{ $buyButtonText }}
                                                            </a>
                                                        @else
                                                            <button class="block w-full text-center bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg cursor-not-allowed">
                                                                {{ $purchaseAtGymText }}
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                                </div>
                                            @endif
                                    @else
                                        @if($isMeditative)
                                        <div class="text-center py-12">
                                            <p class="text-gray-600">No packages available at this time.</p>
                                        </div>
                                        @else
                                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                                            <p class="text-gray-600">No packages available at this time.</p>
                                        </div>
                                        @endif
                                    @endif
                                    @break

                                @case('coaches')
                                    @php
                                        // Get coaches block data
                                        $coachesData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($coachesData)) {
                                            $coachesData = [];
                                        }
                                        
                                        // Get organization ID from page or default
                                        $orgId = $page->org_id ?? env('CMS_DEFAULT_ORG_ID', 8);
                                        
                                        // Fetch coaches (OrgUser with isOnRoster = true)
                                        $coaches = \App\Models\OrgUser::where('org_id', $orgId)
                                            ->where('isOnRoster', true)
                                            ->where('isDeleted', false)
                                            ->orderBy('fullName', 'asc')
                                            ->get();
                                        
                                        $layout = $coachesData['layout'] ?? 'grid';
                                        $columns = $coachesData['columns'] ?? 3;
                                        $showPhoto = $coachesData['show_photo'] ?? true;
                                        $showBio = $coachesData['show_bio'] ?? true;
                                        $viewProfileText = $coachesData['view_profile_text'] ?? 'View Profile';
                                        
                                        $gridCols = match($columns) {
                                            2 => 'md:grid-cols-2',
                                            4 => 'md:grid-cols-4',
                                            default => 'md:grid-cols-3'
                                        };
                                    @endphp
                                    
                                    @if($section->title)
                                        @if($isMeditative)
                                            <div class="row justify-content-center mb-5 pb-3">
                                                <div class="col-md-12 heading-section ftco-animate text-center">
                                                    <h2 class="mb-1">{{ $section->title }}</h2>
                                                    @if($section->subtitle)
                                                    <p class="text-gray-600">{{ $section->subtitle }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 text-center">{{ $section->title }}</h2>
                                            @if($section->subtitle)
                                            <p class="text-xl text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
                                            @endif
                                        @endif
                                    @endif
                                    
                                    @if($coaches->count() > 0)
                                        @if($isMeditative)
                                            {{-- Meditative Template: Use coach cards --}}
                                            <div class="row">
                                                @foreach($coaches as $coach)
                                                    <div class="col-lg-{{ 12 / $columns }} d-flex ftco-animate">
                                                        <div class="coach align-items-stretch">
                                                            @if($showPhoto)
                                                                @php
                                                                    $photoUrl = null;
                                                                    if ($coach->photoFilePath) {
                                                                        if (filter_var($coach->photoFilePath, FILTER_VALIDATE_URL)) {
                                                                            $photoUrl = $coach->photoFilePath;
                                                                        } elseif (file_exists(public_path('storage/' . $coach->photoFilePath))) {
                                                                            $photoUrl = asset('storage/' . $coach->photoFilePath);
                                                                        } elseif (file_exists(public_path($coach->photoFilePath))) {
                                                                            $photoUrl = asset($coach->photoFilePath);
                                                                        }
                                                                    }
                                                                @endphp
                                                                <div class="img" style="background-image: url({{ $photoUrl ?: asset('images/trainer-1.jpg') }});"></div>
                                                            @endif
                                                            <div class="text bg-white p-4 ftco-animate">
                                                                <span class="subheading">Coach</span>
                                                                <h3><a href="/coach/view?id={{ $coach->uuid ?? $coach->id }}">{{ $coach->fullName }}</a></h3>
                                                                @if($showBio)
                                                                    @php
                                                                        $bio = null;
                                                                        try {
                                                                            if (method_exists($coach, 'orgUserProfileCoach') && $coach->orgUserProfileCoach) {
                                                                                $bio = $coach->orgUserProfileCoach->bio ?? null;
                                                                            }
                                                                        } catch (\Exception $e) {}
                                                                    @endphp
                                                                    @if($bio)
                                                                    <p>{{ Str::limit($bio, 150) }}</p>
                                                                    @endif
                                                                @endif
                                                                <p><a href="/coach/view?id={{ $coach->uuid ?? $coach->id }}" class="btn btn-white px-4 py-3">{{ $viewProfileText }} <span class="ion-ios-arrow-round-forward"></span></a></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            @if($layout === 'grid')
                                            <div class="grid grid-cols-1 {{ $gridCols }} gap-6">
                                                @foreach($coaches as $coach)
                                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow">
                                                        @if($showPhoto)
                                                            @php
                                                                // Handle different photo path formats
                                                                $photoUrl = null;
                                                                if ($coach->photoFilePath) {
                                                                    // Check if it's already a full URL
                                                                    if (filter_var($coach->photoFilePath, FILTER_VALIDATE_URL)) {
                                                                        $photoUrl = $coach->photoFilePath;
                                                                    } elseif (file_exists(public_path('storage/' . $coach->photoFilePath))) {
                                                                        $photoUrl = asset('storage/' . $coach->photoFilePath);
                                                                    } elseif (file_exists(storage_path('app/public/' . $coach->photoFilePath))) {
                                                                        $photoUrl = asset('storage/' . $coach->photoFilePath);
                                                                    } elseif (file_exists(public_path($coach->photoFilePath))) {
                                                                        $photoUrl = asset($coach->photoFilePath);
                                                                    }
                                                                }
                                                            @endphp
                                                            
                                                            @if($photoUrl)
                                                                <div class="w-full h-64 bg-gray-200 overflow-hidden">
                                                                    <img src="{{ $photoUrl }}" 
                                                                         alt="{{ $coach->fullName }}" 
                                                                         class="w-full h-full object-cover">
                                                                </div>
                                                            @else
                                                                <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                                                                    <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        @endif
                                                        
                                                        <div class="p-6">
                                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $coach->fullName }}</h3>
                                                            
                                                            @if($showBio)
                                                                @php
                                                                    // Try to get bio from coach profile if relationship exists
                                                                    $bio = null;
                                                                    try {
                                                                        if (method_exists($coach, 'orgUserProfileCoach') && $coach->orgUserProfileCoach) {
                                                                            $bio = $coach->orgUserProfileCoach->bio ?? null;
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        // Relationship doesn't exist, skip bio
                                                                    }
                                                                @endphp
                                                                @if($bio)
                                                                    <p class="text-gray-700 mb-4 text-sm line-clamp-3">
                                                                        {{ Str::limit($bio, 200) }}
                                                                    </p>
                                                                @endif
                                                            @endif
                                                            
                                                            <a href="/coach/view?id={{ $coach->uuid ?? $coach->id }}" 
                                                               class="inline-block w-full text-center bg-gray-900 hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                                                {{ $viewProfileText }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @else
                                                {{-- List Layout (horizontal cards like Yii2) --}}
                                                <div class="space-y-4">
                                                @foreach($coaches as $coach)
                                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow">
                                                        <div class="flex flex-col md:flex-row">
                                                            @if($showPhoto)
                                                                @php
                                                                    // Handle different photo path formats
                                                                    $photoUrl = null;
                                                                    if ($coach->photoFilePath) {
                                                                        if (filter_var($coach->photoFilePath, FILTER_VALIDATE_URL)) {
                                                                            $photoUrl = $coach->photoFilePath;
                                                                        } elseif (file_exists(public_path('storage/' . $coach->photoFilePath))) {
                                                                            $photoUrl = asset('storage/' . $coach->photoFilePath);
                                                                        } elseif (file_exists(storage_path('app/public/' . $coach->photoFilePath))) {
                                                                            $photoUrl = asset('storage/' . $coach->photoFilePath);
                                                                        } elseif (file_exists(public_path($coach->photoFilePath))) {
                                                                            $photoUrl = asset($coach->photoFilePath);
                                                                        }
                                                                    }
                                                                @endphp
                                                                
                                                                <div class="w-full md:w-1/4 h-64 md:h-auto bg-gray-200 overflow-hidden">
                                                                    @if($photoUrl)
                                                                        <img src="{{ $photoUrl }}" 
                                                                             alt="{{ $coach->fullName }}" 
                                                                             class="w-full h-full object-cover">
                                                                    @else
                                                                        <div class="w-full h-full flex items-center justify-center">
                                                                            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            
                                                            <div class="flex-1 p-6">
                                                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $coach->fullName }}</h3>
                                                                
                                                                @if($showBio)
                                                                    @php
                                                                        // Try to get bio from coach profile if relationship exists
                                                                        $bio = null;
                                                                        try {
                                                                            if (method_exists($coach, 'orgUserProfileCoach') && $coach->orgUserProfileCoach) {
                                                                                $bio = $coach->orgUserProfileCoach->bio ?? null;
                                                                            }
                                                                        } catch (\Exception $e) {
                                                                            // Relationship doesn't exist, skip bio
                                                                        }
                                                                    @endphp
                                                                    @if($bio)
                                                                        <p class="text-gray-700 mb-4">
                                                                            {{ Str::limit($bio, 420) }}
                                                                        </p>
                                                                    @endif
                                                                @endif
                                                                
                                                                <a href="/coach/view?id={{ $coach->uuid ?? $coach->id }}" 
                                                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                                                                    {{ $viewProfileText }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        @if($isMeditative)
                                        <div class="text-center py-12">
                                            <p class="text-gray-600">No coaches available at this time.</p>
                                        </div>
                                        @else
                                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                                            <p class="text-gray-600">No coaches available at this time.</p>
                                        </div>
                                        @endif
                                    @endif
                                    @break

                                @case('schedule')
                                    @php
                                        // Get schedule block data
                                        $scheduleData = is_string($section->data) ? json_decode($section->data, true) : ($section->data ?? []);
                                        if (!is_array($scheduleData)) {
                                            $scheduleData = [];
                                        }
                                        
                                        // Get organization ID from page or default
                                        $orgId = $page->org_id ?? env('CMS_DEFAULT_ORG_ID', 8);
                                        
                                        // Determine the date to show - check URL parameter first, then block settings
                                        $requestDate = request()->get('date');
                                        $defaultDate = $scheduleData['default_date'] ?? 'today';
                                        $daysToShow = (int)($scheduleData['days_to_show'] ?? 1);
                                        
                                        // Use URL parameter if provided, otherwise use block default
                                        if ($requestDate) {
                                            if ($requestDate === 'now') {
                                                $startDate = now();
                                            } else {
                                                try {
                                                    $startDate = \Carbon\Carbon::parse($requestDate);
                                                } catch (\Exception $e) {
                                                    $startDate = now();
                                                }
                                            }
                                        } elseif ($defaultDate === 'today') {
                                            $startDate = now();
                                        } elseif ($defaultDate === 'tomorrow') {
                                            $startDate = now()->addDay();
                                        } else {
                                            try {
                                                $startDate = \Carbon\Carbon::parse($defaultDate);
                                            } catch (\Exception $e) {
                                                $startDate = now();
                                            }
                                        }
                                        
                                        $endDate = $startDate->copy()->addDays($daysToShow - 1);
                                        
                                        // Fetch events for the date range
                                        $events = \App\Models\Event::where('org_id', $orgId)
                                            ->where('isActive', true)
                                            ->where('isCanceled', false)
                                            ->where('isDeleted', false)
                                            ->whereBetween('startDateTimeLoc', [
                                                $startDate->copy()->startOfDay()->toDateTimeString(),
                                                $endDate->copy()->endOfDay()->toDateTimeString()
                                            ])
                                            ->with(['program'])
                                            ->orderBy('startDateTimeLoc', 'asc')
                                            ->get();
                                        
                                        // Group events by date
                                        $eventsByDate = $events->groupBy(function($event) {
                                            return $event->startDateTimeLoc ? $event->startDateTimeLoc->format('Y-m-d') : 'unknown';
                                        });
                                        
                                        $showDateNavigation = $scheduleData['show_date_navigation'] ?? true;
                                        $showDropInButton = $scheduleData['show_drop_in_button'] ?? true;
                                        $dropInText = $scheduleData['drop_in_text'] ?? 'Drop In';
                                        
                                        // Calculate previous and next dates
                                        $prevDate = $startDate->copy()->subDay()->format('Y-m-d');
                                        $nextDate = $endDate->copy()->addDay()->format('Y-m-d');
                                    @endphp
                                    
                                    @if($section->title)
                                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 text-center">{{ $section->title }}</h2>
                                    @endif
                                    
                                    @if($section->subtitle)
                                        <p class="text-xl text-gray-600 mb-8 text-center">{{ $section->subtitle }}</p>
                                    @endif
                                    
                                    @if($showDateNavigation)
                                        @php
                                            $currentUrl = request()->url();
                                            $urlParams = request()->except('date');
                                            $baseUrl = $currentUrl . (count($urlParams) > 0 ? '?' . http_build_query($urlParams) . '&' : '?');
                                        @endphp
                                        <div class="flex justify-center items-center gap-4 mb-8">
                                            <a href="{{ $baseUrl }}date={{ $prevDate }}" 
                                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                                <span class="mr-1">←</span> Previous
                                            </a>
                                            <a href="{{ $baseUrl }}date=now" 
                                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                                Today
                                            </a>
                                            <a href="{{ $baseUrl }}date={{ $nextDate }}" 
                                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                                Next <span class="ml-1">→</span>
                                            </a>
                                        </div>
                                    @endif
                                    
                                    @if($eventsByDate->count() > 0)
                                        @foreach($eventsByDate as $date => $dateEvents)
                                            @php
                                                $dateObj = \Carbon\Carbon::parse($date);
                                                $isToday = $dateObj->isToday();
                                                $dateString = $isToday ? 'Today' : $dateObj->format('l, M j');
                                            @endphp
                                            
                                            <div class="mb-8">
                                                <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">
                                                    {{ $dateString }}
                                                    @if($isToday)
                                                        <span class="text-sm font-normal text-gray-600 ml-2">({{ $dateObj->format('M j') }})</span>
                                                    @endif
                                                </h3>
                                                
                                                <div class="space-y-3">
                                                    @foreach($dateEvents as $event)
                                                        @php
                                                            $program = $event->program;
                                                            $programColor = $program->color ?? '#3b82f6';
                                                            $startTime = $event->startDateTimeLoc ? $event->startDateTimeLoc->format('g:i A') : '';
                                                            $endTime = $event->endDateTimeLoc ? $event->endDateTimeLoc->format('g:i A') : '';
                                                            $timeText = $startTime && $endTime ? "{$startTime} - {$endTime}" : ($startTime ? $startTime : '');
                                                            $eventName = $event->name ?? ($program ? $program->name : 'Event');
                                                        @endphp
                                                        
                                                        <div class="bg-white rounded-lg border-l-4 shadow-md hover:shadow-lg transition-shadow cursor-pointer"
                                                             style="border-left-color: {{ $programColor }};"
                                                             onclick="window.location.href='/event/view?id={{ $event->uuid ?? $event->id }}'">
                                                            <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                                <div class="flex-1">
                                                                    <h4 class="text-lg font-semibold text-gray-900 mb-1">
                                                                        <a href="/event/view?id={{ $event->uuid ?? $event->id }}" 
                                                                           class="hover:text-blue-600 transition-colors">
                                                                            {{ $eventName }}
                                                                        </a>
                                                                    </h4>
                                                                    @if($timeText)
                                                                        <p class="text-gray-600 text-sm">{{ $timeText }}</p>
                                                                    @endif
                                                                </div>
                                                                
                                                                @if($showDropInButton)
                                                                    <div class="flex-shrink-0">
                                                                        <a href="/event/view?id={{ $event->uuid ?? $event->id }}" 
                                                                           class="inline-block px-4 py-2 border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition-colors font-medium">
                                                                            {{ $dropInText }}
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                                            <p class="text-gray-600">No events scheduled for this date.</p>
                                        </div>
                                    @endif
                                    
                                    @if($showDateNavigation && $eventsByDate->count() > 0)
                                        @php
                                            $currentUrl = request()->url();
                                            $urlParams = request()->except('date');
                                            $baseUrl = $currentUrl . (count($urlParams) > 0 ? '?' . http_build_query($urlParams) . '&' : '?');
                                        @endphp
                                        <div class="flex justify-center items-center gap-4 mt-8">
                                            <a href="{{ $baseUrl }}date={{ $prevDate }}" 
                                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                                <span class="mr-1">←</span> Previous
                                            </a>
                                            <a href="{{ $baseUrl }}date=now" 
                                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                                Today
                                            </a>
                                            <a href="{{ $baseUrl }}date={{ $nextDate }}" 
                                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                                Next <span class="ml-1">→</span>
                                            </a>
                                        </div>
                                    @endif
                                    @break

                                @default
                                    {{-- Default content section --}}
                                    <div class="default-section py-8">
                                        <div class="container mx-auto px-6">
                                            @if($section->title)
                                                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $section->title }}</h3>
                                            @endif
                                            @if($section->subtitle)
                                                <p class="text-lg text-gray-600 mb-4">{{ $section->subtitle }}</p>
                                            @endif
                                            @if($section->content)
                                                <div class="prose max-w-none">
                                                    {!! $section->content !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                            @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Fallback: Display page content as a section if no sections exist --}}
                <div class="cms-sections">
                    <div class="cms-section cms-section-content {{ $isMeditative ? 'ftco-section ftco-animate' : 'py-16' }}">
                        <div class="{{ $isMeditative ? 'container' : 'container mx-auto px-6' }}">
                            @if($page->title && !$this->hasHeroSection())
                                <div class="mb-8">
                                    @if($isMeditative)
                                        <div class="row justify-content-center mb-5 pb-3">
                                            <div class="col-md-12 heading-section ftco-animate text-center">
                                                <h1 class="mb-1">{{ $page->title }}</h1>
                                                @if($page->description)
                                                <p class="text-gray-600">{{ $page->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 text-center">{{ $page->title }}</h1>
                                        @if($page->description)
                                            <p class="text-xl text-gray-600 max-w-3xl mx-auto text-center">{{ $page->description }}</p>
                                        @endif
                                    @endif
                                </div>
                            @endif
                            
                            @if($page->content)
                                @if($isMeditative)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="text-gray-700">
                                                {!! $page->content !!}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="prose prose-lg max-w-none">
                                        {!! $page->content !!}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-16">
                                    <div class="{{ $isMeditative ? 'col-md-12' : 'max-w-2xl mx-auto' }}">
                                        <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Page Content Coming Soon</h2>
                                        <p class="text-lg text-gray-600 mb-8">This page is currently being updated. Please check back soon.</p>
                                        <a href="/cms-admin/pages/{{ $page->id }}/edit" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                                            Edit This Page
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Add some basic styling --}}
            <style>
                .cms-page {
                    min-height: 50vh;
                }
                
                .container {
                    max-width: 1200px;
                }
                
                .prose {
                    color: #374151;
                    line-height: 1.75;
                }
                
                .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
                    color: #111827;
                    font-weight: 600;
                }
                
                .prose p {
                    margin-bottom: 1.25em;
                }
                
                .prose ul, .prose ol {
                    margin: 1.25em 0;
                    padding-left: 1.625em;
                }
                
                .prose li {
                    margin: 0.5em 0;
                }
                
                .prose a {
                    color: #2563eb;
                    text-decoration: underline;
                }
                
                .prose a:hover {
                    color: #1d4ed8;
                }
            </style>
        </div>

    @else
        {{-- Page not found --}}
        <div class="min-h-screen flex items-center justify-center bg-gray-50">
            <div class="text-center">
                <div class="mb-8">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Page Not Found</h1>
                <p class="text-xl text-gray-600 mb-8">The requested page "{{ $slug }}" could not be found.</p>
                <div class="space-x-4">
                    <a href="/" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                        Go Home
                    </a>
                    <a href="/cms-admin/pages" class="inline-block border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition">
                        Manage Pages
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>