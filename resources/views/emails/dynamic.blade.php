@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{ $logo }}" class="logo" alt="CloudMarket" style="width: 250px; height: auto;">
@endcomponent
@endslot

{!! $body !!}

@if (!is_null($footer))
@slot('subcopy')
@component('mail::subcopy')
{!! $footer !!}
@endcomponent
@endslot
@endif

@slot('footer')
@component('mail::footer')
<small>&copy; {{ date('Y') }} {{ $tenant->name }}.</small>
@endcomponent
@endslot
@endcomponent