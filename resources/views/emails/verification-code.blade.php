<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'password_reset' ? 'Password Reset' : 'Email Verification' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }

        .content {
            padding: 40px 30px;
            color: #333;
            line-height: 1.6;
        }

        .verification-code {
            background-color: #f8f9fa;
            border: 2px dashed #FFD700;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }

        .code {
            font-size: 36px;
            font-weight: bold;
            color: #FFD700;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }

        .footer {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
        }

        .footer a {
            color: #FFD700;
            text-decoration: none;
        }

        .expiry-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>
                @if ($type === 'password_reset')
                    Password Reset
                @else
                    Welcome to Our Luxury Store
                @endif
            </h1>
        </div>

        <div class="content">
            <h2>Hello {{ $user->name }},</h2>

            @if ($type === 'password_reset')
                <p>We received a request to reset your password. Use the verification code below to continue with your
                    password reset:</p>
            @else
                <p>Thank you for joining our luxury community! To complete your registration and verify your email
                    address, please use the verification code below:</p>
            @endif

            <div class="verification-code">
                <p style="margin: 0 0 10px 0; font-weight: bold; color: #333;">Your Verification Code:</p>
                <div class="code">{{ $code }}</div>
            </div>

            <div class="expiry-notice">
                <strong>⏰ Important:</strong> This verification code will expire in 15 minutes for security reasons.
            </div>

            @if ($type === 'password_reset')
                <p>If you didn't request a password reset, please ignore this email. Your password will remain
                    unchanged.</p>
            @else
                <p>After verification, you'll have full access to our exclusive collection of luxury items and premium
                    features.</p>
            @endif

            <p>If you didn't request this verification code, please ignore this email.</p>

            <p>Best regards,<br>
                <strong>The Luxury Store Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Luxury Store. All rights reserved.</p>
            <p>Need help? Contact us at <a href="mailto:support@luxurystore.com">support@luxurystore.com</a></p>
        </div>
    </div>
</body>

</html>
