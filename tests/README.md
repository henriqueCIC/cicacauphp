# Testes do CICacau

O projeto usa Laravel 12, Pest 4 e PHPUnit. Os testes automatizados ficam separados em:

- `Unit/`: regras pequenas exercitadas sem rota, upload ou gravação no banco;
- `Feature/`: fluxos de integração que atravessam rota, middleware, controller, banco, view e armazenamento;
- `PLANO_COMPLETO_DE_TESTES.md`: catálogo do que deverá ser testado nas demais demandas.

## Legislação

Arquivos atuais:

- `Unit/LegislationTest.php`: conversão das datas do modelo;
- `Feature/LegislationPageTest.php`: página pública, visibilidade, ordenação, paginação, dados do card e compatibilidade com registros antigos;
- `Feature/AdminLegislationTest.php`: autenticação, permissões, validação, cadastro, edição, publicação, rascunho, upload e exclusão.

Última execução local registrada em 18/11/2026:

- Legislação: 25 testes aprovados, 130 verificações;
- suíte completa atual: 27 testes aprovados, 132 verificações.

Os testes de integração usam `DatabaseTransactions`. Cada teste inicia uma transação e a desfaz ao terminar. `Storage::fake('public')` cria um armazenamento temporário para que os PDFs reais não sejam tocados.

## Como executar

Execute os comandos a partir da raiz do projeto:

```bash
cd /Users/henriquebarreto/Documents/Projetos/cicacauphp-dev
```

Teste unitário de Legislação:

```bash
php artisan test tests/Unit/LegislationTest.php
```

Página pública de Legislação:

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=cicacau_local DB_USERNAME=root DB_PASSWORD= php artisan test tests/Feature/LegislationPageTest.php
```

Área administrativa de Legislação:

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=cicacau_local DB_USERNAME=root DB_PASSWORD= php artisan test tests/Feature/AdminLegislationTest.php
```

Todos os testes automatizados de Legislação:

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=cicacau_local DB_USERNAME=root DB_PASSWORD= php artisan test tests/Unit/LegislationTest.php tests/Feature/AdminLegislationTest.php tests/Feature/LegislationPageTest.php
```

Executar somente um caso pelo nome:

```bash
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=cicacau_local DB_USERNAME=root DB_PASSWORD= php artisan test --filter="future publication remains hidden"
```

Parar no primeiro erro:

```bash
php artisan test --stop-on-failure
```

Executar apenas uma suíte:

```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Relatório de cobertura, quando Xdebug ou PCOV estiver instalado:

```bash
php artisan test --coverage
```

## Como ler o resultado

- `PASS`: o comportamento esperado foi confirmado;
- `FAIL`: uma afirmação recebeu um resultado diferente do esperado;
- `ERROR`: o teste não conseguiu chegar à afirmação, geralmente por configuração, conexão ou exceção;
- `assertions`: quantidade de verificações executadas;
- código de saída `0`: todos passaram;
- código de saída diferente de `0`: houve falha ou erro.

## Limitação atual da suíte completa

O `phpunit.xml` configura SQLite em memória. Uma migração antiga de `production_costs` não é compatível com esse banco e impede `RefreshDatabase` de executar todas as migrações. Por isso, os testes de integração de Legislação são chamados explicitamente com o MySQL local já migrado e usam transações. O banco de produção nunca deve ser usado nesses comandos.

Antes de automatizar a suíte completa em CI, deve ser criado um banco exclusivo, por exemplo `cicacau_test`, e a migração antiga deve ser tornada compatível ou os testes devem ser padronizados para o mesmo mecanismo de banco do projeto.

## Testes manuais complementares

Os testes PHP não comprovam pixels ou responsividade. Depois da bateria automatizada, conferir no navegador:

1. três cards por fileira no computador, dois no tablet e um no celular;
2. ausência de rolagem horizontal;
3. descrição limitada a quatro linhas;
4. título, fonte e autor longos dentro do card;
5. PDF e link externo abrindo em nova aba;
6. navegação por teclado e foco visível;
7. menu Legislação em Destaques, após Entrevistas, no computador e no celular.
