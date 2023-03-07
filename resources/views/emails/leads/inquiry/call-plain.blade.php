New Click to Call on {{ $website }}

You are receiving this message because a {{ $website }} user filled out the click to call form on the following listing

Listing Name: {{ $title }}
Listing URL: {{ $url }}

Full Name: {{ $fullName }}
Phone: {{ $phone }}


Trailer Central. Copyright {{ $year }}, All rights reserved.


@if (!empty($isSpam))
    SPAM MESSAGE
    @if (!empty($forwardedFor)) / Visible Proxy: {{ $forwardedFor }} @endif
    Matched Rules: {{ $allFailures }}
    Original Recipients: {{ $originalContactList }}

    Not Spam?
    Send to original recipients anyway: {{ $resendUrl }} (expires after 7 days)
@endif