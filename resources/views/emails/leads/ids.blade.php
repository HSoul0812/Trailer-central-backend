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
                                    You received a new unit inquiry from your website. The details of the request are below:
                                    <br>
                                    <br>
                                    Source: {{ $lead->website->domain }}<br>
                                    First Name: {{ $lead->first_name }}<br>
                                    Last Name: {{ $lead->last_name }}<br>
                                    Email: {{ $lead->email_address }}<br>
                                    Home Phone: {{ $lead->phone_number }}<br>
                                    Street Address: {{ $lead->address }}<br>
                                    City: {{ $lead->city }}<br>
                                    State or Province: {{ $lead->region }}<br>
                                    Zip Postal Code: {{ $lead->zip }}<br>
                                    
                                    @if($lead->inventory)                                        
                                        Condition: {{ strtoupper($lead->inventory->condition) }}<br>
                                        Stock #: {{ $lead->inventory->stock }}<br>
                                        Year: {{ $lead->inventory->year }}<br>
                                        Mfg: {{ $lead->inventory->manufacturer }}<br>
                                        Brand: {{ $lead->inventory->brand }}<br>
                                        Model: {{ $lead->inventory->model }}<br>
                                        Length: {{ $lead->inventory->length }}<br>                                        
                                    @endif
                                    
                                    Comment: {{ $lead->comments }}<br
                                    
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
