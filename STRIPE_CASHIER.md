# Stripe + Cashier — Verified Badge Implementation

Plano de implementação módulo de pagamento (R$5/mês → selo verified) usando Laravel Cashier (Stripe).

## Defaults confirmados

- **Webhook local**: `stripe listen --forward-to localhost:8000/stripe/webhook`
- **Conta Stripe BR**: ativada (BRL)
- **Trial**: sem trial — cobra imediato no checkout
- **Cancelamento**: `cancelAt(periodEnd)` — mantém verified até fim do ciclo pago

## Stripe test cards

- Sucesso: `4242 4242 4242 4242` — qualquer CVC, data futura
- Falha (insufficient funds): `4000 0000 0000 9995`
- 3D Secure: `4000 0025 0000 3155`

---

## Tarefas

### 1. Instalar Laravel Cashier
- [x] `composer require laravel/cashier`
- [x] `php artisan vendor:publish --tag="cashier-migrations"`
- [x] Copiar `create_customer_columns` manualmente (não publicou auto na v16)
- [x] `php artisan migrate`

### 2. Migration `users.verified_at`
- [x] `php artisan make:migration add_verified_at_to_users_table`
- [x] Adicionar coluna `verified_at` timestamp nullable
- [x] `php artisan migrate`

### 3. Atualizar `User` model
- [x] `use Laravel\Cashier\Billable;`
- [x] Cast `verified_at => 'datetime'`
- [x] Helper `isVerified(): bool`

### 4. Config `.env`
- [x] `STRIPE_KEY=pk_test_...` (placeholder em `.env` — preencher com chaves reais do dashboard)
- [x] `STRIPE_SECRET=sk_test_...`
- [x] `STRIPE_WEBHOOK_SECRET=whsec_...`
- [x] `STRIPE_VERIFIED_PRICE_ID=price_...`
- [x] `CASHIER_CURRENCY=brl`
- [x] `config/services.php` ganha bloco `stripe.verified_price_id`

### 5. Criar `BillingController`
- [x] `app/Http/Controllers/Settings/BillingController.php`
- [x] Métodos: `index`, `checkout`, `success`, `cancel`, `portal`, `cancelSubscription`

### 6. Criar `routes/billing.php`
- [x] GET `/settings/billing` → `index`
- [x] POST `/billing/checkout` → `checkout`
- [x] GET `/billing/success` → `success`
- [x] GET `/billing/cancel` → `cancel`
- [x] POST `/billing/portal` → `portal`
- [x] DELETE `/billing/subscription` → `cancelSubscription`
- [x] Registrar em `routes/web.php`
- [x] Excluir `stripe/*` do CSRF em `bootstrap/app.php`

### 7. Webhook handler
- [x] Listener `App\Listeners\HandleVerifiedSubscription`
- [x] Escuta `Laravel\Cashier\Events\WebhookReceived`
- [x] `invoice.payment_succeeded` → `verified_at = now()`
- [x] `customer.subscription.deleted` / `invoice.payment_failed` → `verified_at = null`
- [x] Registrado em `AppServiceProvider::boot`
- [x] Webhook URL `/stripe/webhook` (Cashier registra auto)

### 8. Blade component `<x-verified-badge>`
- [x] `resources/views/components/verified-badge.blade.php`
- [x] SVG check azul (X-style), `aria-label="Verified account"`
- [x] Aceita prop `:user` ou `:show`

### 9. Renderizar badge
- [x] navbar (próprio nome — desktop dropdown)
- [x] `components/chirps/chirp` (autor)
- [x] `components/chirps/comment-list` (autor)
- [x] `users/show` (perfil)
- [x] `search` results (lista users)
- [ ] Command palette suggest items (JS — render via data attr) — pendente

### 10. View `/settings/billing`
- [x] `resources/views/settings/billing/index.blade.php`
- [x] Mostra status atual (Verified / Not verified / Canceled grace)
- [x] Botão "Get verified" → POST /billing/checkout
- [x] Botão "Manage billing" → POST /billing/portal
- [x] Botão "Cancel subscription" → DELETE /billing/subscription
- [x] Link "Billing" no dropdown (desktop + mobile)

### 11. Testes manuais (E2E)
- [ ] **Pendente: preencher chaves Stripe no `.env`**
- [ ] Subscribe com `4242 4242 4242 4242` → badge aparece
- [ ] Cancelar via portal → badge permanece até `current_period_end`
- [ ] Falha pagamento (`4000 0000 0000 9995`) → badge não aparece
- [ ] Webhook local: `stripe listen --forward-to localhost:8000/stripe/webhook`

### 12. Documentação
- [x] README.md: nova linha na tabela Features (Verified badge)
- [x] README.md: setup env vars Stripe + Cashier
- [x] PLAN1.md + PLAN2.md: nova seção "Pagamento / Verified Badge"
- [x] CLAUDE.md: nota sobre webhook + Cashier

### 13. Validação final
- [x] `./vendor/bin/pest` — 141 testes passando
- [x] `npm run build` — OK
- [x] `./vendor/bin/pint` — formatado

---

## Próximos passos manuais (você precisa fazer)

1. **Stripe dashboard** (https://dashboard.stripe.com — modo Test):
   - Criar Product "Chirper Verified".
   - Criar Price recorrente: R$ 5.00 / mês BRL.
   - Copiar `price_...` → `.env` `STRIPE_VERIFIED_PRICE_ID`.
   - Copiar Publishable + Secret keys → `.env`.
   - Ativar Customer Portal: Settings → Billing → Customer portal → Save.

2. **Webhook local** (em outro terminal):
   ```bash
   stripe login
   stripe listen --forward-to localhost:8000/stripe/webhook
   ```
   - Copiar `whsec_...` → `.env` `STRIPE_WEBHOOK_SECRET`.

3. **Testar fluxo**:
   - `composer dev` (sobe server + queue + vite).
   - Login como Alice → `/settings/billing` → "Get verified".
   - Pagar com `4242 4242 4242 4242`.
   - Verificar badge azul aparece no perfil + navbar.

4. **Em produção (Laravel Cloud)**:
   - Configurar webhook permanente no Stripe Dashboard apontando para `https://chirper.../stripe/webhook`.
   - Setar env vars de produção no painel Laravel Cloud.
