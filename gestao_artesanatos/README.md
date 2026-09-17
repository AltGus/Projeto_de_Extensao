# Sistema de Gestão Artesanal

Sistema administrativo desenvolvido como Projeto de Extensão para apoiar o controle de oficinas, alunos atendidos, produção artesanal, materiais, estoque e entregas da entidade beneficiada.

A aplicação foi preparada para execução em computador servidor local e acesso por outros dispositivos da mesma rede, inclusive celular e tablet.

## Entidade beneficiada

**PROJEÇÃO – Projeto Jovens em Ação**  
**ABSER – Associação Beneficente das Senhoras de Entre Rios**

## Objetivo

Digitalizar e organizar rotinas que anteriormente dependiam de registros manuais, permitindo localizar informações com mais rapidez, preservar histórico, acompanhar produções por criança, controlar estoque e gerar relatórios administrativos por período.

Consulte a [revisão técnica e atualização segura](docs/REVISAO_TECNICA_2026-09-17.md) para correções, regra de contagem e resultados de testes.

## Status do projeto

Versão funcional preparada para entrega final acadêmica em 2026. Os fluxos principais estão implementados e a arquitetura ativa foi consolidada em PHP/PDO, com MariaDB/MySQL como banco de dados.

A tentativa anterior de estruturar o sistema como uma aplicação Laravel foi abandonada durante a evolução do projeto. Os diretórios e dependências dessa versão antiga foram removidos da entrega final porque não participavam do fluxo funcional atual.

## Principais funcionalidades

- autenticação de usuários administrativos;
- sessão, hash de senha, CSRF e consultas preparadas;
- cadastro, pesquisa e edição de alunos/crianças sem criação de login;
- página individual do aluno com oficinas, histórico e total de trabalhos;
- vínculo N:N entre alunos e oficinas;
- cadastro de oficinas com orientador responsável;
- imagem JPEG/JPG para oficinas, com miniatura padronizada;
- cadastro de produtos artesanais;
- registro de produções com aluno, oficina, produto, data, quantidade e descrição;
- numeração sequencial automática dos trabalhos por aluno;
- status de produção disponível/indisponível;
- regra de entrega a cada quatro trabalhos;
- registro permanente de entregas;
- controle de materiais com quantidades inteiras ou decimais;
- entradas e saídas de estoque;
- relatórios mensal, semestral, anual e personalizado;
- filtros por oficina, produto, aluno e responsável;
- resumos gerenciais por oficina e por aluno;
- relatório de entregas realizadas no período;
- movimentações de materiais limitadas ao período do relatório;
- exportação CSV das produções filtradas;
- impressão/salvamento do relatório em PDF pelo navegador;
- backup do banco por scripts Windows e Linux/macOS.

## Arquitetura atual

A arquitetura funcional é intencionalmente simples e adequada ao porte do projeto:

```text
Navegador
    ↓
public/index.php
    ↓
src/bootstrap.php
    ↓
src/lib.php
    ↓
resources/views/pages
    ↓
PDO
    ↓
MariaDB / MySQL
```

### Responsabilidades principais

**`public/index.php`**
- front controller da aplicação;
- interpretação da rota e do método HTTP;
- validação CSRF de requisições POST;
- aplicação de permissões;
- preparação dos dados enviados às telas.

**`src/bootstrap.php`**
- inicialização da aplicação;
- tratamento global de exceções;
- carregamento de configuração;
- sessão e cabeçalhos básicos de segurança.

**`src/lib.php`**
- autenticação;
- acesso a dados via PDO;
- regras de negócio;
- alunos, oficinas, produtos, produções e entregas;
- materiais e estoque;
- relatórios;
- funções auxiliares.

**`resources/views/pages/`**
- telas e formulários renderizados pelo servidor.

**`resources/views/layouts/`**
- estrutura visual compartilhada das áreas autenticada e pública.

**`public/assets/`**
- CSS e JavaScript utilizados pela interface.

**`database/schema.sql`**
- esquema para instalação nova.

**`bin/migrate.php`**
- atualização preservadora de instalações já existentes.

## Estrutura de pastas

```text
gestao_artesanatos/
├── bin/                 scripts de instalação, migração, backup e criação de admin
├── config/              configuração da aplicação e do banco
├── database/            esquema SQL atual
├── deploy/              configuração Apache/PHP do container
├── docs/                documentação e estrutura do portfólio digital
├── public/              front controller, assets e arquivos públicos
├── resources/views/     layouts e páginas PHP da interface
├── src/                 bootstrap e regras de negócio
├── tests/local/         testes unitários, integração, concorrência e smoke HTTP
├── compose.yaml
├── Dockerfile
└── README.md
```

## Tecnologias utilizadas

- PHP 8.3;
- Apache HTTP Server;
- PDO com driver MySQL;
- MariaDB 11.4 no ambiente Docker;
- HTML, CSS e JavaScript sem framework obrigatório no navegador;
- Docker e Docker Compose para implantação local reproduzível;
- PowerShell/Shell para scripts de backup.

Laravel **não é a arquitetura da versão final**. Ele fez parte de uma etapa anterior do projeto e foi removido da árvore ativa após verificação de dependências.

## Requisitos

### Opção recomendada

- Docker Desktop ou Docker Engine;
- Docker Compose;
- navegador moderno;
- porta local configurável, padrão `8080`.

### Execução sem Docker

É necessário possuir:

- PHP compatível com PDO MySQL;
- extensão GD para redimensionamento de imagens;
- servidor MariaDB/MySQL acessível;
- Apache ou servidor embutido do PHP.

Para configuração nativa, copie `config/local.example.php` para `config/local.php` e ajuste somente os dados locais. `config/local.php` não deve ser versionado.

## Instalação nova com Docker

Na pasta `gestao_artesanatos`:

```powershell
copy .env.example .env
```

No Linux/macOS:

```sh
cp .env.example .env
```

Preencha no `.env` senhas longas e diferentes:

```env
DB_PASSWORD=troque-por-uma-senha-forte
DB_ROOT_PASSWORD=troque-por-outra-senha-forte
BIND_ADDRESS=127.0.0.1
APP_PORT=8080
SESSION_SECURE_COOKIE=0
```

Suba os serviços:

```sh
docker compose up -d --build
```

Instale o esquema somente em banco novo:

```sh
docker compose exec app php bin/install.php
```

Crie o primeiro usuário administrativo:

```sh
docker compose exec app php bin/create-admin.php
```

Depois acesse:

```text
http://localhost:8080
```

Não existe senha padrão incluída no repositório.

## Execução em rede local / LAN

Para permitir acesso por dispositivos da mesma rede, configure no `.env`:

```env
BIND_ADDRESS=0.0.0.0
APP_PORT=8080
```

ou use especificamente o IPv4 privado do computador servidor.

Reaplique a configuração:

```sh
docker compose up -d
```

No celular ou outro computador conectado à mesma rede, acesse:

```text
http://IP-DO-SERVIDOR:8080
```

No Windows, o IPv4 pode ser consultado com:

```powershell
ipconfig
```

A porta deve ser liberada apenas para a rede privada. O banco de dados não deve ser exposto diretamente à LAN ou à internet.

## Banco de dados

O banco principal é `gestao_artesanal`.

Principais entidades:

- `users`: contas autenticadas;
- `students`: alunos/crianças atendidos pela entidade;
- `workshops`: oficinas;
- `student_workshop`: relação entre aluno e oficina;
- `products`: produtos artesanais;
- `productions`: trabalhos produzidos;
- `deliveries`: entregas vinculadas aos ciclos de quatro trabalhos;
- `materials`: materiais e configuração de unidade;
- `material_workshop`: vínculo de materiais com oficinas;
- `stock_movements`: entradas e saídas de estoque;
- `activity_logs`: registros administrativos.

### Diferença entre `users` e `students`

O perfil `aluno` em `users` é uma estrutura **legada de contas autenticadas do sistema**. Ele foi preservado para não provocar uma migração de autenticação arriscada antes da entrega final.

Os alunos/crianças realmente atendidos pela entidade são armazenados em `students`. Esses registros são independentes de `users` e **não necessitam de e-mail, senha ou credenciais de acesso**.

Novos cadastros de crianças devem ser feitos exclusivamente pela área **Alunos**.

## Regra de quatro trabalhos e entregas

Cada registro de produção conta como um trabalho, independentemente da quantidade de peças. O ciclo é global por aluno, incluindo todas as oficinas. Cada nova produção vinculada recebe um `work_number` crescente, que não é reutilizado após exclusões.

A cada quatro trabalhos registrados, o aluno conquista um ciclo de entrega:

- 4 trabalhos → ciclo 1;
- 8 trabalhos → ciclo 2;
- 12 trabalhos → ciclo 3;
- e assim por diante.

Uma entrega só pode utilizar produção:

- pertencente ao próprio aluno;
- marcada como `disponivel`;
- ainda não entregue.

O banco possui restrições únicas para impedir duas entregas da mesma produção ou duas entregas para o mesmo ciclo.

## Relatórios

Os relatórios aceitam:

- mensal;
- semestral;
- anual;
- intervalo personalizado.

Também podem ser filtrados por:

- oficina;
- produto;
- aluno;
- responsável.

### Indicadores do período

São calculados conforme o intervalo selecionado:

- registros de produção;
- quantidade produzida;
- alunos atendidos;
- entregas realizadas;
- movimentações de estoque;
- entradas;
- saídas.

### Situação atual

Os seguintes indicadores são apresentados separadamente porque representam o estado atual, e não um recorte histórico:

- entregas pendentes atualmente;
- materiais com estoque baixo atualmente.

### Resumos gerenciais

O relatório também apresenta:

- produção por oficina;
- produção por aluno;
- entregas realizadas dentro do período;
- materiais movimentados dentro do período;
- detalhamento das movimentações de estoque dentro do período;
- produções detalhadas.

O saldo atual do estoque não é recalculado ou alterado ao gerar relatórios históricos.

## Migração de uma instalação existente

**Não execute `bin/install.php` em banco já utilizado.**

Antes de atualizar:

1. interrompa temporariamente o uso;
2. faça backup;
3. substitua os arquivos pelo conteúdo desta versão;
4. reconstrua o container;
5. execute a migração.

Backup no Windows:

```powershell
powershell -File bin/backup.ps1
```

Linux/macOS:

```sh
sh bin/backup.sh
```

Atualização:

```sh
docker compose up -d --build
docker compose exec app php bin/migrate.php
```

`bin/migrate.php` é idempotente e foi escrito para preservar registros existentes.

## Backup

O backup do banco deve ser guardado fora do computador servidor sempre que possível.

Além do banco, preserve também as imagens das oficinas armazenadas no volume Docker `workshop_images` ou na pasta equivalente em uma instalação nativa.

Evite:

```sh
docker compose down -v
```

O parâmetro `-v` remove volumes e pode apagar banco e imagens persistentes.

## Segurança

A versão atual utiliza:

- `password_hash()` e `password_verify()`;
- sessão com modo estrito;
- cookie `HttpOnly` e `SameSite=Lax`;
- CSRF em ações POST;
- PDO com consultas preparadas e emulação desabilitada;
- restrições de acesso para ações administrativas;
- chaves estrangeiras e índices únicos para integridade;
- arquivamento lógico de cadastros principais quando necessário preservar histórico.

Dados de responsáveis e telefones são administrativos e não possuem páginas públicas.

Para exposição fora da rede local, seria necessário adicionar HTTPS, proxy reverso e revisão específica de segurança. O cenário de implantação desta entrega é LAN.

## Testes

### Teste unitário sem banco

```sh
php tests/local/unit.php
```

### Integração

Execute somente em banco descartável cujo nome comece por `test_`:

```sh
DB_DATABASE=test_gestao php tests/local/integration.php
```

O teste cobre, entre outros pontos:

- nomes de alunos duplicados com IDs diferentes;
- vínculo aluno/oficina;
- sequência dos números dos trabalhos;
- transação própria de `production_create()`;
- ciclos de quatro trabalhos;
- produção indisponível;
- tentativa de entrega cruzada;
- bloqueio de entrega duplicada;
- proteção contra edição de produção já entregue;
- material inteiro e decimal;
- relatórios mensal, semestral e anual;
- agrupamento por oficina e aluno;
- entregas no período;
- materiais respeitando o período.

### Concorrência

```sh
DB_DATABASE=test_gestao php tests/local/concurrency.php
```

Esse teste depende de banco MariaDB/MySQL e de suporte a `proc_open`. Ele verifica:

- duas saídas concorrentes disputando estoque;
- duas criações simultâneas de produção para o mesmo aluno recebendo números sequenciais distintos.

### Smoke HTTP

O script `tests/local/http_smoke.py` valida páginas principais, login, logout, CSRF, CSV e permissões quando uma instância de teste estiver em execução.

### Integração contínua

O workflow `.github/workflows/local-app.yml` executa no GitHub Actions uma validação com MariaDB 11.4 e `pdo_mysql`, incluindo lint PHP, testes unitários, integração, concorrência, migração idempotente, smoke HTTP, build Docker e smoke HTTP no container Apache.

## Portfólio digital

A pasta `docs/` está organizada para receber os documentos acadêmicos finais, apresentação, sprints e evidências. Arquivos não fornecidos não foram inventados. Consulte `docs/README.md`.

## Limitações conhecidas

- o papel `aluno` em `users` ainda existe por compatibilidade com a autenticação legada;
- produções antigas sem identificação confiável da criança permanecem com `student_id` nulo;
- produções entregues permanecem com seus dados originais no banco, mas a interface passa a exibir **Entregue** como situação efetiva e bloqueia sua edição/exclusão para preservar o histórico;
- a estrutura de responsáveis ainda utiliza `students.guardian_name` e telefones simples;
- movimentações de estoque não registram diretamente a oficina de origem/destino, portanto filtros de oficina/produto/aluno/responsável não são aplicados a essas movimentações;
- upload e responsividade final devem ser validados no computador e celular usados na apresentação;
- os testes automatizados de concorrência foram executados com MariaDB; valide também o uso nos dispositivos da ONG.

## Próximas melhorias

Após a apresentação, podem ser consideradas:

- estruturar responsáveis em entidade própria, permitindo múltiplos responsáveis, parentesco e telefones individuais;
- revisar a nomenclatura dos papéis autenticados para algo como administrador/orientador;
- remover ou migrar o papel legado `aluno` de `users` quando não houver mais dependências;
- registrar oficina ou contexto específico em cada movimentação de estoque, caso a ONG necessite relatório de consumo por oficina;
- adicionar rotina administrativa para limpeza controlada de imagens de oficina órfãs;
- validar e manter o pipeline de integração contínua após a publicação da versão final no GitHub;
- adicionar HTTPS caso o sistema seja futuramente publicado fora da LAN.

## Equipe

- **Gustavo Kraus Machado** — desenvolvimento, banco de dados, implantação e documentação.
- **Robson Keller** — requisitos, testes, documentação e apoio ao desenvolvimento.

Curso de Engenharia de Software  
Centro Universitário Campo Real  
Guarapuava/PR — 2026
