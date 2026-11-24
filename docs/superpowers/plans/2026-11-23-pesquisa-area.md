# Área em Pesquisa Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir que publicadores classifiquem pesquisas por uma área fixa e que visitantes visualizem e filtrem essa área na página pública.

**Architecture:** Adicionar uma coluna `area` nullable para compatibilidade, guardar chaves estáveis e centralizar seus rótulos no modelo `Research`. O painel exigirá área em novas pesquisas e aceitará `null` ao editar registros antigos; a listagem pública manterá registros sem área visíveis quando nenhum filtro estiver ativo.

**Tech Stack:** Laravel 12.35, PHP 8.4, Blade, Eloquent, MySQL, Pest.

**Spec:** `docs/superpowers/specs/2026-11-23-pesquisa-area-design.md`

## Global Constraints

- Preservar pesquisas, PDFs, links e demais dados existentes.
- Não instalar dependências nem criar um CRUD de áreas.
- Usar as chaves estáveis `agronomia`, `biotecnologia`, `economia_mercado`, `fitossanidade`, `genetica_melhoramento`, `meio_ambiente_sustentabilidade`, `producao_manejo`, `tecnologia_alimentos` e `outras`.
- Exibir os rótulos aprovados no documento de design.
- Manter `area = null` para registros antigos até classificação manual.
- Executar testes com MySQL local, pois o erro de `DISTINCT` não é reproduzido de forma confiável em SQLite.
- Manter `graphify-out/`, `.env`, uploads e dados locais fora dos commits.

## Review Focus

- Pesquisa antiga com `area = null`: continua acessível e mostra “Sem área definida”.
- Valor de área fora da lista: cadastro e edição rejeitam o valor.
- Filtro válido: inclui somente a área escolhida e preserva os demais parâmetros da URL.
- Sem filtro: pesquisas classificadas e não classificadas continuam visíveis.
- Rollback: remove apenas a coluna `area`, sem tocar em PDFs ou outras colunas.

---

### Task 0: Versionar o desenho e o plano aprovados

**Files:**
- Add: `docs/superpowers/specs/2026-11-23-pesquisa-area-design.md`
- Add: `docs/superpowers/plans/2026-11-23-pesquisa-area.md`

**Interfaces:**
- Consumes: decisões aprovadas com o usuário.
- Produces: referência versionada para todas as tarefas seguintes.

- [x] **Step 1: Revisar os dois documentos contra as decisões aprovadas**

Confirmar lista de áreas, compatibilidade de registros antigos, posição do filtro e ausência de classificação automática.

- [x] **Step 2: Commitar a documentação de planejamento**

```bash
git add docs/superpowers/specs/2026-11-23-pesquisa-area-design.md docs/superpowers/plans/2026-11-23-pesquisa-area.md
git commit -m "docs: planeja áreas em pesquisas"
```

### Task 1: Isolar a correção MySQL já reproduzida

**Files:**
- Modify: `app/Http/Controllers/ResearchController.php:41-45`
- Test: `tests/Feature/ResearchPageTest.php`

**Interfaces:**
- Consumes: `Research::published()` e rota `pesquisa.index`.
- Produces: consulta de anos compatível com MySQL por meio de `reorder('year', 'desc')`.

- [ ] **Step 1: Confirmar o ciclo de regressão já observado**

Registrar que o teste falhou antes com HTTP 500 / MySQL 3065 e passou após substituir `orderBy` por `reorder`.

- [ ] **Step 2: Executar o teste focado**

Run: `DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=cicacau_local DB_USERNAME=root DB_PASSWORD= php artisan test tests/Feature/ResearchPageTest.php`

Expected: PASS, 1 teste e 2 assertions.

- [ ] **Step 3: Commitar a correção separadamente**

```bash
git add app/Http/Controllers/ResearchController.php tests/Feature/ResearchPageTest.php
git commit -m "fix: corrige filtro de anos de pesquisas"
```

### Task 2: Adicionar área ao banco e ao domínio

**Files:**
- Create: `database/migrations/2026_11_23_000001_add_area_to_researches_table.php`
- Modify: `app/Models/Research.php`
- Test: `tests/Unit/ResearchTest.php`

**Interfaces:**
- Consumes: tabela `researches` e o padrão existente `Research::getTypes()`.
- Produces: atributo nullable `area`, `Research::getAreas(): array` e `Research::getAreaName(): string`.

- [ ] **Step 1: Escrever os testes unitários que falham**

Criar testes que exijam as nove chaves/rótulos aprovados e que `getAreaName()` retorne o rótulo correto ou “Sem área definida” para `null`.

- [ ] **Step 2: Executar os testes unitários e confirmar a falha**

Run: `php artisan test tests/Unit/ResearchTest.php`

Expected: FAIL porque `getAreas()` e `getAreaName()` ainda não existem.

- [ ] **Step 3: Criar a migration reversível**

Adicionar `string('area')->nullable()->after('type')->index()` no `up()` e remover o índice/coluna no `down()`.

- [ ] **Step 4: Implementar a API de área no modelo**

Adicionar `area` a `$fillable`, criar `getAreas(): array` com as chaves estáveis e criar `getAreaName(): string` com fallback “Sem área definida”.

- [ ] **Step 5: Aplicar a migration e verificar preservação**

Run: `php artisan migrate`

Expected: migration aplicada; registros existentes permanecem e recebem `area = null`.

- [ ] **Step 6: Executar os testes unitários**

Run: `php artisan test tests/Unit/ResearchTest.php`

Expected: PASS.

- [ ] **Step 7: Commitar domínio e schema**

```bash
git add app/Models/Research.php database/migrations/2026_11_23_000001_add_area_to_researches_table.php tests/Unit/ResearchTest.php
git commit -m "feat: adiciona áreas às pesquisas"
```

### Task 3: Permitir classificação pelo painel administrativo

**Files:**
- Modify: `app/Http/Controllers/Admin/ResearchController.php`
- Modify: `resources/views/admin/researches/create.blade.php`
- Modify: `resources/views/admin/researches/edit.blade.php`
- Create: `tests/Feature/AdminResearchAreaTest.php`

**Interfaces:**
- Consumes: `Research::getAreas()` e coluna `researches.area` da Task 2.
- Produces: selects administrativos `name="area"`, cadastro obrigatório e edição compatível com registros antigos.

- [ ] **Step 1: Escrever testes administrativos que falham**

Cobrir: publicador autorizado visualiza as áreas; cadastro salva uma chave válida; cadastro sem área falha; valor desconhecido falha; edição salva a área; pesquisa antiga sem área abre e pode ser classificada.

- [ ] **Step 2: Executar os testes e confirmar a falha**

Run: `php artisan test tests/Feature/AdminResearchAreaTest.php`

Expected: FAIL porque o controlador e os formulários ainda não conhecem `area`.

- [ ] **Step 3: Passar a lista para criação e edição**

Nos métodos `create()` e `edit()`, obter `Research::getAreas()` e incluir `$areas` no `compact` das views.

- [ ] **Step 4: Validar e persistir a área**

No `store()`, usar `required|in:<chaves aprovadas>`; no `update()`, usar `nullable|in:<chaves aprovadas>` para permitir edição gradual dos registros antigos.

- [ ] **Step 5: Adicionar os selects administrativos**

Na criação, incluir placeholder obrigatório “Selecione uma área”. Na edição, incluir “Sem área definida” para registros antigos e preservar `old('area', $research->area)`.

- [ ] **Step 6: Executar os testes administrativos**

Run: `php artisan test tests/Feature/AdminResearchAreaTest.php`

Expected: PASS.

- [ ] **Step 7: Commitar o painel**

```bash
git add app/Http/Controllers/Admin/ResearchController.php resources/views/admin/researches/create.blade.php resources/views/admin/researches/edit.blade.php tests/Feature/AdminResearchAreaTest.php
git commit -m "feat: permite classificar pesquisas por área"
```

### Task 4: Exibir e filtrar área na consulta pública

**Files:**
- Modify: `app/Http/Controllers/ResearchController.php`
- Modify: `resources/views/pesquisa.blade.php`
- Modify: `resources/views/pesquisa-single.blade.php`
- Modify: `tests/Feature/ResearchPageTest.php`

**Interfaces:**
- Consumes: `Research::getAreas()`, `Research::getAreaName()` e coluna `researches.area`.
- Produces: parâmetro público `area=<chave>`, filtro visual e rótulos em cards/detalhes.

- [ ] **Step 1: Escrever testes públicos que falham**

Cobrir: ausência de filtro mostra pesquisas com e sem área; `area=agronomia` inclui apenas Agronomia; valor desconhecido não causa erro nem aplica filtro; cards e detalhe mostram o rótulo; registro antigo mostra “Sem área definida”; parâmetros `busca`, `tipo`, `area` e `ano` permanecem na paginação/limpeza.

- [ ] **Step 2: Executar os testes públicos e confirmar a falha**

Run: `php artisan test tests/Feature/ResearchPageTest.php`

Expected: FAIL porque o filtro e os rótulos ainda não existem.

- [ ] **Step 3: Implementar a consulta pública**

No `index()`, passar `$areas = Research::getAreas()` à view e aplicar `where('area', $request->area)` somente quando a chave existir na lista.

- [ ] **Step 4: Adicionar o filtro na posição aprovada**

Em `pesquisa.blade.php`, inserir o select Área entre o campo de busca e “Todos os tipos”; incluir `area` nos controles de filtros ativos e limpar filtros.

- [ ] **Step 5: Exibir a área nas páginas públicas**

Mostrar `getAreaName()` nos cards e na seção de metadados da página individual, incluindo “Sem área definida” para registros antigos.

- [ ] **Step 6: Executar os testes públicos**

Run: `php artisan test tests/Feature/ResearchPageTest.php`

Expected: PASS.

- [ ] **Step 7: Commitar a consulta pública**

```bash
git add app/Http/Controllers/ResearchController.php resources/views/pesquisa.blade.php resources/views/pesquisa-single.blade.php tests/Feature/ResearchPageTest.php
git commit -m "feat: adiciona filtro de área em pesquisas"
```

### Task 5: Verificação integrada e documentação

**Files:**
- Modify: `tests/README.md`
- Verify: todos os arquivos das Tasks 1-4.

**Interfaces:**
- Consumes: fluxo administrativo e público completo.
- Produces: comandos documentados e branch pronta para revisão humana.

- [ ] **Step 1: Documentar os testes de Pesquisa**

Adicionar ao `tests/README.md` os comandos focados de `ResearchTest`, `AdminResearchAreaTest` e `ResearchPageTest` com MySQL local.

- [ ] **Step 2: Executar a suíte completa**

Run: `DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=cicacau_local DB_USERNAME=root DB_PASSWORD= php artisan test`

Expected: todos os testes passam sem falhas.

- [ ] **Step 3: Verificar formatação e whitespace**

Run: `vendor/bin/pint --test && git diff --check`

Expected: nenhum erro de estilo ou whitespace.

- [ ] **Step 4: Validar no Chrome**

Como publicador: criar e editar uma pesquisa com área. Como visitante: testar ausência de filtro, cada área, combinação com tipo/ano e exibição do registro antigo sem área.

- [ ] **Step 5: Commitar a documentação**

```bash
git add tests/README.md
git commit -m "docs: registra testes de áreas em pesquisas"
```
