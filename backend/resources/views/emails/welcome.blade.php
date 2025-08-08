<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f7f7; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7f7f7; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #4f46e5; padding: 20px; color: white; text-align: center;">
                            <h1 style="margin: 0; font-size: 24px;">Welcome, {{ $user->name }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; text-align: left;">
                            <p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $user->name }},</p>
                            <p style="font-size: 14px; margin-bottom: 10px;">
                                Thank you for registering with us. We’re excited to have you as a part of our fitness community!
                            </p>
                            <p style="font-size: 14px; margin-bottom: 10px;">
                                Your registered email: <strong>{{ $user->email }}</strong>
                            </p>
                            <p style="font-size: 14px; margin-top: 20px;">
                                Let’s crush those goals together! 💪
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f0f0f0; padding: 20px; text-align: center;">
                            <p style="font-size: 12px; color: #666;">© {{ date('Y') }} Mav Gym PLC. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
