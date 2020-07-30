New Click to Call on {{ $website }}

You are receiving this message because a {{ $website }} user filled out the click to call form on the following listing

Listing Name: {{ $item_name }}
Listing URL: {{ $item_url }}

Lead Name: {{ $first_name }} {{ $last_name }}
Phone: {{ $phone }}


Trailer Central. Copyright {{ $year }}, All rights reserved.


@if (!empty($is_spam))
    {{ $admin_msg }}
@endif