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

                                    {{ $salesperson_name }}, you have been assigned to handle the 
                                    following lead "{{ $lead_name }}"{{ $next_contact_date }}.<br />
                                    See below for details.<br /><br />

                                    @if (!empty($launch_url))
                                        <a href="{{ $launch_url }}">Click here to open this lead in Trailer Central CRM!</a><br /><br />
                                    @endif

                                    @if (!empty($lead_email))
                                        <strong>Email Address:</strong> {{ $lead_email }}<br />
                                    @endif

                                    @if (!empty($lead_phone))
                                        <strong>Phone Number:</strong> {{ $lead_phone }}<br />
                                    @endif

                                    @if (!empty($lead_status))
                                        <strong>Status:</strong> {{ $lead_status }}<br />
                                    @endif

                                    @if (!empty($lead_address))
                                        <strong>Address:</strong><br />{{ $lead_address }}<br />
                                    @endif

                                    @if (!empty($lead_comments))
                                        <br /><blockquote>{{ $lead_comments }}</blockquote>
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
