<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your SSC Account</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f8fafc; color: #1e293b;-webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); padding: 40px 20px;">
                            <img src="https://mcclawis.edu.ph/wp-content/uploads/2021/08/mcc_logo.png" alt="MCC Logo" style="width: 64px; height: 64px; object-fit: contain; margin-bottom: 16px;" onerror="this.src='https://www.gstatic.com/recaptcha/api2/logo_48.png'">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">SSC Transparency Portal</h1>
                            <p style="color: rgba(255, 255, 255, 0.85); margin: 6px 0 0 0; font-size: 14px; font-weight: 500;">Supreme Student Council Office</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin-top: 0; color: #0f172a; font-size: 20px; font-weight: 700; letter-spacing: -0.2px;">Confirm your student account</h2>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569;">Hello <strong>{{ $user->first_name }}</strong>,</p>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569;">Thank you for registering at the **Madridejos Community College Supreme Student Council Portal**. To complete your activation and verify your school email address, please click the button below:</p>
                            
                            <!-- Button -->
                            <div style="text-align: center; margin: 32px 0;">
                                <a href="{{ $url }}" target="_blank" style="display: inline-block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); transition: background-color 0.2s;">Confirm Account</a>
                            </div>

                            <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 24px;">This activation link is valid for **24 hours**. If you did not register for this account, you can safely ignore this email.</p>
                            
                            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 30px 0;">
                            
                            <!-- Backup Link -->
                            <p style="font-size: 12px; line-height: 1.5; color: #94a3b8; word-break: break-all;">If the button above does not work, copy and paste this URL into your web browser:<br>
                            <a href="{{ $url }}" target="_blank" style="color: #4f46e5; text-decoration: underline;">{{ $url }}</a></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 24px 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0; font-weight: 600; color: #64748b;">Supreme Student Council Office</p>
                            <p style="margin: 4px 0 0 0;">Madridejos Community College, Cebu, Philippines</p>
                            <p style="margin: 12px 0 0 0; font-size: 11px;">This is an automated system email. Please do not reply directly to this message.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>