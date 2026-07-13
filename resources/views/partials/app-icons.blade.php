@php($appIconUrl = \App\Models\SystemSetting::platformLogoUrl() ?: asset('images/tslogo.jpeg'))
<link rel="icon" href="{{ $appIconUrl }}">
<link rel="apple-touch-icon" href="{{ $appIconUrl }}">
