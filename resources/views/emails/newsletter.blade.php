<!DOCTYPE html>
<html>

<head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <title>Luxury Store Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1a1a1a;
            color: #ffffff;
        }

        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid #D4AF37;
        }

        .header h1 {
            margin: 0;
            color: #D4AF37;
            font-size: 28px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
        }

        .content h2 {
            color: #D4AF37;
            margin-top: 0;
        }

        .content p {
            margin-bottom: 20px;
            color: #e0e0e0;
        }

        .btn {
            display: inline-block;
            background-color: #D4AF37;
            color: #000000;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }

        .btn:hover {
            background-color: #B8941F;
        }

        .footer {
            background-color: #0d0d0d;
            padding: 20px;
            text-align: center;
            color: #888;
            font-size: 12px;
        }

        .footer a {
            color: #D4AF37;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class=\"container\">
        <div class=\"header\">
            <h1>LUXURY STORE</h1>
            <p style=\"margin: 10px 0 0 0; color: #ccc;\">Premium Luxury Collection</p>
        </div>

        <div class=\"content\">
            {!! $content !!}

            @if ($postUrl)
                <div style=\"text-align: center; margin: 30px 0;\">
                    <a href=\"{{ $postUrl }}\" class=\"btn\">Read Full Article</a>
                </div>
            @endif
        </div>

        <div class=\"footer\">
            <p>Thank you for being part of our luxury community.</p>
            <p>
                <a href=\"{{ $unsubscribeUrl }}\">Unsubscribe</a> |
                <a href=\"http://localhost:3000\">Visit Our Store</a>
            </p>
            <p>&copy; {{ date('Y') }} Luxury Store. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
