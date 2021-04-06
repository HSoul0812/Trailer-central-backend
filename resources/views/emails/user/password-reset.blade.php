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
                                    <!-- Begin Content -->
                                   We received a request to reset the password for your account. If you submitted this request, please click this link or paste it into your browser to reset your password:
                                   <br><br>
                                   <a href="{{ $resetUrl }}?code={{ $code }}">{{ $resetUrl }}?code={{ $code }}</a>
                                   <br><br>
                                   If you did not submit this request, please contact us immediately.
                                   <br>
                                    <!-- End Content -->
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
