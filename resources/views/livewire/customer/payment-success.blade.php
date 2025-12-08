<div>
    @if($plan)
        <!-- Centered Success Block with Green Background -->
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card shadow-lg border-0" style="background-color: #d4edda;">
                        <div class="card-body text-center p-5">
                            <!-- Success Message -->
                            <div class="mb-4">
                                <h4 class="fw-bold mb-0" style="color: #000000; font-size: 1.25rem;">
                                    Success! Thank you for your Payment.
                                </h4>
                            </div>
                            
                            <hr class="my-4" style="border-color: #000000; opacity: 0.2;">
                            
                            <!-- New Plan Heading -->
                            <h2 class="fw-bold mb-4" style="color: #000000; font-size: 1.75rem;">New Plan</h2>
                            
                            <!-- Plan Name -->
                            <h3 class="mb-4" style="color: #000000; font-size: 1.5rem; font-weight: 600;">{{ $plan->name }}</h3>
                            
                            <!-- Organization Name -->
                            @if(Auth::user() && Auth::user()->orgUser && Auth::user()->orgUser->org)
                                <p class="mb-0" style="color: #000000; font-size: 1rem;">{{ Auth::user()->orgUser->org->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @if($error)
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Payment Failed
                            </h4>
                            <p class="mb-3">{{ $error }}</p>
                            <hr>
                            <p class="mb-3">If the issue persists, please contact support.</p>
                            <div class="mt-3">
                                <a href="{{ route('home') }}" class="btn btn-primary">
                                    <i class="fas fa-home me-1"></i>Return to Home
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">Payment Information Not Found</h4>
                            <p>We couldn't find the payment details. If you just completed a payment, please wait a moment.</p>
                            <hr>
                            <p class="mb-0">The payment may still be processing. Please wait a few moments or contact support.</p>
                            <div class="mt-3">
                                <a href="{{ route('home') }}" class="btn btn-primary">Return to Home</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

