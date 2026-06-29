PASSO A PASSO DE TESTE DE USABILIDADE (COBRINDO TODAS AS FUNCIONALIDADES)

Utilize dois navegadores ou duas abas anônimas:
- Alice
- Bob

==================================================
1. REGISTRO E VERIFICAÇÃO DE E-MAIL
==================================================

1. Acessar /register e criar a conta da Alice:
   - Nome
   - Username (3–30 caracteres, apenas [A-Za-z0-9_], único)
   - E-mail
   - Senha (mínimo 8 caracteres, maiúsculas/minúsculas, número e símbolo)
   - Confirmação de senha

2. Tentar registrar com:
   - E-mail inválido
   - Senha curta / sem maiúscula / sem símbolo
   - Username já em uso
   - Username com caracteres inválidos (ex: "user!")
   - Username muito curto (<3) ou longo (>30)
   - Sem confirmação de senha
   Verificar mensagens de erro.

3. Verificar CAPTCHA Cloudflare Turnstile:
   - Em produção: widget deve aparecer e bloquear submissão sem solve.
   - Em local sem chaves: validação pulada automaticamente.

4. Testar throttle de registro:
   - Mais de 5 tentativas em 1 minuto.
   - Verificar bloqueio.

5. Receber e-mail de verificação (via Resend).

6. Tentar acessar o sistema sem verificar o e-mail:
   - Deve redirecionar para /email/verify.

7. Tentar fazer logout sem verificar e-mail:
   - Deve ser bloqueado (logout exige verified).

8. Clicar em "Resend":
   - Confirmar exibição da mensagem de sucesso.
   - Throttle: 6/min.

9. Clicar no link recebido por e-mail:
   - Deve redirecionar para /
   - Exibir flash message "Email verified".

10. Repetir todo o processo para a conta do Bob.

==================================================
2. LOGIN E LOGOUT
==================================================

1. Executar logout:
   - Deve retornar para a tela de login.

2. Tentar login com senha incorreta:
   - Deve exibir erro.

3. Verificar CAPTCHA Turnstile no login (quando configurado).

4. Realizar login com credenciais corretas:
   - Deve acessar o feed.

5. Testar proteção contra força bruta:
   - Mais de 3 tentativas falhas em 15 min para o mesmo email+IP.
   - Verificar bloqueio temporário.

==================================================
3. RECUPERAÇÃO DE SENHA
==================================================

1. Acessar /forgot-password.

2. Solicitar redefinição para o e-mail da Alice.

3. Informar e-mail inexistente:
   - Verificar mensagem de erro.

4. Testar throttle: mais de 6 requests/min — bloqueio.

5. Abrir link de redefinição recebido.

6. Informar nova senha:
   - Testar confirmação divergente.
   - Verificar validações de senha forte.

7. Efetuar login utilizando a nova senha.

==================================================
4. AUTENTICAÇÃO EM DOIS FATORES (2FA/TOTP)
==================================================

1. Acessar:
   /settings/two-factor

2. Clicar em Enable.

3. Escanear QR Code em aplicativo autenticador.

4. Informar código inválido:
   - Deve exibir erro.

5. Informar código válido:
   - 2FA ativado.
   - Recovery codes exibidos.

6. Fazer logout.

7. Efetuar login novamente:
   - Deve redirecionar para /two-factor-challenge.
   - Throttle: 6/min.

8. Informar código TOTP incorreto:
   - Deve exibir erro.

9. Informar código correto:
   - Deve permitir acesso.

10. Fazer logout e login novamente.

11. Utilizar um Recovery Code:
    - Deve permitir acesso.
    - Código utilizado deve ser invalidado.

12. Regenerar Recovery Codes (exige current_password):
    - Os códigos anteriores devem deixar de funcionar.

13. Desativar o 2FA:
    - Próximo login não deve solicitar desafio.

==================================================
5. PERFIL
==================================================

1. Acessar:
   /settings/profile

2. Alterar:
   - Nome
   - Username (revalidar unicidade e regex)
   - E-mail
   - Bio
   - Avatar (upload de imagem)

3. Alterar o e-mail:
   - Deve exigir nova verificação.

4. Remover avatar:
   - Deve utilizar fallback do avatars.laravel.cloud.

==================================================
6. TEMA (DARK/LIGHT MODE)
==================================================

1. Alternar entre:
   - Dark Mode (laravelChirperDark)
   - Light Mode (laravelChirper)

2. Atualizar a página:
   - Tema deve persistir (localStorage).

3. Verificar:
   - Não deve ocorrer flash de tema incorreto ao carregar.
   - Inline <script> no <head> aplica antes do paint.

==================================================
7. CRUD DE CHIRPS E ANEXOS
==================================================

1. Criar chirp com texto simples.

2. Verificar atualização do contador de caracteres.

3. Exceder o limite permitido:
   - Envio deve ser bloqueado.

4. Criar chirp com imagem:
   - JPG / JPEG
   - PNG
   - WEBP
   - GIF
   - Tamanho ≤ 2 MB

5. Tentar anexar:
   - PDF
   - TXT
   - Imagem > 2 MB
   Deve ser rejeitado.

6. Editar chirp próprio:
   - Confirmar alteração do texto.

7. Tentar editar chirp do Bob:
   - Acessar /chirps/{id}/edit
   - Deve retornar 403.

8. Excluir chirp próprio.

9. Testar throttle:
   - Criar mais de 20 chirps por minuto.
   - Verificar bloqueio.

==================================================
8. FEED (FOR YOU / FOLLOWING)
==================================================

1. Na home /, conferir tabs:
   - "For you" (todos os chirps)
   - "Following" (chirps de seguidos + próprios)

2. Selecionar "Following" sem seguir ninguém:
   - Estado vazio deve linkar para busca/descoberta.

3. Seguir Bob, voltar em "Following":
   - Chirps do Bob devem aparecer.

4. Paginar — seleção da aba deve persistir via ?feed=following.

==================================================
9. MENÇÕES (@username)
==================================================

1. Alice cria chirp com "@bob olá":
   - Handle deve renderizar como link para perfil do Bob.
   - Bob recebe NewMentionNotification.

2. Mencionar usuário inexistente "@naoexiste":
   - Texto deve continuar como texto plano (não vira link).

3. Mencionar a si mesmo:
   - Não deve gerar notificação (self-mention silencioso).

4. Editar chirp e adicionar nova menção "@carol":
   - Apenas Carol (novo handle) recebe notificação.
   - Menções antigas não re-notificam.

5. Verificar escape XSS:
   - Tentar mention com HTML "@<script>" — deve renderizar escapado.

==================================================
10. HASHTAGS (#tag)
==================================================

1. Criar chirp com "#laravel é top".

2. Clicar no hashtag renderizado:
   - Deve abrir /tag/laravel
   - Lista deve conter todos os chirps com #laravel.

3. Acessar /tag/{slug} diretamente:
   - Deve exigir auth + verified.

4. Acessar /tag/slug-inexistente:
   - Deve mostrar lista vazia (ou 404, conforme implementação).

5. Verificar múltiplas tags no mesmo chirp:
   - #php #laravel → chirp deve aparecer em ambas as páginas.

==================================================
11. COMENTÁRIOS
==================================================

1. Bob comenta em um chirp da Alice.

2. Editar comentário inline.

3. Excluir comentário.

4. Tentar editar comentário de outro usuário:
   - Deve ser bloqueado.

5. Throttle: 20 comentários/min.

==================================================
12. REAÇÕES (CHIRPS E COMENTÁRIOS)
==================================================

1. Bob curte um chirp da Alice:
   - Contador deve aumentar.

2. Curtir novamente:
   - Reação removida.

3. Aplicar dislike após like:
   - Tipo da reação deve ser alterado.
   - Conta como nova reação (gera notificação).

4. Repetir o mesmo fluxo em comentários.

5. Alice curtir o próprio chirp:
   - Não deve gerar notificação.

6. Throttle de reações: 60/min.

==================================================
13. SEGUIDORES (FOLLOW)
==================================================

1. Bob acessa:
   /users/{alice_id}

2. Verificar exibição do botão Follow.

3. Seguir Alice:
   - Contador aumenta.
   - Alice recebe notificação.

4. Deixar de seguir:
   - Contador diminui.

5. Seguir novamente:
   - Não deve gerar nova notificação.
   - Apenas o primeiro follow notifica.

==================================================
14. BOOKMARKS
==================================================

1. Toggle de bookmark em qualquer chirp:
   - Ícone na navbar mostra badge com contagem.

2. Acessar /bookmarks:
   - Listar chirps salvos.

3. Remover bookmark:
   - Chirp original permanece intacto.
   - Contador da navbar atualiza.

4. Acessar /bookmarks sem auth:
   - Deve redirecionar para login.

==================================================
15. BUSCA E COMMAND PALETTE
==================================================

1. Utilizar busca na navbar.

2. Digitar consulta:
   - Verificar sugestões via /search/suggest.

3. Submeter busca:
   - Verificar resultados em /search?q=.

4. Testar consultas contendo:
   - %
   - _
   Verificar escape correto (LIKE-wildcard).

5. Consulta vazia:
   - Deve exibir estado neutro.

6. Command Palette:
   - Pressionar Ctrl/Cmd+K em qualquer página.
   - Modal abre com busca live-suggest (users + chirps).
   - Item "see all results" leva para /search?q=.

==================================================
16. NOTIFICAÇÕES
==================================================

1. Bob realiza:
   - Follow
   - Like em chirp da Alice
   - Comentário em chirp da Alice
   - Menciona @alice em um chirp

2. Verificar:
   - Bell exibe quantidade de não lidas (cache por usuário).

3. Alice acessa:
   /notifications

4. Verificar:
   - Notificações marcadas como lidas.
   - Bell zerado.

5. Excluir uma notificação.

6. Utilizar "Clear All":
   - Lista deve ficar vazia.

==================================================
17. PAGAMENTO / VERIFIED BADGE (STRIPE)
==================================================

1. Acessar /settings/billing autenticado:
   - Status inicial: "Not verified".
   - Botão "Get verified — R$ 5,00/mês" visível.

2. Clicar em "Get verified":
   - Redirect para Stripe Hosted Checkout.

3. Pagar com cartão de teste:
   - 4242 4242 4242 4242 / data futura / CVC qualquer.
   - Webhook local: stripe listen --forward-to localhost:8000/stripe/webhook
   - Pós-pagamento: verified_at preenchido.

4. Verificar badge azul (selo verified) aparece em:
   - Navbar (dropdown próprio nome)
   - Chirps no feed (autor)
   - Comentários (autor)
   - Página de perfil /users/{id}
   - Resultados de busca

5. Acessar /settings/billing:
   - Status "Verified" exibido.
   - Botões "Manage billing" e "Cancel subscription" visíveis.

6. Clicar "Manage billing":
   - Redirect para Stripe Customer Portal.
   - Permite atualizar cartão.

7. Clicar "Cancel subscription":
   - Confirma cancelamento.
   - Estado: "Grace period" — badge permanece até fim do ciclo pago.
   - ends_at definido.

8. Testar falha de pagamento:
   - Cartão 4000 0000 0000 9995 (insufficient funds).
   - Webhook invoice.payment_failed → verified_at = null.
   - Badge removido em todas as views.

9. Testar 3D Secure:
   - Cartão 4000 0025 0000 3155.
   - Completar challenge no Stripe.

10. Segurança:
    - POST /stripe/webhook deve estar excluído do CSRF.
    - Tentar POST /billing/checkout sem auth → redirect login.

==================================================
18. AUTORIZAÇÃO E SEGURANÇA
==================================================

1. Deslogado, tentar acessar:
   - /
   - /notifications
   - /bookmarks
   - /chirps/{id}/edit
   - /settings/profile
   - /settings/two-factor
   - /tag/{slug}

   Deve redirecionar para login.

2. Tentar enviar POST para /chirps sem CSRF:
   - Deve retornar erro 419.

3. Abrir DevTools e verificar headers:
   - Content-Security-Policy
   - Strict-Transport-Security (somente produção)
   - X-Frame-Options: DENY
   - X-Content-Type-Options: nosniff
   - Referrer-Policy: strict-origin-when-cross-origin
   - Permissions-Policy (camera/mic/geo/payment desativados)
   - COOP / CORP

4. Em produção:
   - Forçar HTTPS.
   - upgrade-insecure-requests no CSP.

5. Boot guard:
   - APP_DEBUG=true + APP_ENV=production → app deve recusar boot.

==================================================
19. RESPONSIVIDADE
==================================================

1. Abrir DevTools.

2. Ativar viewport mobile.

3. Verificar navbar:
   - Deve colapsar em dropdown hambúrguer.

4. Reexecutar todos os testes dos itens 1 ao 18.

==================================================
COBERTURA
==================================================

Objetivo:
Garantir cobertura completa de todas as rotas definidas em routes/*.php:
auth, password, profile, chirps, verification, comments, follows, users,
search, notifications, bookmarks, tags, two_factor.
