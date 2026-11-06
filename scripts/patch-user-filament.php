<?php

/**
 * Faz o App\Models\User implementar FilamentUser — necessário para o login do
 * painel Filament quando APP_ENV=production. Idempotente: se já implementa, sai.
 *
 * O patch fica fora do commit do model (o repositório continua instalável/CI-verde
 * sem o Filament); ele só é aplicado no provisionamento, quando o Filament já existe.
 */
$path = __DIR__.'/../app/Models/User.php';
$src = file_get_contents($path);

if ($src === false) {
    fwrite(STDERR, "Não consegui ler {$path}\n");
    exit(1);
}

if (str_contains($src, 'FilamentUser')) {
    echo "User já implementa FilamentUser; nada a fazer.\n";
    exit(0);
}

$src = str_replace(
    "use Illuminate\\Notifications\\Notifiable;",
    "use Filament\\Models\\Contracts\\FilamentUser;\nuse Filament\\Panel;\nuse Illuminate\\Notifications\\Notifiable;",
    $src
);

$src = str_replace(
    "class User extends Authenticatable\n",
    "class User extends Authenticatable implements FilamentUser\n",
    $src
);

$method = "\n    public function canAccessPanel(Panel \$panel): bool\n    {\n        // Single-user. Para restringir, compare \$this->email com uma lista.\n        return true;\n    }\n";

$pos = strrpos($src, '}');
$src = substr($src, 0, $pos).$method.substr($src, $pos);

file_put_contents($path, $src);
echo "User.php atualizado para FilamentUser.\n";
