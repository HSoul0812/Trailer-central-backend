New Inventory Information Request on {{ $website }}

Inventory Stock: {{ $inventory_stock }}
Inventory Item: {{ $inventory_title }}
Inventory URL: {{ $inventory_url }}

First Name: {{ $first_name }}
Last Name: {{ $last_name }}
Preferred Contact: {{ $preferred_contact }}
Email: {{ $lead_email }}
Click to Reply: mailto:{{ $lead_email }}?subject={{ $subject }}&body={{ $comments }}
Zip Code: {{ $postal }}
Phone: {{ $phone }}

Comments: {{ $comments }}

@if (!empty($device))
    Via: {{ $device }} Device
@endif


Trailer Central. Copyright {{ $year }}, All rights reserved.


@if (!empty($is_spam))
    SPAM MESSAGE -- SCORE: {{ $all_failures_count }}
    Remote IP: {{ $remote_ip }} @if (!empty($forwarded_for) / Visible Proxy: {{ $forwarded_for }} @endif
    Matched Rules: {{ $all_failures }}
    Original Recipients: {{ $original_contact_list }}

    Not Spam?
    Send to original recipients anyway: {{ $resend_url }} (expires after 7 days)
@endif