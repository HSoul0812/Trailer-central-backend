{{-- <link rel="icon" type="image/png" href="{{ asset('/img/favicon.png') }} "> --}}
@if(config('app.env') === 'production' && !empty(config('logrocket.app_id')))
    @php
        $guard = config('nova.guard');
    @endphp
    @auth($guard)
        @php
            $user = auth()->guard($guard)->user();
        @endphp
        <script src="https://cdn.lr-in-prod.com/LogRocket.min.js" crossorigin="anonymous"></script>
        <script>window.LogRocket && window.LogRocket.init('{{ config('logrocket.app_id')  }}');</script>
        <script>
          window.LogRocket && LogRocket.identify('{{ $user->id }}-NOVA', {
            name: '{{ $user->name }}',
            email: '{{ $user->email }}',
          });
        </script>
    @endauth
@endif
