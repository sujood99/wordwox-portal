{{-- Contact Form Section Partial --}}
@if($isFitness)
    @php
        $contactSettings = is_string($section->settings) ? json_decode($section->settings, true) : ($section->settings ?? []);
        $contactTitleFontSize = $contactSettings['title_font_size'] ?? '';
        $contactSubtitleFontSize = $contactSettings['subtitle_font_size'] ?? '';
        $contactTitleStyle = '';
        $contactSubtitleStyle = '';
        if (!empty($contactTitleFontSize)) {
            $numericValue = is_numeric($contactTitleFontSize) ? (float) $contactTitleFontSize : (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', $contactTitleFontSize, $matches) ? (float) $matches[1] : null);
            if ($numericValue && $numericValue >= 1) {
                $contactTitleStyle = 'font-size: ' . $numericValue . 'px;';
            }
        }
        if (!empty($contactSubtitleFontSize)) {
            $numericValue = is_numeric($contactSubtitleFontSize) ? (float) $contactSubtitleFontSize : (preg_match('/^([0-9]+(?:\.[0-9]+)?)/', $contactSubtitleFontSize, $matches) ? (float) $matches[1] : null);
            if ($numericValue && $numericValue >= 1) {
                $contactSubtitleStyle = 'font-size: ' . $numericValue . 'px;';
            }
        }
    @endphp
    <div class="container my-4 my-md-5">
        <div class="row">
            {{-- Contact Information --}}
            @if($showContactInfo && ($orgContact['email'] || $orgContact['phone'] || $orgContact['address']))
                <div class="col-12 col-md-12 col-lg-4 mb-4 mb-lg-5 order-2 order-lg-1">
                    <div class="card h-100 border-0 shadow-sm contact-info-card-fitness">
                        <div class="card-body p-4">
                            <h3 class="h5 mb-4 contact-info-title-fitness">
                                <i class="fas fa-map-marker-alt me-2"></i>Contact Information
                            </h3>
                            @if($orgContact['address'])
                                <div class="mb-3 contact-info-item-fitness">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <span>{{ $orgContact['address'] }}</span>
                                </div>
                            @endif
                            @if($orgContact['phone'])
                                <div class="mb-3 contact-info-item-fitness">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:{{ $orgContact['phone'] }}" class="contact-info-link-fitness">{{ $orgContact['phone'] }}</a>
                                </div>
                            @endif
                            @if($orgContact['email'])
                                <div class="mb-3 contact-info-item-fitness">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:{{ $orgContact['email'] }}" class="contact-info-link-fitness">{{ $orgContact['email'] }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-8 order-1 order-lg-2">
            @else
                <div class="col-12 col-lg-8 mx-auto">
            @endif
                @if($section->title)
                <h2 class="section-heading text-center mb-3 mb-md-4" style="{{ $contactTitleStyle }}">{{ $section->title }}</h2>
                @endif
                @if($section->subtitle)
                <p class="text-center mb-4 mb-md-5 text-muted contact-subtitle-fitness" style="{{ $contactSubtitleStyle }}">{{ $section->subtitle }}</p>
                @endif
                @if($section->content)
                <div class="text-center mb-4 mb-md-5 contact-content-fitness">{!! $section->content !!}</div>
                @endif

                <!-- Dynamic Contact Form -->
                <div class="card shadow-lg border-0 contact-form-card-fitness">
                    <div class="card-body p-4 p-md-5">
                        @include('partials.cms.sections.contact-form-traditional', ['page' => $page])
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif($isMeditative)
    <section class="ftco-section contact-section ftco-degree-bg">
        <div class="container">
            <div class="row d-flex mb-4 mb-md-5 contact-info">
                <div class="col-12 col-md-12 mb-3 mb-md-4">
                    @if($section->title)
                    <h2 class="h4 h5-md">{{ $section->title }}</h2>
                    @endif
                    @if($section->subtitle)
                    <p class="mb-2 mb-md-3">{{ $section->subtitle }}</p>
                    @endif
                    @if($section->content)
                    <div>{!! $section->content !!}</div>
                    @endif
                </div>
            </div>
            <div class="row block-9">
                <div class="col-12 col-md-6 mb-4 mb-md-0 pr-md-5">
                    {{-- Dynamic Contact Form --}}
                    <div class="bg-light p-4 p-md-5 contact-form">
                        @include('partials.cms.sections.contact-form-traditional', ['page' => $page])
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    {{-- Contact Information --}}
                    @if($showContactInfo && ($orgContact['email'] || $orgContact['phone'] || $orgContact['address']))
                        <div class="contact-info bg-primary p-4 p-md-5 h-100">
                            <h3 class="h4 h5-md text-white mb-3 mb-md-4">Contact Information</h3>
                            @if($orgContact['address'])
                                <p class="text-white-50 mb-2 mb-md-3">
                                    <span class="text-white font-weight-bold">Address:</span><br>
                                    {{ $orgContact['address'] }}
                                </p>
                            @endif
                            @if($orgContact['phone'])
                                <p class="text-white-50 mb-2 mb-md-3">
                                    <span class="text-white font-weight-bold">Phone:</span><br>
                                    <a href="tel:{{ $orgContact['phone'] }}" class="text-white">{{ $orgContact['phone'] }}</a>
                                </p>
                            @endif
                            @if($orgContact['email'])
                                <p class="text-white-50 mb-2 mb-md-3">
                                    <span class="text-white font-weight-bold">Email:</span><br>
                                    <a href="mailto:{{ $orgContact['email'] }}" class="text-white">{{ $orgContact['email'] }}</a>
                                </p>
                            @endif
                        </div>
                    @else
                        <div id="map" style="min-height: 300px; height: 100%;"></div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@else
    {{-- Default Contact Form for Modern Template --}}
    <div class="max-w-6xl mx-auto">
        @if($section->title)
        <h2 class="text-3xl font-bold text-center mb-8">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
        <p class="text-xl text-center mb-8 text-gray-600">{{ $section->subtitle }}</p>
        @endif
        @if($section->content)
        <div class="text-center mb-8">{!! $section->content !!}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Contact Information --}}
            @if($showContactInfo && ($orgContact['email'] || $orgContact['phone'] || $orgContact['address']))
                <div class="lg:col-span-1">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-8 text-white h-full">
                        <h3 class="text-2xl font-bold mb-6">Contact Information</h3>
                        @if($orgContact['address'])
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-xl mr-3"></i>
                                    <span class="font-semibold">Address</span>
                                </div>
                                <p class="text-indigo-100 ml-8">{{ $orgContact['address'] }}</p>
                            </div>
                        @endif
                        @if($orgContact['phone'])
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-phone text-xl mr-3"></i>
                                    <span class="font-semibold">Phone</span>
                                </div>
                                <a href="tel:{{ $orgContact['phone'] }}" class="text-indigo-100 hover:text-white ml-8">{{ $orgContact['phone'] }}</a>
                            </div>
                        @endif
                        @if($orgContact['email'])
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-envelope text-xl mr-3"></i>
                                    <span class="font-semibold">Email</span>
                                </div>
                                <a href="mailto:{{ $orgContact['email'] }}" class="text-indigo-100 hover:text-white ml-8">{{ $orgContact['email'] }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="lg:col-span-2">
            @else
                <div class="lg:col-span-3">
            @endif
                {{-- Dynamic Contact Form --}}
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    @include('partials.cms.sections.contact-form-traditional', ['page' => $page])
                </div>
            </div>
        </div>
    </div>
@endif