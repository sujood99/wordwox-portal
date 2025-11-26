{{-- Traditional Contact Form (Similar to Yii Project) --}}
@php
    $orgId = $page->org_id ?? session('org_id') ?? env('CMS_DEFAULT_ORG_ID', 8);
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form id="contact-form" action="{{ route('contact.submit') }}" method="post" class="contact-form">
    @csrf
    <input type="hidden" name="org_id" value="{{ $orgId }}">
    
    <div class="form-group field-contactform-name required mb-3">
        <label for="contactform-name" class="form-label">Name</label>
        <input type="text" 
               id="contactform-name" 
               class="form-control @error('name') is-invalid @enderror" 
               name="name" 
               value="{{ old('name') }}"
               autofocus 
               aria-required="true"
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group field-contactform-email required mb-3">
        <label for="contactform-email" class="form-label">Email</label>
        <input type="text" 
               id="contactform-email" 
               class="form-control @error('email') is-invalid @enderror" 
               name="email" 
               value="{{ old('email') }}"
               aria-required="true"
               required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group field-contactform-subject required mb-3">
        <label for="contactform-subject" class="form-label">Subject</label>
        <input type="text" 
               id="contactform-subject" 
               class="form-control @error('subject') is-invalid @enderror" 
               name="subject" 
               value="{{ old('subject') }}"
               aria-required="true"
               required>
        @error('subject')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group field-contactform-body required mb-3">
        <label for="contactform-body" class="form-label">Body</label>
        <textarea id="contactform-body" 
                  class="form-control @error('body') is-invalid @enderror" 
                  name="body" 
                  rows="6" 
                  aria-required="true"
                  required>{{ old('body') }}</textarea>
        @error('body')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Captcha can be added here if needed --}}
    {{-- For now, we'll skip captcha but you can add Google reCAPTCHA or similar --}}
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary" name="contact-button">Submit</button>
    </div>
</form>

