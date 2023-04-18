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
            <table bgcolor="{{ $bgColor }}" cellpadding="10" cellspacing="0" border="0" width="650" style="border:1px solid #E0E0E0;">
                <tr style="background:{{ $bgHeader }}">
                    @if ($website == $simpleDomain)
                      <td valign="top" style="text-align:center">
                          <a href="{{ $logoUrl }}">
                            <img src="{{ $logo }}" alt="{{ $fromName }}" style="margin-bottom:10px;margin-top:10px;height:20px;" border="0"/>
                          </a>
                      </td>
                    @else
                      <td valign="top">
                          <a href="{{ $logoUrl }}">
                            <img src="{{ $logo }}" alt="{{ $fromName }}" style="margin-bottom:10px;margin-top:10px;height:20px;" border="0"/>
                          </a>
                      </td>
                    @endif
                </tr>
                <tr>
                    <td valign="top">
                        <!-- begin content -->
                        @if ($website == $simpleDomain)
                            <h3 style="font-size:16px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">
                                TrailerTrader.com / A customer is interested in {{ $title }} <br>
                                Following is the content of the 'contact form' that was submitted to you through trailertrader.com.
                            </h3>
                        @else
                          <h3 style="font-size:16px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">New Inventory Information Request on {{ $website }}</h3>
                        @endif


                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Inventory Stock: <strong>{{ $stock }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Inventory Item: <a href="{{ $url }}"><strong>{{ $title }}</strong></a></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif;">Full Name: <strong>{{ $fullName }}</strong></p>

                        <p style="font-size:12px; line-height:16px; font-family:Verdana, Arial, Helvetica, sans-serif; color: #8C2B22;">
                            Buyer's email: <strong><a href="mailto:{{ $email }}?subject={{ $subject }}&body={{ $comments }}">{{ $email }}</a></strong>
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
                                    <p style="text-align: right; font-size: 10px; color: #ABABB8; margin: 0; font-family:Verdana, Arial, Helvetica, sans-serif;">{{$fromName}}. Copyright {{ $year }}, All rights reserved.</p>
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

@if (!empty($isSpam))
    <div style='width: 650px; margin: 0 auto; background: #ffeb3b; color: #000; border: 1px solid #333; padding: 10px; font-family: monospace'>

        <strong style='text-align: right; display: block; margin: 0; padding: 0;'>SPAM MESSAGE</strong><br />


        @if (!empty($forwardedFor))
            / Visible Proxy: {{ $forwardedFor }}
        @endif
        </strong><br/>

        <strong>Matched Rules:</strong> {{ $allFailures }}<br/>

        <strong>Original Recipients:</strong> {{ $originalContactList }}

        <strong>Not Spam?</strong> <a href="{{ $resendUrl }}">Send to original recipients anyway</a>
        <span style="color: #888">(expires after 7 days)</span>

    </div>
@endif

</body>
</html>
