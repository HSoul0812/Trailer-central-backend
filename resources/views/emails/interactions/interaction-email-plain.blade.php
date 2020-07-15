{{ $salesperson_name }}, you have been assigned to handle the 
following lead "{{ $lead_name }}"{{ $next_contact_date }}.
See below for details.

@if (!empty($launch_url))
    Click here to open this lead in Trailer Central CRM: {{ $launch_url }}
@endif


@if (!empty($lead_email))
    Email Address: {{ $lead_email }}
@endif
@if (!empty($lead_phone))
    Phone Number: {{ $lead_phone }}
@endif
@if (!empty($lead_status))
    Status: {{ $lead_status }}
@endif
@if (!empty($lead_address))
    Address:
    {{ $lead_address }}
@endif

@if (!empty($lead_comments))
    {{ $lead_comments }}
@endif