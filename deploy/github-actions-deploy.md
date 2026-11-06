# Deploy automático no Oracle via GitHub Actions

O GitHub faz o deploy na sua VM por SSH. Suas credenciais ficam **só nos Secrets
do GitHub** (criptografados) — nunca no chat nem no sandbox do Claude.

Fluxo: **você cria a VM + adiciona os secrets → clica em "Run workflow" → o
GitHub instala Docker, envia o código e sobe o CRM**. Reexecutar = redeploy.

---

## Passo 1 — Gerar uma chave SSH dedicada (no seu PC)
```bash
ssh-keygen -t ed25519 -f oracle_deploy -N ""
# Gera: oracle_deploy (privada)  e  oracle_deploy.pub (pública)
```

## Passo 2 — Criar a VM Always Free
Siga `deploy/oracle-free-tier.md` (passos 1–2). Ao criar:
- **SSH key:** cole o conteúdo de `oracle_deploy.pub`.
- **Public IPv4:** Yes. Anote o **IP**.
- Na **Security List** da VCN, abra **Ingress TCP 80** de `0.0.0.0/0`
  (a porta 22 já vem aberta). *Essa é a única etapa no console da Oracle.*

> Não precisa instalar Docker na mão: o workflow faz isso na 1ª execução.

## Passo 3 — Adicionar os Secrets no GitHub
Repo → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Valor |
|--------|-------|
| `DEPLOY_HOST` | IP público da VM |
| `DEPLOY_USER` | `ubuntu` |
| `DEPLOY_SSH_KEY` | **conteúdo** do arquivo `oracle_deploy` (a chave **privada**) |
| `DEPLOY_ENV` | conteúdo do seu `.env` (baseie em `deploy/.env.oracle.example`, troque as senhas) |
| `DEPLOY_PORT` | *(opcional)* `22` |
| `DEPLOY_PATH` | *(opcional)* `leads-crm` |

Notas:
- Em `DEPLOY_ENV`, deixe `APP_KEY=` **vazio**: é gerado no 1º boot e preservado
  (o `.env` só é criado se ainda não existir na VM).
- Defina `APP_URL=http://SEU_IP` e senhas fortes em `DB_PASSWORD`, `DB_ROOT_PASSWORD`,
  `ADMIN_PASSWORD`.

## Passo 4 — Rodar o deploy
GitHub → aba **Actions** → **"Deploy to Oracle (Always Free)"** → **Run workflow**.

Acompanhe os passos. O **1º boot é lento** (instala Composer/Filament/npm numa VM
ARM) — pode passar de 5 min; o passo *Health check* é tolerante a isso.

## Passo 5 — Acessar
Abra **`http://SEU_IP/admin`** e entre com `ADMIN_EMAIL` / `ADMIN_PASSWORD` do seu `.env`.

---

## Redeploy
Toda vez que o código mudar, rode o workflow de novo (ou me peça pra adicionar
gatilho por push). O `rsync` só manda o diff e o Compose reconstrói o necessário.

## Diagnóstico
```bash
ssh ubuntu@SEU_IP "cd leads-crm && sudo docker compose -f deploy/compose.oracle.yml logs -f app"
```

## Segurança
- A chave privada e as senhas ficam **apenas** nos Secrets do GitHub.
- Troque para **HTTPS + domínio** antes de usar pra valer (peça o `compose` com Caddy).
- Para rotacionar acesso: gere nova chave, troque `authorized_keys` na VM e o secret `DEPLOY_SSH_KEY`.
