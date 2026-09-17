<?php
if (PHP_SAPI !== 'cli') exit(1);
require __DIR__ . '/../src/bootstrap.php';
$_SESSION = [];
function prompt(string $label): string { echo $label; return trim(fgets(STDIN)); }
try {
    $name = prompt('Nome: ');
    $email = prompt('E-mail: ');
    echo "Senha (12 a 72 bytes; não será exibida em terminais Unix): ";
    $hidden = PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec') && function_exists('stream_isatty') && stream_isatty(STDIN);
    if ($hidden) shell_exec('stty -echo');
    try { $password = rtrim(fgets(STDIN), "\r\n"); } finally { if ($hidden) shell_exec('stty echo'); echo "\n"; }
    if (!user_create(['name' => $name, 'email' => $email, 'password' => $password, 'role' => 'professor'])) {
        foreach (flashes() as $messages) foreach ($messages as $message) fwrite(STDERR, $message . "\n");
        exit(1);
    }
    echo "Professor criado.\n";
} catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
