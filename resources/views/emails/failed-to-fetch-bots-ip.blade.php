@component('mail::message')
The code couldn't fetch {{ $providerName }} bots IP address.

Server URL: [{{ config('app.url') }}]({{ config('app.url') }})

Bot IP URL: [{{ $url }}]({{ $url }})

Error message: {{ $errorMessage }}

If the URL has changed, please ask the developer to update the `\App\Http\Middleware\HumanOnly` class.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
