<div>
    {{-- Success and Error Messages --}}
    @if($showSuccessMessage)
        @if($template === 'meditative')
        <div class="alert alert-success mb-4">
            <i class="fa fa-check-circle"></i>
            Thank you for your message! We'll get back to you soon.
        </div>
        @elseif($template === 'fitness')
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle text-success me-2"></i>
            Thank you for your message! We'll get back to you soon.
        </div>
        @else
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800">
                        Thank you for your message! We'll get back to you soon.
                    </p>
                </div>
            </div>
        </div>
        @endif
    @endif

    @if(session('error'))
        @if($template === 'meditative')
        <div class="alert alert-danger mb-4">
            <i class="fa fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
        @elseif($template === 'fitness')
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle text-danger me-2"></i>
            {{ session('error') }}
        </div>
        @else
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- Template-specific Form Styling --}}
    @if($template === 'meditative')
        {{-- Meditative Template Form --}}
        <form wire:submit.prevent="submit">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Name *</label>
                        <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Your Name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email *</label>
                        <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Your Email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input wire:model="phone" type="tel" class="form-control" placeholder="Your Phone">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subject</label>
                        <input wire:model="subject" type="text" class="form-control" placeholder="Subject">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea wire:model="message" cols="30" rows="6" class="form-control @error('message') is-invalid @enderror" placeholder="Message"></textarea>
                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <button type="submit" wire:loading.attr="disabled" class="btn btn-primary py-3 px-5">
                            <span wire:loading.remove>Send Message</span>
                            <span wire:loading>Sending...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
    @elseif($template === 'fitness')
        {{-- Fitness Template Form --}}
        <form wire:submit.prevent="submit">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name *</label>
                        <input wire:model="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" placeholder="Your Name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email *</label>
                        <input wire:model="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" placeholder="Your Email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input wire:model="phone" type="tel" class="form-control form-control-lg" placeholder="Your Phone">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject</label>
                        <input wire:model="subject" type="text" class="form-control form-control-lg" placeholder="Subject">
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message *</label>
                        <textarea wire:model="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Your Message"></textarea>
                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" wire:loading.attr="disabled" class="btn-fitness w-100 py-3">
                        <span wire:loading.remove>
                            <i class="fas fa-paper-plane me-2"></i>
                            Send Message
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Sending...
                        </span>
                    </button>
                </div>
            </div>
        </form>
        
    @else
        {{-- Modern Template Form (Default) --}}
        <form wire:submit.prevent="submit" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input wire:model="name" type="text" id="name" name="name" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="Your Name">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input wire:model="email" type="email" id="email" name="email" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="your@email.com">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input wire:model="phone" type="tel" id="phone" name="phone" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Your Phone">
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input wire:model="subject" type="text" id="subject" name="subject" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Subject">
                </div>
            </div>
            
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                <textarea wire:model="message" id="message" name="message" rows="5" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-500 @enderror"
                          placeholder="Your message..."></textarea>
                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 text-white font-bold py-4 px-8 rounded-lg transition-all transform hover:scale-105">
                <span wire:loading.remove>🚀 Send Message</span>
                <span wire:loading>⚡ Sending...</span>
            </button>
        </form>
    @endif
</div>