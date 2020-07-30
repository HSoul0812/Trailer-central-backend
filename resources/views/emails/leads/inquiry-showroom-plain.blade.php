New Showroom Model Information Request on {{ $website }}

Showroom Title: {{ $showroom_title }}
Showroom URL: {{ $showroom_url }}

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
    {{ $admin_msg }}
@endif