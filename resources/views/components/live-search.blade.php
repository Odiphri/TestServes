@props([
    'action',
    'target',
    'search' => '',
    'placeholder' => 'Search',
    'label' => 'Search',
    'clearHref' => null,
])

<div class="card live-search-card">
    <div class="card-body">
        <form method="GET" action="{{ $action }}" class="row g-2 align-items-end" data-auto-submit="true" data-live-search-target="{{ $target }}">
            <div class="col-12 col-md">
                <label class="form-label">{{ $label }}</label>
                <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="{{ $placeholder }}">
            </div>

            {{ $slot }}

            <div class="col-12 col-md-auto d-flex gap-2">
                <button class="btn btn-primary-custom flex-grow-1" type="submit">Search</button>
                <a href="{{ $clearHref ?? $action }}" class="btn btn-light" data-live-search-clear>Clear</a>
            </div>
        </form>
    </div>
</div>
