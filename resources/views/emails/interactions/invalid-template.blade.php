<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notice: Could not send template named "{!! $name !!}"!</title>
</head>
<body>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td>
            <!-- begin content -->

            {!! $toName !!}, an issue has been detected with your {!! $typeName !!}
            called "{!! $name !!}"! The email template you tried to send has no html
            to send in the campaign! Please check the template in the CRM and try again!
            Your {!! $typeName !!} has been temporarily disabled until this has been resolved.<br /><br />

            <a href="{!! $launchUrl !!}">Click here to go directly there!</a>

            <!-- end content -->
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
