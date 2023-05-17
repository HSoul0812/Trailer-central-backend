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
                                    <br><br>
                                    Source: {{ $source }}<br>
                                    First Name: {{ $firstName }}<br>
                                    Last Name: {{ $lastName }}<br>
                                    Email: {{ $emailAddress }}<br>
                                    Home Phone: {{ $phoneNumber }}<br>
                                    Street Address: {{ $addressStreet }}<br>
                                    City: {{ $addressCity }}<br>
                                    State or Province: {{ $addressRegion }}<br>
                                    Zip Postal Code: {{ $addressZip }}<br>

                                    @if(!empty($inventoryId))
                                        Condition: {{ $inventoryCondition }}<br>
                                        Stock #: {{ $inventoryStock }}<br>
                                        Year: {{ $inventoryYear }}<br>
                                        Mfg: {{ $inventoryMfg }}<br>
                                        Brand: {{ $inventoryBrand }}<br>
                                        Model: {{ $inventoryModel }}<br>
                                        Length: {{ $inventoryLength }}<br>                                        
                                    @endif

                                    Comment: {{ $comments }}<br>

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