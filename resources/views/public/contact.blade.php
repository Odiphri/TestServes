<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TestServes</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin: 0; background: #f6f9fc; color: #102033; font-family: Inter, system-ui, sans-serif; }
        .public-wrap { width: min(1080px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 48px; }
        .public-nav { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 28px; }
        .public-nav a { color: #0f766e; font-weight: 800; text-decoration: none; }
        .contact-grid { display: grid; grid-template-columns: .8fr 1.2fr; gap: 18px; align-items: start; }
        .public-card { background: #fff; border: 1px solid #dbe3ef; border-radius: 8px; padding: 24px; box-shadow: 0 12px 32px rgba(16,32,51,.06); }
        h1 { font-size: clamp(34px, 5vw, 54px); font-weight: 800; margin: 0 0 10px; }
        .muted { color: #607086; line-height: 1.7; }
        .contact-list { display: grid; gap: 10px; margin-top: 18px; }
        .contact-list a { color: #0f766e; font-weight: 800; text-decoration: none; }
        .btn-primary { background: #0f766e; border-color: #0f766e; font-weight: 800; }
        @media (max-width: 820px) { .contact-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="public-wrap">
        <nav class="public-nav">
            <a href="{{ route('platform.home') }}">TestServes</a>
            <a href="{{ route('platform.login') }}">Owner login</a>
        </nav>

        <div class="contact-grid">
            <section class="public-card">
                <h1>Contact Us</h1>
                <p class="muted">Reach TestServes support for onboarding, technical help, payments, partnerships, privacy requests, or general questions.</p>
                <div class="contact-list">
                    <a href="{{ route('contact') }}">Contact Us</a>
                    <a href="{{ $settings['whatsapp_support_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp Support</a>
                    <a href="{{ $settings['whatsapp_community_url'] }}" target="_blank" rel="noopener noreferrer">Join WhatsApp Community</a>
                    <a href="{{ $settings['x_url'] }}" target="_blank" rel="noopener noreferrer">X {{ $settings['x_handle'] }}</a>
                </div>
            </section>

            <section class="public-card">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger">Please check the highlighted fields and try again.</div>@endif
                <form method="POST" action="{{ route('contact.submit') }}">
                    @csrf
                    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-10000px;" aria-hidden="true">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="name">Name</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="school_name">School name</label><input class="form-control @error('school_name') is-invalid @enderror" id="school_name" name="school_name" value="{{ old('school_name') }}">@error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="category">Category</label><select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select>@error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="subject">Subject</label><input class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>@error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12"><label class="form-label" for="message">Message</label><textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="6" required>{{ old('message') }}</textarea>@error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" id="consent" name="consent" value="1" required @checked(old('consent'))><label class="form-check-label" for="consent">I consent to TestServes using this information to respond to my message.</label>@error('consent')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div></div>
                        <div class="col-12"><button class="btn btn-primary px-4" type="submit">Send message</button></div>
                    </div>
                </form>
            </section>
        </div>
    </main>
    @include('partials.public-footer')
    @include('partials.floating-whatsapp')
    @include('partials.cookie-notice')
</body>
</html>
