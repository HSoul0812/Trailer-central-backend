<!doctype html>
<html lang="en">

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
                                    <h2>An integration has been: {{ $dealerIntegration->active ? 'activated' : 'deactivated' }}</h2>

                                    <!-- begin content -->
                                    <h3>Dealer Information</h3>
                                    <ul>
                                        <li>
                                            <strong>Dealer Id:</strong> {{ $dealerIntegration->dealer_id }}
                                        </li>
                                        <li>
                                            <strong>Dealer Name:</strong> {{ $dealerIntegration->dealer->name }}
                                        </li>
                                        <li>
                                            <strong>Dealer Email:</strong> {{ $dealerIntegration->dealer->email }}
                                        </li>
                                    </ul>
                                    <h3>Integration Information</h3>
                                    <ul>
                                        <li>
                                            <strong>Integration Id:</strong> {{ $dealerIntegration->integration_id }}
                                        </li>
                                        <li>
                                            <strong>Integration Name:</strong> {{ $dealerIntegration->integration->name }}
                                        </li>
                                    </ul>

                                    @if ($dealerIntegration->active)
                                        <h3>Integration Settings</h3>
                                        <li>
                                            <strong>Settings:</strong>
                                            <ul>
                                                @foreach(unserialize($dealerIntegration->settings) as $key => $setting)
                                                    <li>
                                                        <strong>{{ $key }}</strong>: {{ $setting }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                        @if (!empty(explode(",", $dealerIntegration->location_ids)))
                                            <li>
                                                <strong>Locations:</strong>
                                                <ul>
                                                    @foreach(explode(",", $dealerIntegration->location_ids) as $location)
                                                        <li>{{ $location }}</li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
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
