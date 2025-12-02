{{-- Hero Section Partial --}}
@if($section->type === 'hero' && ($isMeditative || $isFitness))
    {{-- Hero sections don't need the container wrapper --}}
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
    @elseif($isFitness)
        {{-- Fitness Template Hero Section --}}
        @php
            $settings = is_string($section->settings) ? json_decode($section->settings, true) : ($section->settings ?? []);
            // Use CSS variables for default colors, with fallbacks
            $bgColor = $settings['background_color'] ?? 'var(--fitness-primary, #ff6b6b)';
            $textColor = $settings['text_color'] ?? 'var(--fitness-text-light, #ffffff)';
            
            // Height settings - now direct number input
            $height = $settings['height'] ?? ($settings['custom_height'] ?? '500');
            $heightValue = is_numeric($height) ? max(1, intval($height)) : 500;
            $minHeightStyle = 'min-height: ' . $heightValue . 'px;';
            
            // Font size settings - now direct number input (px only)
            $titleFontSize = $settings['title_font_size'] ?? ($settings['custom_title_font_size'] ?? '');
            $titleStyle = '';
            if (!empty($titleFontSize)) {
                // Extract numeric value and add px if needed
                $numericValue = 0;
                if (is_numeric($titleFontSize)) {
                    $numericValue = max(1, (float) $titleFontSize);
                    $titleStyle = 'font-size: ' . $numericValue . 'px;';
                } elseif (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', $titleFontSize, $matches)) {
                    $numericValue = max(1, (float) $matches[1]);
                    $titleStyle = 'font-size: ' . $numericValue . 'px;';
                }
            }
            
            $subtitleFontSize = $settings['subtitle_font_size'] ?? ($settings['custom_subtitle_font_size'] ?? '');
            $subtitleStyle = '';
            if (!empty($subtitleFontSize)) {
                // Extract numeric value and add px if needed
                $numericValue = 0;
                if (is_numeric($subtitleFontSize)) {
                    $numericValue = max(1, (float) $subtitleFontSize);
                    $subtitleStyle = 'font-size: ' . $numericValue . 'px;';
                } elseif (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', $subtitleFontSize, $matches)) {
                    $numericValue = max(1, (float) $matches[1]);
                    $subtitleStyle = 'font-size: ' . $numericValue . 'px;';
                }
            }
            
            $contentFontSize = $settings['content_font_size'] ?? '';
            $contentStyle = '';
            if (!empty($contentFontSize)) {
                $numericValue = 0;
                if (is_numeric($contentFontSize)) {
                    $numericValue = max(1, (float) $contentFontSize);
                    $contentStyle = 'font-size: ' . $numericValue . 'px;';
                } elseif (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', $contentFontSize, $matches)) {
                    $numericValue = max(1, (float) $matches[1]);
                    $contentStyle = 'font-size: ' . $numericValue . 'px;';
                }
            }
            
            // Use gradient as default if no custom background is set
            $useGradient = !isset($settings['background_color']) || $settings['background_color'] === '#ff6b6b' || $settings['background_color'] === 'var(--fitness-primary, #ff6b6b)';
            $backgroundStyle = $useGradient 
                ? 'background: var(--fitness-gradient, linear-gradient(135deg, #ff6b6b 0%, #4ecdc4 100%));' 
                : 'background-color: ' . $bgColor . ';';
        @endphp
        <section class="hero-section-custom" style="{{ $backgroundStyle }} color: {{ $textColor }}; {{ $minHeightStyle }}">
            <div class="container">
                <div class="row align-items-center justify-content-center text-center hero-content-row" style="{{ $minHeightStyle }}">
                    <div class="col-12 col-md-10 col-lg-10">
                        @if($section->title)
                        <h1 class="display-2 fw-bold mb-3 mb-md-4 hero-title-custom" style="color: {{ $textColor }}; {{ $titleStyle }}">{{ $section->title }}</h1>
                        @endif
                        @if($section->subtitle)
                        <h3 class="mb-3 mb-md-4 hero-subtitle-custom" style="color: {{ $textColor }}; {{ $subtitleStyle }}">{{ $section->subtitle }}</h3>
                        @endif
                        @if($section->content)
                        <div class="lead mb-4 mb-md-5" style="color: {{ $textColor }}; {{ $contentStyle }}">{!! $section->content !!}</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif
@else
    {{-- Default hero section for other templates --}}
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