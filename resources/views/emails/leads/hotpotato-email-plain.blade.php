Your salesperson {{ $salesperson_name }} failed to follow-up with the lead
{{ $lead_name }}{{ $next_contact_date }} as scheduled.

@if (!empty($new_sales_email))
    As per your request, we re-assigned the lead to the next available
    salesperson, {{ $new_sales_email }} and have scheduled the next
    contact time to {{ $new_contact_date }}.
@else
    As per your request, we we attempted to re-assign the lead to the
    next available salesperson, but no suitable salesperson was found.
    
    We automatically rescheduled the next contact time to {{ $new_contact_date }}.
@endif

@if ($weekend > 0)
    As it is now the weekend, we set the Next Contact Date to Monday instead
    of {{ $weekend > 1 ? 'Sunday' : 'Saturday' }}. If you wish to instead schedule
    this for the weekend, feel free to do so manually in the Trailer Central CRM.
@endif

@if (!empty($launch_url))
    Click here to open this lead in Trailer Central CRM: {{ $launch_url }}
@endif