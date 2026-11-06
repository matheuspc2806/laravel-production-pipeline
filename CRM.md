# Leads CRM — manutenção Laravel/PHP (mercado EUA)

Sistema para **gerar, qualificar e trabalhar leads** de manutenção de sistemas
Laravel/PHP, voltado a pequenas empresas americanas fora de TI.

## Duas partes
1. **Kit de prospecção** (`leads/`) — o *método*: onde achar empresas por nicho
   (diretórios públicos dos EUA), o **Check de 60s** de qualificação e um
   `lead-tracker.csv`. Comece por aqui.
2. **App CRM** (Laravel 13 + **Filament v5** + **MySQL**) — o *sistema* para
   gerenciar o pipeline, já semeado com **13 leads reais** (odontologia/Columbus).

## Stack
- Laravel 13 / PHP 8.4
- Filament v5 (painel admin em `/admin`)
- MySQL 8.4
- Deploy: Oracle Cloud **Always Free** via Docker Compose

## Arquivos que compõem o CRM
| Camada | Caminho |
|--------|---------|
| Tabela de leads | `database/migrations/2026_11_06_100000_create_leads_table.php` |
| Model | `app/Models/Lead.php` |
| Enums (nicho / pipeline) | `app/Enums/Niche.php`, `app/Enums/LeadStatus.php` |
| Dados reais + admin | `database/seeders/LeadSeeder.php`, `database/seeders/DatabaseSeeder.php` |
| Provisionamento | `scripts/setup-crm.sh`, `scripts/patch-user-filament.php` |
| Deploy Oracle | `deploy/oracle-free-tier.md`, `deploy/compose.oracle.yml`, `deploy/Dockerfile.oracle`, `deploy/.env.oracle.example` |

## Como subir (resumo)
Numa VM Always Free da Oracle, siga **`deploy/oracle-free-tier.md`**. Em uma linha:
```bash
cp deploy/.env.oracle.example .env   # edite senhas e APP_URL
docker compose -f deploy/compose.oracle.yml up -d --build
# painel: http://SEU_IP/admin
```

## Nota de engenharia (honesta)
A **camada de domínio** (migration, model, enums, seeder) foi escrita e revisada
aqui e mantém o repositório instalável e o CI verde — ela **não** acopla o Filament
no commit. A **instalação do Filament + geração do painel** roda no destino
(`scripts/setup-crm.sh`), porque não foi possível instalar/validar o Filament v5
dentro do sandbox do Claude (sem PHP 8.4 e com o proxy cortando o `api.github.com`
do Composer). Logo, a 1ª subida pode exigir 1–2 ajustes — peça ajuda ao vivo.

## Princípio de qualificação (importante)
Nenhum lead é marcado como "Laravel desatualizado/vulnerável". O stack e os sinais
de site neglected são confirmados **lead a lead** com ferramentas públicas
(Wappalyzer + PageSpeed) no **Check de 60s** — é a forma legítima e a que converte.
