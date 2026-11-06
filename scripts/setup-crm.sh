#!/usr/bin/env bash
#
# Provisiona o CRM de leads (Filament v5) sobre este projeto Laravel.
# Rode no SERVIDOR de destino (PHP 8.4 + Composer + Node 20+, MySQL acessível).
# NÃO roda no sandbox do Claude Code (lá faltam rede p/ composer e PHP 8.4).
# É idempotente: pode rodar de novo sem quebrar nada.
#
set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> [1/9] Dependências base (sem scripts; Filament ainda não está no composer)"
composer install --no-interaction --prefer-dist --no-scripts

echo "==> [2/9] Instalando Filament v5 (painel admin)"
if ! grep -q '"filament/filament"' composer.json; then
  composer require filament/filament:"^5.0" -W --no-interaction
fi

echo "==> [3/9] .env + APP_KEY"
[ -f .env ] || cp deploy/.env.oracle.example .env
php artisan key:generate --force

echo "==> [4/9] Scaffolding do painel Filament (/admin)"
php artisan filament:install --panels --no-interaction

echo "==> [5/9] Liberando acesso do User ao painel (FilamentUser)"
php scripts/patch-user-filament.php

echo "==> [6/9] Migrando o banco (MySQL)"
php artisan migrate --force

echo "==> [7/9] Gerando o LeadResource a partir da tabela 'leads'"
if ! ls app/Filament/Resources 2>/dev/null | grep -qi lead; then
  php artisan make:filament-resource Lead --generate --no-interaction \
    || php artisan make:filament-resource Lead --no-interaction \
    || echo "   (gere depois com: php artisan make:filament-resource Lead --generate)"
fi

echo "==> [8/9] Seed (admin do painel + 13 leads reais)"
php artisan db:seed --force

echo "==> [9/9] Assets + cache"
(npm ci && npm run build) || echo "   (build de assets falhou — verifique Node 20+)"
php artisan filament:assets || true
php artisan optimize

echo
echo "OK ✅  Painel em: ${APP_URL:-http://localhost}/admin"
echo "Login: use ADMIN_EMAIL / ADMIN_PASSWORD definidos no .env"
