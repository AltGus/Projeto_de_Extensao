# Gestão Artesanal — servidor local / LAN

Aplicação PHP com PDO/MySQL para oficinas, produção artesanal e estoque da ONG.
O fluxo em uso é `public/index.php → src/bootstrap.php → src/lib.php`.
Os arquivos Laravel antigos permanecem no repositório para referência, mas **não são usados** pela instalação abaixo. Não execute `artisan migrate` para esta aplicação.

## Instalação nova com Docker (Windows, Linux ou macOS)

Instale Docker com Compose; no Windows, abra o Docker Desktop antes de começar.
Na pasta `gestao_artesanatos`:

1. Copie `.env.example` para `.env` (`copy .env.example .env` no CMD).
2. Preencha `DB_PASSWORD` e `DB_ROOT_PASSWORD` com duas senhas longas e diferentes. Prefira letras e números aleatórios para evitar interpretação de caracteres pelo Compose. Não publique `.env`.
3. Execute:

```sh
docker compose up -d --build
docker compose exec app php bin/install.php
docker compose exec app php bin/create-admin.php
```

O último comando pede nome, e-mail e senha para criar seu professor. Não existe senha padrão. Não importe `seed.sql` ou `reset.sql`.
Abra **http://localhost:8080**. Cadastre os demais usuários em Administração.
O cadastro público está desativado. A instalação não usa serviços externos durante o uso; internet é necessária para baixar e construir as imagens inicialmente.

## Acesso em rede local

No `.env`, altere `BIND_ADDRESS` para o IP privado do computador servidor (ex.: `192.168.1.20`) ou `0.0.0.0`, e execute novamente `docker compose up -d`.
Nos outros computadores/celulares da mesma rede, abra `http://IP-DO-SERVIDOR:8080`.
No Windows, descubra o IPv4 usando `ipconfig`. Reserve esse IP no roteador.
Permita TCP 8080 no firewall **somente para a rede privada/sub-rede da ONG**. Não configure encaminhamento de porta no roteador. O banco não publica a porta 3306.
O servidor e o Docker precisam permanecer ligados. Os clientes precisam apenas de navegador.

O modo HTTP é destinado à rede local confiável. Para hospedar pela internet ou usar rede compartilhada, configure HTTPS em um proxy reverso e `SESSION_SECURE_COOKIE=1` antes de liberar acesso. A configuração entregue não publica o sistema na internet.

## Instalação existente: preservar os dados

Faça um backup e teste a restauração em uma cópia antes da atualização. Pare o acesso dos usuários durante a migração.
Com o banco existente configurado e o código atualizado:

```sh
php bin/migrate.php
```

No Docker, use `docker compose exec app php bin/migrate.php`.
A migração adiciona campos/tabela de materiais, campos de arquivamento, decimais, restrições de histórico e unicidade de vínculos. Vínculos de oficina duplicados são consolidados. Ela pode ser repetida; DDL do MySQL não possui rollback integral. Em caso de falha, mantenha o sistema fora de uso, corrija a causa e repita, ou restaure o backup.
**Não execute install.php em um banco existente.** Ele recusa bancos não vazios.
Dados inconsistentes antigos, como estoque negativo, precisam de conferência com a ONG; não são inventados nem ajustados automaticamente.

Para migrar um banco do XAMPP para Docker, exporte somente a estrutura/dados do banco `gestao_artesanal` pelo phpMyAdmin; restaure no serviço `db` antes de executar `migrate.php`.

## Backup e restauração

Linux/macOS: `sh bin/backup.sh`.
Windows PowerShell: `powershell -File bin/backup.ps1`.
Guarde cópia dos arquivos de `backups` em outro dispositivo protegido; contêm dados pessoais. Faça backup diário e antes de atualizações. Teste periodicamente a restauração em um banco separado.

Para restaurar, pare a aplicação, copie o SQL para o contêiner e importe. **A restauração substitui tabelas existentes: confirme o arquivo e guarde o backup atual antes de executá-la.**

```sh
docker compose stop app
docker compose cp backups/SEU-ARQUIVO.sql db:/tmp/restore.sql
docker compose exec db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" < /tmp/restore.sql'
docker compose exec db rm /tmp/restore.sql
docker compose start app
```

Os comandos acima usam shell Linux/macOS ou PowerShell (não CMD). Não use `docker compose down -v`: isso remove o volume do banco. `docker compose down` preserva-o.

## Sem Docker: Apache/XAMPP

Requisitos: PHP 8.3, PDO MySQL e MariaDB 10.11+/MySQL 8. Configure o **DocumentRoot exclusivamente em `gestao_artesanatos/public`**, habilite mod_rewrite e AllowOverride para `.htaccess`. Nunca sirva a raiz do repositório. Bloqueie a pasta duplicada `public/public`; a regra fornecida faz isso.
Crie um banco UTF-8 `gestao_artesanal` e um usuário exclusivo com senha e permissões apenas nesse banco. Copie `config/local.example.php` para `config/local.php`, preencha e execute `php bin/install.php` e `php bin/create-admin.php`.
Para um teste em um único computador, após preparar o banco: `php -S 127.0.0.1:8000 -t public public/router.php`. Use Apache para uso cotidiano por vários usuários.

## Recursos e correções

- Materiais com oficinas e quantidades com até três casas decimais (ponto ou vírgula no servidor).
- Estoque transacional com bloqueio de linhas em inclusão/edição/exclusão; saldo não negativo. Alterar cadastro do material não altera saldo: use Estoque.
- Produção validada no servidor; aluno só registra em seu próprio nome; produto deve corresponder à oficina quando vinculado.
- Permissões recarregadas do banco; arquivar conta invalida o acesso na próxima requisição.
- Arquivamento de usuários, materiais, produtos e oficinas preserva registros históricos. Não há tela de restauração de arquivados nesta entrega.
- Logs e operações da interface na mesma transação: falhas não deixam log de sucesso.
- E-mail/perfil/senha validados; erros internos vão para logs do servidor.
- Relatórios de produção com período, oficina, produto e responsável; CSV compatível com Excel e impressão/salvar PDF pelo navegador. Não gera `.xlsx` nem PDF no servidor. O consumo de materiais continua sendo o total histórico, identificado na tela.

## Testes

`php tests/local/unit.php` não exige banco. Os testes de integração **criam tabelas e dados** e devem usar um banco vazio, descartável, cujo nome começa por `test_`:

```sh
DB_DATABASE=test_gestao php tests/local/integration.php
DB_DATABASE=test_gestao php tests/local/concurrency.php
```

A segunda suíte usa processos concorrentes em Linux. A integração contínua no GitHub configura MariaDB e roda testes, migração repetida e build Docker.

## Limites e próximos passos

Participantes ainda são contas `users` com perfil aluno. A separação em `participants`/`workshop_participant`, cadastro de crianças sem conta, controle de acesso por oficina e política de retenção de dados precisam de uma etapa própria de modelagem e migração. Não trate esta entrega como homologação final para dados reais antes de validar os fluxos com a ONG.
A instalação no computador da ONG, firewall, IP e rotina de backup precisam ser executados no local. Esta alteração prepara os arquivos; não instala remotamente no seu computador.

Referências de infraestrutura: [PHP/Apache oficial](https://hub.docker.com/_/php), [MariaDB oficial](https://hub.docker.com/_/mariadb).

## Validação desta entrega (16/09/2026)

Executados com PHP 8.3.6 e MariaDB 10.11.7 em banco descartável:
- Sintaxe dos arquivos PHP da aplicação e scripts.
- Testes unitários de decimais, datas e CSV.
- Integração de materiais/oficinas, estoque, produção, filtros, arquivamento e revogação de sessão.
- Duas saídas simultâneas disputando o mesmo saldo.
- Login HTTP, páginas principais, lista de oficinas no material, CSV, rejeição CSRF e bloqueio de administração para aluno.
- Migração repetida tanto no schema novo quanto no schema original do repositório.

O build Docker/Apache e a execução no computador da ONG não foram executados neste ambiente. O workflow incluído valida também o build Docker; consulte seu resultado no GitHub. Backups e restauração precisam ser ensaiados no ambiente de instalação antes de usar dados reais.
