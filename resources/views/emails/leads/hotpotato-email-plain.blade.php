Your salesperson {{ $salesperson_name }} failed to follow-up with the lead
{{ $lead_name }}{{ $old_contact_date }} as scheduled.

@if (!empty($new_salesperson_name))
    As per your request, we re-assigned the lead to the next available
    salesperson, {{ $new_salesperson_name }} and have scheduled the next
    contact time to {{ $next_contact_date }}.
@else
    As per your request, we we attempted to re-assign the lead to the
    next available salesperson, but no suitable salesperson was found.
    
    We automatically rescheduled the next contact time to {{ $next_contact_date }}.
@endif

@if ($weekday > 0)
    As it is now the weekend, we set the Next Contact Date to Monday instead
    of {{ $weekday > 1 ? 'Sunday' : 'Saturday' }}. If you wish to instead schedule
    this for the weekend, feel free to do so manually in the Trailer Central CRM.
@endif

@if (!empty($launch_url))
    Click here to open this lead in Trailer Central CRM: {{ $launch_url }}
@endif