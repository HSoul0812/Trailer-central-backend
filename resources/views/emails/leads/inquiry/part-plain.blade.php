New Part Information Request on {{ $website }}

Part Stock: {{ $stock }}
Part Item: {{ $title }}
Part URL: {{ $url }}

Full Name: {{ $fullName }}
Preferred Contact: {{ $preferred }}
Email: {{ $email }}
Click to Reply: mailto:{{ $email }}?subject={{ $subject }}&body={{ $comments }}
Zip Code: {{ $postal }}
Phone: {{ $phone }}

Comments: {{ $comments }}

@if (!empty($device))
    Via: {{ $device }} Device
@endif


Trailer Central. Copyright {{ $year }}, All rights reserved.


@if (!empty($isSpam))
    SPAM MESSAGE -- SCORE: {{ $allFailuresCount }}
    Remote IP: {{ $remoteIp }} @if (!empty($forwardedFor)) / Visible Proxy: {{ $forwardedFor }} @endif
    Matched Rules: {{ $allFailures }}
    Original Recipients: {{ $originalContactList }}

    Not Spam?
    Send to original recipients anyway: {{ $resendUrl }} (expires after 7 days)
@endif