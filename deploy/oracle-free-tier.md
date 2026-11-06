# Deploy do Leads CRM no Oracle Cloud — Always Free

Runbook para subir o CRM (Laravel 13 + Filament v5 + MySQL 8.4) numa VM
**Always Free** da Oracle, via Docker Compose.

> ⚠️ **Honestidade:** este runbook e o `scripts/setup-crm.sh` foram escritos com
> cuidado, mas **não pude executá-los no sandbox do Claude** (lá faltam PHP 8.4 e
> rede para o Composer baixar o Filament). Então trate a 1ª subida como um
> "primeiro boot" que pode pedir 1–2 ajustes. Me chame que eu acompanho ao vivo.

---

## 0. O que você vai ter
- Painel admin em `http://SEU_IP/admin` com CRUD de leads, filtros por nicho/status, score.
- Banco MySQL com os **13 leads reais** (odontologia/Columbus) já populados.
- Tudo rodando em container, de graça, na sua tenancy Oracle.

## 1. Criar a VM (Always Free)
1. Oracle Cloud → **Compute → Instances → Create instance**.
2. Image: **Canonical Ubuntu 22.04**. Shape: **VM.Standard.A1.Flex** (Ampere ARM) —
   marque algo como **1 OCPU / 6 GB** (dentro do Always Free). Alternativa AMD:
   **VM.Standard.E2.1.Micro**.
3. Em **Networking**, garanta **Assign a public IPv4 address = Yes**.
4. Baixe a chave SSH e crie. Anote o **IP público**.

## 2. Abrir a porta 80
**(a) Security List (VCN):** Networking → sua VCN → Subnet → Security List →
**Add Ingress Rule**: Source `0.0.0.0/0`, IP Protocol `TCP`, Destination port `80`.

**(b) Firewall da VM** (imagens Oracle Ubuntu bloqueiam por iptables):
```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo netfilter-persistent save
```

## 3. Instalar Docker
```bash
sudo apt-get update
sudo apt-get install -y docker.io docker-compose-v2 git
sudo usermod -aG docker $USER && newgrp docker
```

## 4. Clonar o repositório e configurar o .env
```bash
git clone https://github.com/matheuspc2806/laravel-production-pipeline.git app
cd app
git checkout claude/laravel-maintenance-sales-us-yhx68t

cp deploy/.env.oracle.example .env
nano .env     # troque DB_PASSWORD, DB_ROOT_PASSWORD, ADMIN_EMAIL, ADMIN_PASSWORD
              # e coloque APP_URL=http://SEU_IP_PUBLICO
```
> O repo é privado: no `git clone` use um **Personal Access Token** do GitHub
> (usuário + token como senha) ou configure uma deploy key.

## 5. Subir
```bash
docker compose -f deploy/compose.oracle.yml up -d --build
```
Acompanhe o 1º boot (instala Filament, migra, gera o painel, popula leads):
```bash
docker compose -f deploy/compose.oracle.yml logs -f app
```

## 6. Acessar
Abra **`http://SEU_IP_PUBLICO/admin`** e entre com `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

---

## 7. HTTPS + domínio (recomendado antes de usar pra valer)
Login por HTTP num IP público é só para teste. Para produção, aponte um domínio
para o IP e ponha um **Caddy** na frente (HTTPS automático via Let's Encrypt):
```bash
# exemplo mínimo de Caddyfile
seu-dominio.com {
    reverse_proxy app:8080
}
```
Me peça que eu te entrego o `compose` com Caddy pronto.

## 8. Manutenção
- **Logs:** `docker compose -f deploy/compose.oracle.yml logs -f app`
- **Reprovisionar/atualizar:** `git pull && docker compose -f deploy/compose.oracle.yml up -d --build`
- **Backup do banco:**
  ```bash
  docker compose -f deploy/compose.oracle.yml exec mysql \
    mysqldump -uroot -p"$DB_ROOT_PASSWORD" leads > backup-$(date +%F).sql
  ```
- **Importar seus leads do CSV:** os dados do `leads/lead-tracker.csv` podem ser
  carregados pelo painel (ou peça que eu escrevo um importador Artisan).

## 9. Por que não a imagem FrankenPHP do repo?
A imagem de produção do repositório "assa" o `vendor` no build — ótimo para CI/CD,
mas atrapalha a **geração do painel Filament em runtime** (que precisa do banco).
Para o free tier priorizei "sobe fácil": um container PHP 8.4 que provisiona na 1ª
subida. Quando você quiser CI/CD de verdade, dá para migrar o painel já gerado
para dentro da imagem FrankenPHP — me chama que eu faço.
