# Arquitetura final do Sistema de Gestão Artesanal

## Fluxo real da aplicação

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

## Componentes

### `public/index.php`

É o front controller. Recebe as requisições HTTP, identifica a rota, valida CSRF em POST, aplica autenticação/permissões e chama as regras de negócio necessárias.

### `src/bootstrap.php`

Inicializa a aplicação. Configura tratamento de erros, carrega configuração local quando existente, define timezone, inicia sessão e aplica cabeçalhos básicos de segurança.

### `src/lib.php`

Concentra a camada funcional desta aplicação de pequeno porte:

- conexão PDO;
- autenticação;
- regras de permissão;
- consultas e persistência;
- oficinas;
- alunos;
- produtos;
- produções;
- entregas;
- materiais;
- estoque;
- relatórios;
- validações e funções auxiliares.

### `resources/views/pages/`

Contém as telas PHP renderizadas pelo servidor.

### `resources/views/layouts/`

Contém os layouts compartilhados da área autenticada e das páginas de autenticação.

### `public/assets/`

Contém o CSS e JavaScript efetivamente utilizados pela interface.

### Banco de dados

MariaDB/MySQL é acessado por PDO com prepared statements. A instalação nova usa `database/schema.sql` e instalações existentes são evoluídas por `bin/migrate.php`.

## Arquitetura que não faz parte da versão final

O projeto passou anteriormente por uma tentativa de implementação com Laravel e por cópias duplicadas da aplicação. Esses arquivos não eram carregados por `public/index.php`, não eram copiados pelo `Dockerfile` e não participavam das telas ou regras atuais. Eles foram removidos na rodada final após verificação de referências.

Laravel, Artisan, Blade, Composer/Vendor e Node/Vite não são dependências da aplicação funcional entregue.

## Autenticação e alunos atendidos

`users` representa contas autenticadas. O papel legado `aluno` foi mantido por compatibilidade de autenticação.

`students` representa as crianças/alunos atendidos pela entidade. Esses registros administrativos não possuem login, e-mail ou senha obrigatórios.

A separação é deliberada e evita tratar a criança atendida como usuário do sistema.

## Transações críticas

`production_create()` inicia sua própria transação quando o chamador ainda não estiver em uma. O bloqueio `SELECT ... FOR UPDATE` do aluno permanece ativo até o insert e commit, garantindo numeração sequencial consistente mesmo em chamada direta.

`delivery_mark()` segue a mesma estratégia: quando necessário, abre transação, bloqueia o aluno, recalcula o ciclo pendente, bloqueia a produção e somente então grava a entrega. Se a função já estiver dentro da transação do fluxo HTTP, ela reutiliza essa transação sem tentar aninhar commits incompatíveis.

O banco reforça essas regras com índices únicos para `(student_id, work_number)`, `production_id` em entregas e `(student_id, cycle_number)`.


## Integridade histórica de produções entregues

Produções entregues são tratadas como registros históricos protegidos. A existência de uma linha em `deliveries` passa a determinar a situação efetiva **Entregue** na interface, mesmo que `productions.availability_status` preserve o valor anterior. A edição e a exclusão dessas produções são bloqueadas na camada de regra de negócio e as ações deixam de ser oferecidas na interface.
