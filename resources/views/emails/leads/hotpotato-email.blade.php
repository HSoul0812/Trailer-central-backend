<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
</head>
<body style="font-family:Gotham, 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color:#747474; margin:0; padding:0; color:#333333;">

<table width="100%" bgcolor="#f0f2ea" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td style="padding:40px 0;">
            <!-- begin main block -->
            <table cellpadding="0" cellspacing="0" border="0" align="center">
                <tbody>
                <tr>
                    <td>
                        <!-- begin wrapper -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tbody>
                            <tr>
                                <td bgcolor="#FFFFFF" style="padding: 30px;">
                                    <!-- begin content -->

                                    Your salesperson {{ $salesperson_name }} failed to follow-up with the lead
                                    {{ $lead_name }}{{ $old_contact_date }} as scheduled.<br /><br />

                                    @if (!empty($new_salesperson_name))
                                        As per your request, we re-assigned the lead to the next available
                                        salesperson, {{ $new_salesperson_name }} and have scheduled the next
                                        contact time to {{ $next_contact_date }}.<br /><br />
                                    @else
                                        As per your request, we we attempted to re-assign the lead to the
                                        next available salesperson, but no suitable salesperson was found.<br /><br />
                                        We automatically rescheduled the next contact time to {{ $next_contact_date }}.<br /><br />
                                    @endif

                                    @if ($weekday > 0)
                                        As it is now the weekend, we set the Next Contact Date to Monday instead
                                        of {{ $weekday > 1 ? 'Sunday' : 'Saturday' }}. If you wish to instead schedule
                                        this for the weekend, feel free to do so manually in the Trailer Central CRM.<br /><br />
                                    @endif

                                    @if (!empty($launch_url))
                                        <a href="{{ $launch_url }}">Click here to open this lead in Trailer Central CRM!</a><br /><br />
                                    @endif

                                    <!-- end content -->
                                </td>
                            </tr>

                            </tbody>
                        </table>
                        <!-- end wrapper-->
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- end main block -->
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
