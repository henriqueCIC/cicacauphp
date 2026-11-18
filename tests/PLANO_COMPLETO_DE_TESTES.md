# Plano completo de testes das demandas CICacau

Este arquivo guarda os cenários planejados. A implementação automatizada atual está concentrada em Legislação. Pesquisa e Mercado devem receber seus testes quando essas funcionalidades forem implementadas.

## Legislação — automatizados nesta etapa

### Modelo

- converter `original_publication_date` em objeto de data;
- converter `published_at` em objeto de data e hora.

### Página pública

- abrir sem autenticação;
- mostrar estado vazio sem publicações;
- mostrar somente publicações com data presente ou passada;
- ocultar rascunhos;
- ocultar publicações futuras;
- mostrar título, descrição, fonte, data original, data no CICacau, autor, PDF e link;
- preservar compatibilidade com registros antigos sem fonte e data original;
- ordenar da publicação mais recente para a mais antiga;
- paginar 12 itens por página;
- limitar descrições antigas a 200 caracteres e quatro linhas sem alterar o banco;
- escapar conteúdo informado pelo usuário por meio da saída padrão do Blade.

### Administração e permissões

- redirecionar visitante para o login em todas as rotas internas;
- retornar acesso proibido para usuário sem `is_admin`;
- permitir formulário para superadmin, admin e publicador administrativos;
- registrar o usuário autenticado como publicador, ignorando `user_id` enviado pelo formulário;
- listar publicações e rascunhos no painel;
- preservar a proteção das rotas mesmo em requisição direta.

### Validação e cadastro

- exigir título, descrição, data original, fonte, PDF e link externo;
- aceitar descrição com exatamente 200 caracteres;
- rejeitar descrição com 201 caracteres;
- rejeitar título e fonte acima de 255 caracteres;
- rejeitar data inválida;
- rejeitar URL inválida e protocolo `javascript:`;
- rejeitar arquivo que não seja PDF;
- rejeitar PDF acima de 10 MB;
- armazenar PDF válido com nome interno único;
- publicar imediatamente quando a opção estiver marcada;
- criar rascunho quando a opção não estiver marcada;
- exibir mensagem de sucesso e redirecionar à lista.

### Exclusão

- remover o registro selecionado;
- remover o PDF correspondente;
- preservar outros registros;
- preservar PDFs de outros registros;
- negar exclusão a visitante e usuário sem acesso administrativo;
- tratar exclusão quando o arquivo já não existir.

### Edição e status

- abrir formulário preenchido com os dados existentes;
- atualizar os campos sem alterar o publicador original;
- manter o PDF quando nenhum arquivo novo for enviado;
- substituir o PDF e remover o arquivo anterior;
- publicar um rascunho;
- devolver uma publicação ao estado de rascunho;
- refletir imediatamente o status na página pública;
- negar edição e mudança de status a visitante e usuário sem acesso administrativo.

### Testes manuais e visuais

- três cards no desktop, dois no tablet e um no celular;
- cards com altura estável e descrição de quatro linhas;
- quebra de palavras longas;
- ausência de rolagem horizontal;
- menu público e administrativo;
- abertura real do PDF e do link externo;
- teclado, foco, leitor de tela, contraste e ampliação do navegador.

## Pesquisa — executar quando implementada

### Área da publicação

- cadastrar e editar pesquisa com área;
- validar área obrigatória ou opcional conforme a regra definida;
- rejeitar área inexistente ou manipulada;
- preservar pesquisas antigas sem área;
- mostrar a área correta no painel e na página pública.

### Filtro público

- posicionar área entre palavra-chave e tipo;
- filtrar somente pesquisas da área escolhida;
- restaurar tudo em “Todas as áreas”;
- combinar área, palavra-chave e tipo;
- manter parâmetros ao paginar, atualizar e compartilhar a URL;
- tratar acentos e maiúsculas;
- ocultar rascunhos em qualquer combinação.

### Quatro botões

- testar Artigos e Documentos;
- testar Livros;
- testar Patentes;
- testar Teses e Dissertações;
- confirmar destino, filtro e resultados de cada botão;
- testar categoria vazia, troca entre categorias, teclado e celular.

## Mercado — executar quando implementado

### Integração com API

- processar resposta válida;
- validar valor, moeda, unidade, origem e horário;
- tratar resposta incompleta ou malformada;
- tratar indisponibilidade, timeout, autenticação e limite de consultas;
- rejeitar valores impossíveis conforme as regras do domínio;
- simular a API nos testes automatizados;
- impedir exposição de credenciais.

### Atualização automática

- executar no intervalo definido;
- impedir duplicidade e concorrência;
- guardar data da última atualização válida;
- preservar o último valor confiável quando a API falhar;
- recuperar na execução seguinte;
- comprovar agendamento no ambiente de homologação.

### Exibição

- mostrar valores nacionais e internacionais nos campos corretos;
- formatar moeda, unidade, casas decimais e data;
- distinguir ausência de dado de valor zero;
- informar dado antigo ou indisponível;
- testar conversões com valores conhecidos, caso sejam implementadas.

## Segurança geral

- impedir HTML e JavaScript executável em campos textuais;
- testar parâmetros extras e alteração de campos internos;
- verificar autorização no servidor, além da visibilidade dos botões;
- verificar CSRF nas operações de escrita;
- ocultar credenciais, stack traces e configuração interna;
- impedir execução de arquivos enviados;
- revisar acesso direto a PDFs de rascunhos.

## Migrações e banco

- criar estrutura em banco vazio;
- migrar uma cópia da estrutura anterior;
- preservar registros existentes;
- validar chaves estrangeiras e exclusão de usuários com conteúdo;
- testar rollback em banco descartável;
- executar migrações novamente sem duplicar alterações;
- usar banco exclusivo de testes em automação contínua.

## Regressão e entrega

- executar a suíte em clone limpo;
- verificar rotas e menus principais;
- confirmar que Notícias, Entrevistas e demais módulos continuam funcionando;
- executar build dos recursos visuais;
- aplicar migrações em homologação antes de produção;
- remover conta, publicações e PDFs temporários;
- excluir segredos e `.env` do commit;
- repetir os fluxos principais após unir a branch ao código mais recente.
