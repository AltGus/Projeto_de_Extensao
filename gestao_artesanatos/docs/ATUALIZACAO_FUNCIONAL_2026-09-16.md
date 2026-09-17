# Atualização funcional — alunos, produções, entregas, oficinas, estoque e relatórios

## Objetivo

Esta atualização evolui o sistema existente sem reconstruí-lo. O acesso em computador e celular pela rede local permanece baseado na mesma porta e no mesmo `compose.yaml`. A principal correção de modelagem é separar a criança/aluno atendido das contas que autenticam no sistema.

## Requisitos funcionais implementados

1. Cadastro administrativo de alunos/crianças com ID exclusivo, nome, responsáveis, telefones opcionais, observações e pesquisa por nome.
2. Página individual do aluno com oficinas, quantidade total de trabalhos, histórico completo, filtro por oficina e situação de entregas.
3. Vínculo aluno–oficina por tabela associativa `student_workshop`.
4. Produção associada por chave estrangeira ao aluno e à oficina. O número do trabalho é calculado automaticamente em sequência por aluno.
5. Detalhes de produto exibem as produções relacionadas e o nome clicável do aluno.
6. Controle de ciclos de quatro trabalhos e registro de entrega em `deliveries`.
7. Uma produção só pode ser entregue ao mesmo aluno que a produziu e somente se estiver `disponivel`.
8. Oficina com orientador responsável relacionado a usuário `professor`.
9. Oficina com imagem JPEG/JPG padronizada em 16:9 e fallback quando não há imagem.
10. Materiais com `quantity_mode` inteiro ou decimal; entradas e saídas validam a regra no servidor.
11. Relatórios mensal, semestral, anual e personalizado, com indicadores de produção, alunos, entregas, estoque e baixo estoque.

## Regras de negócio

- Criança/aluno atendido não é conta de login.
- Nomes iguais são permitidos; relacionamentos usam `students.id`.
- Uma produção nova exige um aluno ativo já vinculado à oficina selecionada.
- `work_number` é sequencial por aluno e não é digitado manualmente.
- A cada quatro produções registradas nasce um ciclo de entrega.
- O histórico nunca reinicia após uma entrega.
- Uma entrega ocupa um único ciclo e uma produção não pode ser entregue duas vezes.
- Produção indisponível não pode ser escolhida para entrega.
- Produção de outro aluno não pode ser entregue para a criança atual.
- Material inteiro rejeita fração tanto no cadastro inicial quanto em movimentações.
- Produções históricas anteriores à atualização não recebem aluno inventado; permanecem com vínculo nulo.

## Modelo de dados

### `students`
Registro administrativo da criança, sem autenticação.

### `student_workshop`
Relacionamento N:N entre alunos e oficinas.

### `productions`
Novos campos: `student_id`, `work_number`, `availability_status`.

### `deliveries`
Registra `student_id`, `production_id`, `cycle_number`, `delivered_at`, usuário que registrou e observações. Há unicidade por produção e por ciclo do aluno.

### `workshops`
Novos campos: `responsible_user_id` e `image_path`.

### `materials`
Novo campo: `quantity_mode` (`integer` ou `decimal`).

## Arquitetura e compatibilidade

A camada em uso continua procedural/PDO. A arquitetura Laravel antiga foi posteriormente removida da entrega final após verificação de que não participava do fluxo funcional. A atualização de banco continua sendo realizada por `bin/migrate.php`, que pode ser executado novamente de forma preservadora.

## Manual do usuário — fluxos principais

### Cadastrar criança
Acesse **Alunos → Novo Aluno**, informe nome, responsável, contatos opcionais e oficinas.

### Registrar produção
Acesse **Produções → Nova Produção**, selecione a criança, produto, oficina, disponibilidade, quantidade, data e descrição. O número do trabalho é criado automaticamente.

### Entregar produção
Abra a página da criança. Quando houver um ciclo de quatro trabalhos pendente, aparece **ENTREGAR PRODUÇÃO**. Selecione uma produção disponível daquela própria criança e use **Marcar como entregue**.

### Definir responsável da oficina
Ao criar ou editar uma oficina, selecione **Orientador responsável** entre usuários professores ativos.

### Configurar material
No material, selecione unidade e se aceita quantidade inteira ou decimal. O estoque respeita essa configuração.

### Gerar relatório
Em **Relatórios**, escolha mensal, semestral, anual ou personalizado. Os demais filtros podem refinar por oficina, produto, aluno e responsável.

## Manual técnico — atualização

1. Fazer backup do banco.
2. Atualizar os arquivos do projeto.
3. Reconstruir a imagem Docker para obter GD e os novos arquivos.
4. Executar `docker compose exec app php bin/migrate.php`.
5. Não executar `install.php` em banco existente.
6. Validar os fluxos descritos abaixo.

## Testes previstos/automatizados

A suíte `tests/local/integration.php` cobre com dados fictícios:

- duas crianças chamadas Ana Silva com IDs diferentes;
- pesquisa de aluno;
- vínculo com oficina;
- trabalhos 1–4 e surgimento do primeiro ciclo;
- entrega válida;
- trabalhos 5–8 e novo ciclo;
- tentativa de entregar produção de outro aluno;
- material inteiro rejeitando 1,5;
- material decimal aceitando fração;
- relatório mensal, semestral e anual.

Além disso, devem ser feitos testes manuais no ambiente final para upload JPEG, layout móvel e acesso por IP LAN, pois dependem do navegador, Docker e rede reais da ONG.

## Implantação

Nenhuma porta ou host foi alterado. A aplicação continua publicada pela porta configurada em `APP_PORT` e pelo endereço configurado em `BIND_ADDRESS`. Foi adicionado um volume `workshop_images` para persistência das imagens de oficinas.

## Limitações conhecidas

- Produções históricas antigas não podem ser associadas automaticamente a uma criança sem fonte confiável; devem permanecer sem aluno ou ser corrigidas manualmente após conferência documental.
- Os relatórios de materiais e movimentações de estoque agora respeitam o mesmo intervalo de datas selecionado no relatório, sem alterar o saldo atual.
- A exclusão de imagem antiga substituída não é automática, priorizando evitar perda do arquivo anterior se uma transação de banco falhar. Arquivos órfãos podem ser limpos administrativamente depois de backup.
- O ambiente de geração desta entrega não possuía Docker, então o build do container e o teste real de MariaDB/LAN devem ser executados na máquina de implantação antes da entrega final.


## Refinamentos da rodada final

- arquitetura antiga e cópias duplicadas comprovadamente não utilizadas foram removidas;
- README e portfólio digital foram reorganizados para a entrega final;
- `Entregas pendentes atualmente` passou a ser exibido em seção de situação atual, separado dos indicadores do período;
- materiais e movimentações de estoque passaram a respeitar o intervalo do relatório;
- foram adicionados resumos de produção por oficina, produção por aluno e entregas no período;
- `production_create()` e `delivery_mark()` passaram a garantir transação própria quando chamadas fora de uma transação já existente;
- testes de integridade foram ampliados e foi incluído teste de concorrência para numeração de produções.

## Melhorias mantidas fora do escopo desta entrega

- Estruturar responsáveis em entidade própria, permitindo múltiplos responsáveis, parentesco e telefones individuais.
- Revisar a nomenclatura dos papéis autenticados e remover/migrar o papel legado `aluno` quando não houver dependências.


## Refinamento de entrega e histórico

A existência de um registro em `deliveries` determina a situação efetiva **Entregue** apresentada ao usuário. Isso evita exibir uma peça entregue como "Disponível" sem alterar desnecessariamente o valor histórico de `availability_status`. Após uma entrega, a produção fica bloqueada para edição e exclusão, preservando produto, oficina, data, quantidade e demais dados que compõem o histórico administrativo.

O Painel Administrativo também possui acesso direto à área de **Alunos atendidos**, reforçando que `students` é independente das contas autenticadas em `users`.
