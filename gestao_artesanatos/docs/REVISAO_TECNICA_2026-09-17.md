# Revisão técnica — 17/09/2026

Base: ZIP `Projeto_de_Extensao-entrega-final-refinada(2).zip`. A árvore ativa foi mantida em PHP/PDO; os arquivos antigos de Laravel que ainda estavam no GitHub foram retirados da versão atual, preservados no histórico Git. Não foram incluídos banco, credenciais, fotos reais ou dados dos alunos.

## Problemas corrigidos

- Exclusão de trabalho que sustentava ciclo já entregue: agora bloqueada.
- Concorrência entre criação, edição, exclusão e entrega: bloqueios na ordem aluno → produção, com leituras atuais dentro da transação. Duas entregas simultâneas não consomem o mesmo ciclo.
- Numeração `MAX + 1` reutilizava o último número excluído: contador persistente por aluno, inicializado pela migração. Lacunas após exclusões são intencionais.
- Precisão de material era validada pelo saldo enviado pelo formulário: agora usa saldo bloqueado no banco e histórico de movimentações. Não permite reinterpretar unidade de saldo existente.
- Valor padrão de precisão acessava campo ausente: corrigido.
- Cadastro direto de aluno podia ficar parcial se o vínculo com oficina falhasse: operação atômica, com validação de oficinas e telefones opcionais.
- Produções legadas sem aluno não podiam receber identificação pela edição: agora podem, com numeração atribuída nesse momento. Autoria não é inventada na migração.
- Acesso de contas legadas de aluno a páginas que enumeravam crianças: bloqueado, inclusive formulários de produção, produtos e oficinas.
- Consulta de cadastro arquivado pelo histórico retornava erro: páginas de consulta preservadas; alterações continuam bloqueadas. Filtro inclui oficinas com trabalhos históricos.
- Miniaturas no servidor PHP local não eram servidas: rota de JPEG adicionada. Upload exige JPEG real, GD, até 5 MB e 12 megapixels; gera 640 × 360. Falha ao salvar é tratada.
- Limite PHP do container alinhado ao limite de upload anunciado.
- Migração original preservava chaves `ON DELETE CASCADE` no histórico: alteradas para `RESTRICT`.
- Data/hora de entrega usa o mesmo fuso da aplicação.
- Totais do relatório somavam unidades incompatíveis (metros, quilos e unidades): cartões contam movimentações; quantidades continuam detalhadas por material/unidade.
- Testes recuperavam IDs incorretamente depois de commit ou inserção na tabela de vínculos; faltava usuário de teste para permissões. Corrigidos e ampliados. Exceções CLI encerram com código de erro.

## Organização

`public/` é a única raiz pública. `config/`, `src/`, `database/`, `bin/`, `resources/views/`, `tests/local/` e `docs/` têm funções distintas. Não há necessidade de Node, Composer ou Laravel para executar esta versão. `src/lib.php` ainda concentra regras e persistência; dividi-lo por módulos é uma melhoria futura, não uma condição para instalar esta atualização.

## Regra de negócio preservada do ZIP

Um **registro de produção** corresponde a um trabalho para o ciclo de quatro, independentemente do campo quantidade. A contagem é global por aluno, incluindo suas oficinas; o filtro de oficina muda somente a consulta. A cada quatro registros existentes, uma produção disponível do próprio aluno pode ser entregue. A entrega é registrada uma vez e bloqueia edição/exclusão da produção entregue. A quantidade de peças é um indicador separado. A migração não associa automaticamente contas legadas a crianças.

## Verificações

Executados com PHP 8.3, PDO MySQL, GD e MariaDB 10.11: sintaxe PHP; testes unitários; integração de alunos/produções/entregas/estoque/relatórios; processos concorrentes para estoque, sequência e entrega; regressões de exclusão, legado, validação e precisão; migração repetida no banco atual e no esquema original; HTTP com login, permissões, CSRF, páginas, CSV, upload real, dimensões do JPEG e rejeição de arquivo falso.

O GitHub Actions também valida MariaDB 11.4, build Docker e HTTP em Apache. Consulte o resultado associado ao commit publicado. A validação visual no computador/celular da ONG e uma restauração com os backups reais continuam dependendo desse ambiente.

## Atualizar a instalação utilizada pela ONG

1. Interrompa temporariamente os cadastros. Na pasta atual `gestao_artesanatos`, execute `powershell -File bin/backup.ps1` (Windows) ou `sh bin/backup.sh` (Linux/macOS).
2. Copie também as imagens: `docker compose cp app:/var/www/app/public/uploads/workshops backups/workshops`. Guarde SQL e imagens fora do servidor. Em instalação nativa, copie `public/uploads/workshops`.
3. Atualize os arquivos **na mesma pasta/projeto Compose**, preservando `.env`, `config/local.php`, backups e imagens. Não mude o nome do projeto Compose: isso pode selecionar volumes vazios. Não use `docker compose down -v`.
4. Execute `docker compose up -d --build` e `docker compose exec app php bin/migrate.php`. Em instalação nativa, use `php bin/migrate.php` com a configuração existente.
5. Confira login, alunos já cadastrados, imagens e uma consulta de histórico antes de retomar os cadastros. Nunca execute `install.php` para atualizar banco existente.

Servidor PHP nativo: `php -d upload_max_filesize=5M -d post_max_size=7M -S 0.0.0.0:8080 -t public public/router.php`. Mantenha PDO MySQL e GD habilitados. Esse servidor serve para teste local; Docker/Apache é o caminho documentado para uso contínuo.

Em caso de falha de migração, mantenha o sistema sem novas gravações e verifique o erro no terminal. DDL do MySQL não tem rollback completo: restaure o backup em ambiente separado antes de tentar uma reversão. Não apague os volumes como forma de resolver erro.
