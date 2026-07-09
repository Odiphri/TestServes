<form class="platform-card p-3 mb-3" method="POST" action="{{ route('super-admin.subscription-plans.ai-draft') }}">
    @csrf
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h2 class="h5 mb-1">AI Plan Builder</h2>
            <p class="text-muted mb-0">Describe the package you want. Gemini will draft the name, pricing, trial days, and included app features for review.</p>
        </div>
        <button class="btn btn-primary align-self-lg-start" type="submit">Generate draft</button>
    </div>
    <label class="form-label mt-3" for="prompt">Prompt</label>
    <textarea class="form-control" id="prompt" name="prompt" rows="3" placeholder="Example: Create a recommended growth plan for medium secondary schools with CBT, attendance, bursary, branding, and support. Price it around 35,000 monthly.">{{ old('prompt') }}</textarea>
    @error('prompt')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</form>
