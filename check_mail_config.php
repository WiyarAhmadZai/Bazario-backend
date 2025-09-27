<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'MAIL_MAILER: ' . config('mail.default') . PHP_EOL;
echo 'MAIL_HOST: ' . config('mail.mailers.smtp.host') . PHP_EOL;
echo 'MAIL_PORT: ' . config('mail.mailers.smtp.port') . PHP_EOL;
echo 'MAIL_USERNAME: ' . config('mail.mailers.smtp.username') . PHP_EOL;
echo 'MAIL_ENCRYPTION: ' . config('mail.mailers.smtp.encryption') . PHP_EOL;
echo 'MAIL_PASSWORD SET: ' . (config('mail.mailers.smtp.password') ? 'YES' : 'NO') . PHP_EOL;
