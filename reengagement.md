# Preve — Reengajamento & Hiato

Design para trazer de volta usuários inativos e permitir que registrem períodos de ausência ("hiato") com um fluxo de retorno que reconcilia saldo, caixinhas e recorrentes.

Motivação real: ficamos ~60 dias sem usar o sistema. Ao voltar: saldo calculado ≠ saldo real, caixinhas desatualizadas, e as recurring transactions **continuaram gerando lançamentos** durante todo o período (o scheduler roda `recurring:generate` semanalmente). O retorno hoje é doloroso — e é exatamente aí que a maioria dos usuários desiste.

---

## Definição de "inativo"

> Usuário sem nenhuma `Transaction` criada **manualmente** há mais de 30 dias.

- Query base: última `transactions.created_at` com `recurring_transaction_id IS NULL`, por usuário.
- Usar `created_at` (quando o usuário agiu), **não** `transaction_date` (que pode ser retroativa/futura).
- Não precisa de coluna nova pra detectar — o FK `recurring_transaction_id` já existe.
- Edge case: usuário que nunca criou transação nenhuma → é problema de **onboarding**, não de reengajamento. Excluir do e-mail (ou tratar como segmento separado, V2).
- Edge case: usuário só edita/deleta transações antigas mas não cria → tecnicamente ativo. MVP: ignorar (criar transação é o comportamento que queremos de volta).

```sql
-- rascunho da query de segmentação
SELECT u.id, u.email, MAX(t.created_at) AS last_manual_activity
FROM users u
LEFT JOIN transactions t ON t.user_id = u.id AND t.recurring_transaction_id IS NULL
GROUP BY u.id, u.email
HAVING MAX(t.created_at) < NOW() - INTERVAL '30 days'
   AND MAX(t.created_at) IS NOT NULL;
```

---

## Visão geral das 4 peças

| # | Peça | Objetivo |
| --- | --- | --- |
| 1 | **E-mail de reengajamento** | Puxar o usuário de volta pro app |
| 2 | **Banner "bora voltar?"** | Receber quem voltou e apontar pro fluxo de retorno |
| 3 | **Hiato** | Registrar o período de ausência (dado semântico: "esse buraco no gráfico tem explicação") |
| 4 | **Fluxo de retorno (wizard)** | Reconciliar saldo, caixinhas e recorrentes em um só lugar |

Ordem de implementação sugerida: **3 → 4 → 2 → 1**. O e-mail e o banner apontam pro fluxo de retorno — não faz sentido chamar o usuário de volta pra uma porta que não existe. Mas o e-mail one-shot pra base atual (ver Fase 0) pode sair antes se quisermos.

---

## 1. E-mail de reengajamento

### Estado atual

- `app/Mail/` e `app/Notifications/` **não existem** — zero infra de e-mail além do reset/verify do Fortify.
- Queue já configurada (`QUEUE_CONNECTION=redis` no `.env.example`, tabela `jobs` existe).
- Mailpit configurado pra dev local (`MAIL_HOST=mailpit`).
- `User` já usa `Notifiable` → `$user->notify(...)` funciona imediatamente.

### Backend

- **Notification** (não Mailable direto): `ReengagementNotification implements ShouldQueue` — via `mail`, template Markdown (`resources/views/mail/`). Notification em vez de Mailable porque de graça ganhamos outros canais no futuro e o padrão `$user->notify()`.
- **Command**: `users:notify-inactive {--days=30} {--dry-run}` em `app/Console/Commands/`.
  - Copiar o padrão de `GenerateRecurringTransactions`: `chunkById(100)` + dispatch por usuário.
  - `--dry-run` lista quem receberia, sem enviar (essencial pro primeiro disparo na base real).
- **Service**: `InactivityService` (segue a regra do CLAUDE.md — nada de query no command além do chunk):
  - `lastManualActivity(User): ?CarbonInterface`
  - `isInactive(User, int $days = 30): bool`
  - `inactiveUsersQuery(int $days): Builder` — usado pelo command.
- **Anti-spam — coluna nova em `users`**: `reengagement_notified_at (timestamp, nullable)`.
  - Regra: não reenviar se já notificado nos últimos N dias (sugestão: 30). Sem isso, o command semanal viraria spam semanal.
  - Zerar quando o usuário cria uma transação manual (observer já existe: `TransactionObserver::created`).
- **Supressões**: não enviar se o usuário tem hiato ativo cobrindo o período (ele já avisou que ia sumir), nem se `email_verified_at IS NULL`.
- **Scheduler** (`routes/console.php`): `Schedule::command('users:notify-inactive')->weekly();`

### Conteúdo do e-mail

- Tom: leve, pessoal, curto. Sem cara de marketing automation.
- Corpo: "faz X dias que você não registra nada" + 1 dado concreto pra gerar curiosidade/FOMO (ex: "suas recorrentes continuaram rodando: R$ X lançados desde então") + CTA único.
- CTA: link direto pro **fluxo de retorno** (peça 4), não pra home genérica.
- Respeitar `users.locale` (campo já existe) — copy em `lang/` PT/EN.

### Fase 0 — disparo one-shot pra base atual

O primeiro envio pra base existente é só rodar o command manualmente:

```bash
make bash-app
php artisan users:notify-inactive --dry-run   # conferir a lista
php artisan users:notify-inactive
```

Sem código descartável — o mesmo command vira o recorrente do scheduler.

---

## 2. Banner "bora voltar?"

### Comportamento

- Aparece pra usuário logado que está inativo (mesma regra: 30+ dias sem transação manual).
- Copy na linha de: *"Faz mais de 30 dias que você não registra nada por aqui. Bora voltar?"*
- Duas ações:
  1. **CTA primário**: "Atualizar minhas finanças" → abre o fluxo de retorno (peça 4).
  2. **Secundário**: "Registrar hiato" → registrar o período de ausência (peça 3).
- Dismissível, mas com memória curta: sumiu por 7 dias, volta se continuar inativo. Persistir dismiss em coluna `reengagement_banner_dismissed_at` no user (não localStorage — o usuário pode logar de outro device).
- Some imediatamente quando: cria transação manual, registra hiato, ou completa o fluxo de retorno.

### Backend

- Prop compartilhada em `HandleInertiaRequests::share()` (já compartilha `auth.user`, `sidebarOpen`): algo como `reengagement: ['inactive_days' => int|null, 'show_banner' => bool]`, calculada via `InactivityService` (mesmo service do e-mail — uma regra só, dois consumidores).
- Cuidado com custo: `share()` roda em toda request. Cachear `lastManualActivity` por usuário (cache redis, TTL curto, invalidado no `TransactionObserver::created`).
- Endpoint de dismiss: `POST /reengagement/dismiss` (controller fino → service).
- ⚠️ `Dashboard.vue` faz partial reload com `only: [...]` — prop compartilhada via middleware não sofre com isso, mais um motivo pra não passar como prop da página.

### Frontend

- `components/Reengagement/ReengagementBanner.vue`, montado em `AppSidebarLayout`/`AppHeaderLayout` (acima do slot de conteúdo) pra aparecer em todas as páginas.
- Reutilizar `ui/alert` (`Alert`/`AlertTitle`/`AlertDescription`) — hoje só existe a variante destructive em uso (`AlertError.vue`); criar uso com variante default/info + botões.
- i18n via `vue-i18n` (padrão já usado no app).

---

## 3. Hiato

### O que é (e o que não é)

Hiato é **metadado**: "de tal data a tal data eu não registrei nada, e eu sei disso". Serve pra:

- Explicar buracos em gráficos/histórico (tooltip/faixa sombreada nos charts — V2 visual, mas o dado já fica certo desde o MVP).
- Suprimir e-mail de reengajamento e banner durante o hiato.
- Contextualizar o fluxo de retorno ("seu hiato foi de 12/mai a 26/jul — vamos reconciliar esse período").
- Excluir o período de médias/estatísticas que ficariam distorcidas (ex: `SavingsHistoryService::averageMonthlyContribution` — média mensal cai artificialmente com meses vazios de hiato). Decidir caso a caso quais métricas respeitam hiato.

Hiato **não** deleta nem altera transação nenhuma por si só. Reconciliação é a peça 4.

### Modelagem

**Nova entidade `Hiatus`** (tabela `hiatuses`):

| Campo | Tipo | Notas |
| --- | --- | --- |
| `id` | bigint | PK |
| `user_id` | FK users | user-scoped |
| `started_at` | date | início da ausência |
| `ended_at` | date, nullable | `null` = hiato em aberto ("ainda estou fora" / registrado antecipadamente) |
| `note` | string, nullable | "viagem", "correria no trabalho"... |
| `reconciled_at` | timestamp, nullable | setado quando o fluxo de retorno é concluído pra esse hiato |
| timestamps | | |

Entidade própria (e não campos no user) porque hiatos se repetem — em 1 ano de uso o usuário pode ter 3. Histórico importa.

Dois jeitos de nascer um hiato:

1. **Retroativo** (caso principal): usuário voltou, banner sugere o período detectado (`última transação manual → hoje`) pré-preenchido, ele ajusta e confirma.
2. **Antecipado**: "vou viajar 1 mês" → registra com `ended_at` futuro ou null. Bônus: suprime o e-mail de reengajamento antes mesmo dele existir.

### Backend

- Migration, Model (`belongsTo(User)`, scopes `active()`, `covering(CarbonInterface)`), Factory.
- `HiatusController` (index/store/update/destroy — fino), `CreateHiatusRequest`/`UpdateHiatusRequest`, `HiatusPolicy`.
- Validação: `ended_at >= started_at`; impedir sobreposição com hiato existente do mesmo user.
- Relação no `User`: `hasMany(Hiatus)`.

### Frontend

- Type `IHiatus` em `types/models/hiatus.d.ts`.
- `components/Hiatus/RegisterHiatusDialog.vue` — dialog simples (período pré-preenchido + note), aberto pelo banner ou por Settings.
- Gestão completa (listar/editar/excluir) pode morar em Settings — V2; MVP só cria via banner/fluxo de retorno.

### Interação com recorrentes

Hiato antecipado poderia **pausar** recorrentes (`is_active = false`) e reativar no fim. Decisão: **V2**. No MVP as recorrentes continuam rodando e o acerto acontece na reconciliação — menos mágica, menos surpresa. (O lever já existe: `RecurringTransaction.is_active`.)

---

## 4. Fluxo de retorno (wizard de reconciliação)

O coração da proposta. Página/dialog em passos que responde: *"o que o sistema acha que aconteceu vs. o que realmente aconteceu enquanto você esteve fora?"*

### Princípio inegociável

**Toda correção vira `Transaction` de ajuste — nunca edição direta de valor calculado.**

- Não existe saldo persistido: saldo = agregação de transactions (`Transaction::netBalance`). Só dá pra "corrigir saldo" criando transação.
- `SavingsBucket.current_amount` é cache denormalizado, e `SavingsHistoryService` **recomputa o histórico a partir das transactions** — escrever `current_amount` na mão faz o gráfico de savings divergir do total pra sempre. Ajuste de caixinha = transaction com `savings_bucket_id` (o `TransactionObserver` + `SavingsBucketBalanceService` já propagam o delta sozinhos).

### Passos do wizard

**Passo 1 — Revisar recorrentes geradas no período**
- Listar transactions com `recurring_transaction_id NOT NULL` e `transaction_date` dentro do hiato.
- Por linha: ✅ manter (aconteceu de verdade) / ❌ excluir (não aconteceu). Ações em lote ("manter todas" / "excluir todas").
- Aproveitar pra perguntar: alguma recorrente morreu de vez? → desativar (`is_active = false`).

**Passo 2 — Acertar o saldo**
- Mostrar saldo calculado atual e input "quanto você tem de verdade?" (máscara de moeda padrão do app).
- Diferença → transação de ajuste: `INCOME` ou `EXPENSE` conforme o sinal, `description` = "Ajuste de saldo (retorno de hiato)", `transaction_date` = hoje.
- Categoria: **"Ajustes"** — decidir se criamos uma categoria de sistema por usuário ou deixamos o usuário escolher (ver Decisões em aberto).

**Passo 3 — Acertar caixinhas**
- Uma linha por `SavingsBucket`: valor atual no sistema + input do valor real.
- Diferença → transação de ajuste com `savings_bucket_id`. ⚠️ Convenção de sinal já existente no `SavingsBucketBalanceService`: `EXPENSE` + bucket = aporte (+), `INCOME` + bucket = saque (−).
- ⚠️ Interação com o Passo 2: ajuste de caixinha também mexe no saldo da conta. **Ordem importa: acertar caixinhas ANTES do saldo final**, ou calcular o ajuste de saldo por último considerando os ajustes de caixinha. Sugestão: inverter a ordem dos passos na UI (recorrentes → caixinhas → saldo) — saldo por último, sempre.

**Passo 4 — Fechar**
- Resumo do que foi feito; marca `hiatus.reconciled_at = now()`; some o banner; toast de boas-vindas.
- Opcional: "adiciona aí as 2–3 transações grandes que você lembra do período" — link pro create transaction com data retroativa. Nice-to-have, não bloqueia.

### Backend

- `ReconciliationService`:
  - `pendingRecurringTransactions(User, Hiatus): Collection`
  - `balanceAdjustment(User, int $realBalanceCents): ?Transaction` — delega criação ao `TransactionService` existente (pra passar por observers/validações normais)
  - `bucketAdjustments(User, array $realAmounts): array`
  - `complete(Hiatus): void`
- `ReconciliationController` — fino: `show` (renderiza wizard com payload do service), `store` por passo ou um `store` único. Sugestão: **um POST por passo** (mais simples de errar pouco, usuário pode parar no meio e voltar).
- Rota: `/welcome-back` ou `/reconciliation`. Acessível mesmo sem hiato registrado (cria o hiato retroativo no primeiro passo se não existir).

### Frontend

- `pages/Reconciliation.vue` (ou dialog multi-step — página é melhor: fluxo longo, precisa de link direto do e-mail).
- `components/Reconciliation/`: `RecurringReviewStep.vue`, `BucketAdjustStep.vue`, `BalanceAdjustStep.vue`, `SummaryStep.vue`.
- `useForm()` por passo, padrão do app.

---

## Decisões em aberto

1. **Categoria dos ajustes**: criar categoria de sistema "Ajustes" automaticamente pro usuário (seeder/observer) vs. pedir pra escolher. *Recomendação: criar automaticamente — menos fricção no wizard; transações de ajuste identificáveis nos relatórios.*
2. **Transação de ajuste entra nos relatórios de gasto/renda do mês?** Um ajuste de −R$ 2.000 no dia da volta distorce "gastos de julho". Opções: (a) aceitar a distorção no MVP; (b) flag `is_adjustment` na transaction e excluir das métricas. *Recomendação: (a) no MVP, com a categoria "Ajustes" permitindo filtrar visualmente; (b) fica anotado pra V2 — mexe em muitos services.*
3. **Threshold de 30 dias**: fixo ou configurável por usuário? *Recomendação: constante no MVP (config `preve.inactivity_days`), configurável nunca/V3.*
4. **E-mail com dado concreto** ("suas recorrentes lançaram R$ X") exige query por usuário no envio — ok em queue, mas decidir se o MVP do e-mail já tem isso ou vai genérico primeiro.
5. **Hiato aparece nos gráficos** (faixa sombreada no `ChartMonthly`/calendário)? *V2 — o dado já fica registrado certo desde o MVP.*

## Fora de escopo (V2+)

- Pausar/reativar recorrentes automaticamente pelo hiato.
- Flag `is_adjustment` + exclusão de métricas.
- Faixa de hiato nos gráficos.
- Sequência de e-mails (D+30, D+60, D+90) — MVP é 1 e-mail com cooldown.
- Segmento "nunca criou transação" (onboarding drip).
- Gestão de hiatos em Settings.

---

## Plano de implementação (PRs pequenos)

| # | PR | Conteúdo |
| --- | --- | --- |
| 1 | `Hiatus` core | migration, model, policy, requests, controller, `InactivityService`, testes |
| 2 | Banner | shared prop no middleware + cache, `ReengagementBanner.vue`, dismiss endpoint, `RegisterHiatusDialog.vue` |
| 3 | Wizard — recorrentes | `ReconciliationService::pendingRecurringTransactions`, página + passo 1 |
| 4 | Wizard — caixinhas + saldo | passos 2 e 3, transações de ajuste, categoria "Ajustes" |
| 5 | E-mail | Notification, command `users:notify-inactive`, coluna `reengagement_notified_at`, scheduler, disparo one-shot |

Cada PR com testes Pest (feature) — a segmentação de inativos e a matemática dos ajustes são os pontos que mais merecem cobertura.
