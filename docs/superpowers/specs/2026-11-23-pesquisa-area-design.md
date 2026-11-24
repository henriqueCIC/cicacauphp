# Área em Pesquisa

## Intenção

Permitir que o publicador classifique pesquisas por área e que visitantes filtrem a listagem pública por essa classificação.

## Decisões

- Adicionar `area` como coluna `string` opcional em `researches`.
- Centralizar a lista fixa em `Research::getAreas()`, seguindo o padrão existente de tipos.
- Usar as áreas: Agronomia; Biotecnologia; Economia e Mercado; Fitossanidade; Genética e Melhoramento; Meio Ambiente e Sustentabilidade; Produção e Manejo; Tecnologia de Alimentos; Outras.
- Manter pesquisas antigas com `area = null`, exibidas como “Sem área definida” até classificação manual.
- Preservar todos os PDFs, links e demais dados existentes.
- Incluir o campo nos formulários administrativos de criação e edição.
- Exibir a área nos cards e na página individual.
- Adicionar o filtro Área entre a busca por palavra-chave e o filtro de tipo.
- Sem filtro de área, listar pesquisas classificadas e não classificadas; com filtro, listar somente a área escolhida.

## Abordagens consideradas

- `string` opcional com lista no modelo: escolhida por ser simples, compatível e fácil de ampliar.
- `enum` no MySQL: rejeitada porque cada alteração de áreas exigiria migration.
- Tabela própria de áreas: adiada porque exigiria CRUD e relacionamento sem necessidade atual.

## Compatibilidade e rollback

- A migration de avanço apenas adiciona a coluna e não modifica registros existentes.
- O rollback remove a coluna `area`; portanto, qualquer classificação feita depois da migration seria perdida ao reverter.
- Pesquisas antigas continuam funcionando durante a transição.

## Fora do escopo

- Painel próprio para administrar áreas.
- Classificação automática dos artigos existentes.
- Alterações nos quatro atalhos de tipos da página inicial.
