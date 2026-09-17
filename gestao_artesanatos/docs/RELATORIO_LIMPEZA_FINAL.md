# Relatório de limpeza, estabilização e preparação final

## Escopo

Esta rodada foi executada sobre a versão funcional anexada. O sistema não foi reconstruído. A limpeza foi feita somente depois de verificar o fluxo real `public/index.php → src/bootstrap.php → src/lib.php → resources/views/pages → PDO → MariaDB/MySQL`, as instruções `COPY` do `Dockerfile`, as rotas e as referências dos arquivos ativos.

## 1. Arquivos e pastas removidos

### Cópias grandes comprovadamente obsoletas

- `app/Controllers/Nova pasta/` — continha praticamente outro projeto completo, incluindo `vendor/` e `node_modules/`. Não havia referência no fluxo atual e o diretório não era copiado para o container funcional.
- `public/public/` — cópia parcial antiga de `public/`, views e assets. Nenhuma rota ativa apontava para essa árvore.

### Estrutura Laravel antiga

Foram removidos após confirmação de ausência de dependências no runtime atual:

- `app/`
- `Auth/`
- `Models/`
- `Providers/`
- `View/`
- `bootstrap/`
- `routes/`
- `storage/`
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `phpunit.xml`
- `postcss.config.js`
- `tailwind.config.js`
- `vite.config.js`
- configurações Laravel antigas em `config/`;
- migrations/factories/seeders Laravel antigos;
- testes `Feature`/`Unit` dependentes de Laravel/Pest;
- arquivos Blade e assets `resources/css` / `resources/js` da tentativa antiga.

### Views e arquivos duplicados não utilizados

Também foram removidos arquivos PHP que não eram chamados por nenhuma rota atual, incluindo:

- `resources/views/pages/auth/`
- `resources/views/pages/dashboard/index.php`
- `resources/views/pages/reports/index.php`
- `resources/views/pages/products/fields.php` — arquivo antigo que continha múltiplas views concatenadas e não era incluído pelas telas atuais;
- layouts Blade/guest não utilizados;
- componentes/templates antigos não referenciados;
- `database/reset.sql` e `database/seed.sql` antigos, que não faziam parte de `install.php` ou `migrate.php` e estavam conceitualmente desatualizados;
- arquivo público vazio criado acidentalmente com comandos de rewrite no nome.

## 2. Motivo das remoções

A confirmação utilizou quatro verificações principais:

1. busca por `include`, `require`, chamadas de view, referências de assets e caminhos antigos;
2. inspeção das rotas declaradas em `public/index.php`;
3. inspeção do `Dockerfile`, que define explicitamente quais diretórios entram na aplicação em produção;
4. verificação posterior de que todas as 29 views referenciadas por rotas continuam existentes.

O objetivo foi eliminar duplicações e dependências históricas sem alterar a arquitetura funcional.

## 3. Arquivos/estruturas mantidos por segurança

- `config/local.example.php`: continua útil para execução nativa sem Docker.
- `public/router.php`: permite smoke test e execução com servidor embutido do PHP.
- `public/favicon.ico` e `public/robots.txt`: arquivos públicos pequenos e seguros.
- `.github/workflows/local-app.yml`: pipeline atual e coerente com a aplicação final, com MariaDB, `pdo_mysql`, lint, integração, concorrência, smoke HTTP e build Docker.
- `workshop_user` no esquema/migração: relação legada mantida para evitar perda ou migração destrutiva de dados antigos.
- papel `aluno` em `users`: mantido por compatibilidade de autenticação; não representa as crianças atuais.
- produções históricas com `student_id` nulo: permanecem válidas quando não existe fonte confiável para identificar a criança.

## 4. Tamanho antes e depois

### Antes

- ZIP recebido: **54.859.847 bytes** (aproximadamente 52,3 MiB).
- Conteúdo não comprimido listado no ZIP: **124.274.747 bytes**, distribuídos em **18.272 arquivos**.
- No sistema de arquivos de trabalho, a extração ocupava aproximadamente **171 MB**, principalmente pelo grande número de arquivos pequenos de `vendor` e `node_modules`.
- Somente `app/Controllers/Nova pasta/` ocupava aproximadamente **169 MB em blocos de disco**, com cerca de 15 mil arquivos; dentro dela, `vendor/` e `node_modules/` respondiam pela quase totalidade desse volume.

### Depois

A árvore completa do repositório final ficou em aproximadamente **276 KB de conteúdo lógico** (cerca de **620 KB em blocos de disco** neste ambiente), sem dependências vendorizadas ou cópias duplicadas. O ZIP final ficou em aproximadamente **112 KB**, contra cerca de 53 MB do ZIP recebido.

## 5. README atualizado

O README principal foi refeito para representar a aplicação real. Ele agora contém:

- nome e objetivo do projeto;
- entidade beneficiada;
- funcionalidades;
- tecnologias efetivamente usadas;
- arquitetura final;
- estrutura de pastas;
- instalação nova;
- atualização de instalação existente;
- execução em LAN;
- banco de dados;
- backup;
- migração;
- segurança;
- relatórios;
- testes;
- equipe;
- status atual;
- limitações conhecidas;
- próximas melhorias.

Também documenta explicitamente a diferença entre `users` e `students`.

## 6. Portfólio digital

Foram criadas/preparadas:

- `docs/README.md`
- `docs/sprints/README.md`
- `docs/apresentacao/README.md`
- `docs/evidencias/README.md`
- `docs/evidencias/links-videos.md`

Nenhum PDF, DOCX, PPTX ou link acadêmico foi inventado. As pastas apenas indicam onde os arquivos oficiais devem ser colocados quando estiverem disponíveis.

## 7. Alterações nos relatórios

### Indicadores por período

Foi criada separação visual entre:

**Indicadores do período**
- registros de produção;
- quantidade produzida;
- alunos atendidos;
- entregas realizadas;
- movimentações de estoque;
- entradas;
- saídas.

**Situação atual**
- entregas pendentes atualmente;
- materiais com estoque baixo atualmente.

Isso deixa explícito que o número de entregas pendentes é um estado atual do sistema, não um valor histórico do mês/semestre/ano filtrado.

### Materiais

`report_material_consumption()` passou a receber os filtros do relatório e aplicar `movement_date` ao mesmo `start/end` do período.

Foi adicionada também `report_stock_movements()`, que apresenta o detalhamento das entradas e saídas do período.

O saldo atual do estoque não é alterado por essas consultas.

## 8. Novas consultas e agrupamentos gerenciais

Foram adicionadas:

- `report_production_by_workshop_filtered()` — registros e quantidade produzida por oficina;
- `report_production_by_student()` — trabalhos e quantidade produzida por aluno;
- `report_deliveries()` — entregas cuja data de entrega está no período;
- `report_stock_movements()` — movimentações de estoque no intervalo selecionado;
- `report_filter_parts()` — centraliza validação e montagem dos filtros compartilhados.

Os agrupamentos de produção e entrega reaproveitam os filtros de oficina, produto, aluno e responsável quando esses filtros são aplicáveis à entidade consultada.

Movimentações de estoque não possuem atualmente oficina/produto/aluno/responsável diretamente registrados; por isso, esses filtros não são inventados para estoque. Apenas o período é aplicado com precisão.

## 9. Robustez de `production_create()`

`production_create()` agora verifica `PDO::inTransaction()`.

- Se já estiver dentro da transação iniciada pelo fluxo HTTP, reutiliza essa transação.
- Se for chamada diretamente, inicia a própria transação.
- O `SELECT ... FOR UPDATE` utilizado para serializar a numeração permanece protegido até o `INSERT` e o `COMMIT`.
- Em erro, somente a função que abriu a transação executa o rollback.

Isso evita depender de `action_attempt()` para que a numeração sequencial seja segura.

## 10. Robustez de entregas

`delivery_mark()` recebeu o mesmo padrão de propriedade da transação.

A operação agora:

1. bloqueia o aluno com `FOR UPDATE`;
2. recalcula o ciclo pendente dentro da transação;
3. bloqueia a produção escolhida;
4. confirma propriedade e disponibilidade;
5. verifica entrega anterior;
6. insere a entrega;
7. faz commit apenas se a própria função abriu a transação.

Os índices únicos existentes continuam garantindo:

- uma entrega por produção;
- uma entrega por ciclo do aluno.

## 11. Testes realizados

### Executados com sucesso neste ambiente

- lint PHP de todos os **52 arquivos PHP** restantes: sem erro de sintaxe;
- `tests/local/unit.php`: passou;
- parse de `compose.yaml`: passou;
- conferência de todas as **29 views** chamadas pelas rotas: nenhuma ausente;
- conferência dos caminhos copiados pelo `Dockerfile`: todos existem;
- busca por referências de runtime para Laravel/Blade/`public/public`/`Nova pasta`: nenhuma encontrada;
- verificação de CSS e JS ativos: arquivos presentes;
- renderização isolada da nova view de relatórios com dados fictícios: passou;
- servidor embutido do PHP: `/login`, CSS e JavaScript responderam corretamente, incluindo token CSRF;
- compilação sintática de `tests/local/http_smoke.py`: passou.

### Testes ampliados no projeto

`tests/local/integration.php` agora cobre adicionalmente:

- chamadas diretas a `production_create()`;
- sequência 1–12;
- produção indisponível;
- entrega cruzada;
- segundo uso do mesmo ciclo;
- segunda entrega da mesma produção;
- agrupamento por oficina;
- agrupamento por aluno;
- filtros dos agrupamentos;
- entregas no período;
- materiais dentro e fora do período.

`tests/local/concurrency.php` agora testa duas criações simultâneas para o mesmo aluno, esperando números `1` e `2`, além do teste concorrente de estoque.

`tests/local/http_smoke.py` foi atualizado para incluir alunos e os novos blocos do relatório.

## 12. Testes que não puderam ser executados aqui

Este ambiente de trabalho não possui:

- Docker;
- MariaDB/MySQL;
- extensão `pdo_mysql` no PHP disponível no host.

Por isso, os testes de integração e concorrência com banco são marcados explicitamente como **SKIP** quando `pdo_mysql` não está disponível. O build real do container, MariaDB, upload JPEG, navegação autenticada completa e acesso por celular/LAN devem ser executados no computador de implantação.

Essa limitação é do ambiente usado para preparar o ZIP, não uma remoção de dependência do projeto: o `Dockerfile` instala `pdo_mysql` e GD.

## 13. Melhorias mantidas para depois da apresentação

- estruturar responsáveis em entidade própria, permitindo múltiplos responsáveis, parentesco e telefones individuais;
- revisar nomenclatura de papéis para administrador/orientador;
- migrar/remover futuramente o papel legado `aluno` em `users` quando não houver dependências;
- registrar oficina/contexto em movimentações de estoque se a ONG passar a exigir consumo por oficina;
- rotina de limpeza de imagens órfãs após backup;
- validar e manter o pipeline de integração contínua com MariaDB após a publicação da versão final no GitHub;
- HTTPS e endurecimento adicional caso o sistema deixe de ser somente LAN.


## 14. Refinamentos finais de integridade

- Uma produção que possui registro em `deliveries` é exibida na interface como **Entregue**, independentemente do valor histórico de `availability_status`. O campo original não é reescrito apenas para fins visuais.
- Produções entregues passaram a ser registros históricos protegidos: a interface oculta ações de edição/exclusão e a camada de regra de negócio rejeita alterações posteriores.
- O Painel Administrativo passou a oferecer acesso explícito a **Alunos atendidos**, mantendo clara a separação entre crianças em `students` e contas autenticadas em `users`.
- O pipeline `.github/workflows/local-app.yml` já executa integração com MariaDB; a pendência futura é apenas validar e manter esse pipeline após a publicação final no GitHub.
