{{-- Page Header Partial --}}
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
    @elseif($isFitness)
        {{-- Fitness Template Hero Header - Removed --}}
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