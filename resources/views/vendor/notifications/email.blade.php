<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Notifications Email Template</title>
    <meta name="description" content="Notifications Email Template">
    <style type="text/css">
        a:hover {
            text-decoration: none !important;
        }

        :focus {
            outline: none;
            border: 0;
        }

    </style>
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" bgcolor="#eaeeef"
    leftmargin="0">
    <!--100% body table-->
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
        style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="background-color: #f2f3f8; max-width:670px; margin:0 auto;" width="100%" border="0"
                    align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        @if (!empty($salutation))
                            <td>
                                {{ $salutation }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <td>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                style="max-width:600px; background:#fff; border-radius:3px; text-align:left;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                <tr>
                                    <td style="padding:40px;">
                                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td>
                                                    <h4
                                                        style="color: #1e1e2d; font-weight: 500; margin: 0; font-size: 32px;font-family:'Rubik',sans-serif;">
                                                        {{ $notification['greetings'] }}</h4>
                                                    <p
                                                        style="font-size:15px; color:#455056; line-height:24px; margin:8px 0 30px;">
                                                        {{ $notification['body'] }}</p>
                                                    @isset($notification['line1'])
                                                        <p
                                                            style="font-size:15px; color:#455056; line-height:24px; margin:8px 0 30px;">
                                                            {{ $notification['line1'] }}</p>
                                                    @endisset
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:40px;">
                                        @isset($notification['tablehead'])
                                            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                <th>
                                                    <tr
                                                        style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                        @foreach ($notification['tablehead'] as $thead)
                                                            <td align="center"
                                                                style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; background-color: #eee; margin: 0; padding: 10px 5px;"
                                                                valign="center">{{ $thead }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                </th>
                                                <tbody>
                                                    <tr
                                                        style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                        @foreach ($notification['tablebody'] as $tbody)
                                                            <td align="center"
                                                                style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px;  border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;"
                                                                valign="center">{{ $tbody }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        @endisset

                                    </td>
                                </tr>
                            </table>
                        </td>

                    </tr>

                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->
</body>

</html>
