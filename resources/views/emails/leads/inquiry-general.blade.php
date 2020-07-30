<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
</head>
<body style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px; background-color:#f6f6f6; margin:0; padding:0;">

<table width="100%" bgcolor="#f6f6f6" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td align="center" valign="top" style="padding:20px 0 5px;">
            <!-- begin wrapper -->
            <table bgcolor="{{ $bgcolor }}" cellpadding="0" cellspacing="0" border="0" width="650" style="border:1px solid #E0E0E0;">
                <tr style="background:{{ $bgheader }}">
                    <td valign="top">
                        <a href="https://www.trailercentral.com/">
                            <img src="https://www.trailercentral.com/images/logo2.png" alt="Trailer Central" style="margin-bottom:10px;" border="0"/>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <!-- begin content -->

                        <h3 style="font-size:16px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">New General Submission on {{ $website }}</h3>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">First Name: <strong>{{ $first_name }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Last Name: <strong>{{ $last_name }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Preferred Contact: <strong>{{ $preferred_contact }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif; color: #8C2B22; font-weight: bold">
                            Click to Reply: <strong><a href="mailto:{{ $lead_email }}?subject={{ $subject }}&body={{ $comments }}">{{ $lead_email }}</a></strong>
                        </p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Zip Code: <strong>{{ $postal }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Phone: <strong>{{ $phone }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Comments: <strong>{{ $comments }}</strong></p>

                        @if (!empty($device))
                            <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Via: {{ $device }} Device</p>
                        @endif

                        <!-- end content -->
                    </td>
                </tr>
                <tr>
                    <td align="center" width="650">
                        <table cellspacing="0" cellpadding="4" border="0" width="650">
                            <tr>
                                <td>
                                    <p style="text-align: right; font-size: 10px; color: #ABABB8; margin: 0; font-family:Verdana, Arial, Helvetica, sans-serif;">Trailer Central. Copyright {{ $year }}, All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- end wrapper-->
        </td>
    </tr>
    </tbody>
</table>

@if (!empty($is_spam))
    <div style='width: 650px; margin: 0 auto; background: #ffeb3b; color: #000; border: 1px solid #333; padding: 10px; font-family: monospace'>

        <strong style='text-align: right; display: block; margin: 0; padding: 0;'>SPAM MESSAGE -- SCORE: {{ $all_failures_count }}</strong><br />

        <strong>Remote IP:</strong> {{ $remote_ip }}
        @if (!empty($forwarded_for)
            / Visible Proxy: {{ $forwarded_for }}
        @endif
        </strong><br/>

        <strong>Matched Rules:</strong> {{ $all_failures }}<br/>

        <strong>Original Recipients:</strong> {{ $original_contact_list }}

        <strong>Not Spam?</strong> <a href="{{ $resend_url }}">Send to original recipients anyway</a>
        <span style="color: #888">(expires after 7 days)</span>

    </div>
@endif

</body>
</html>