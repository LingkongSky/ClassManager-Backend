<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if ($argc < 2) {
    echo "Usage: php hash_password.php [password]\n";
    exit(1);
}

$password = $argv[1];

$sha1 = sha1($password);
$result = hash_hmac("sha256", $sha1, config('app.password_sign'));

echo "Origin password: $password\n";
echo "SHA1: $sha1\n";
echo "Result: $result\n";