# 🚀 Guia de Instalação - WebEngine CMS

Este guia fornece instruções detalhadas para instalar o WebEngine CMS no seu servidor.

## 📋 Pré-requisitos

Antes de começar a instalação, certifique-se de ter:

### Requisitos do Sistema

- **PHP 8.1 ou superior**
- **Servidor MSSQL** (Microsoft SQL Server)
- **Composer** (gerenciador de dependências PHP)
- **Servidor Web**: Apache ou Nginx
- **Banco de dados MU Online** já existente

### Extensões PHP Necessárias

- `pdo_sqlsrv` (ou `pdo_dblib` / `pdo_odbc`)
- `curl`
- `openssl`
- `mbstring`
- `json`

## 🎯 Métodos de Instalação

O WebEngine CMS oferece dois métodos de instalação:

### Método 1: Instalador Web (Recomendado) 🌐

O instalador web é a forma mais fácil e rápida de instalar o sistema.

#### Passo 1: Preparar o Ambiente

```bash
# Clone o repositório
git clone <url-do-repositorio>
cd ggc-website

# Instale as dependências
composer install
```

#### Passo 2: Acessar o Instalador

1. Configure seu servidor web para apontar para o diretório `/public`
2. Acesse no navegador: `http://seusite.com/install`
3. Siga o assistente de instalação em 7 passos:

**Etapa 1 - Bem-vindo**
- Introdução e informações sobre o sistema

**Etapa 2 - Verificação de Requisitos**
- Verifica se o servidor atende todos os requisitos
- Mostra quais extensões estão faltando (se houver)

**Etapa 3 - Configuração do Banco de Dados**
- Configure a conexão com o MSSQL
- Teste a conexão antes de continuar
- Informações necessárias:
  - Driver (sqlsrv, dblib ou odbc)
  - Host (geralmente `localhost`)
  - Porta (geralmente `1433`)
  - Nome do banco (geralmente `MuOnline`)
  - Usuário e senha

**Etapa 4 - Configurações Gerais**
- Nome do servidor
- URL do site
- Configurações de email (opcional)

**Etapa 5 - Instalação do Banco**
- Cria todas as tabelas necessárias
- Adiciona colunas nas tabelas existentes
- Insere dados iniciais

**Etapa 6 - Finalização**
- Cria diretórios necessários
- Gera arquivo `.env`
- Configura permissões

**Etapa 7 - Conclusão**
- Instruções finais
- Link para acessar o site

#### Passo 3: Pós-Instalação

```bash
# IMPORTANTE: Remova o diretório de instalação
rm -rf install/

# Ou renomeie para segurança
mv install/ _install_backup/
```

---

### Método 2: Instalação Manual 🛠️

Para instalação manual ou automação via scripts.

#### Passo 1: Preparar Arquivos

```bash
# Clone e instale dependências
git clone <url-do-repositorio>
cd ggc-website
composer install
```

#### Passo 2: Configurar .env

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite com suas configurações
nano .env
```

Configure as seguintes variáveis no `.env`:

```env
# Banco de Dados
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=MuOnline
DB_USERNAME=sa
DB_PASSWORD=sua_senha

# Aplicação
APP_NAME="Nome do Seu Servidor"
APP_URL=http://seusite.com
APP_ENV=production
APP_DEBUG=false

# Email (opcional)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_senha
MAIL_FROM_ADDRESS=noreply@seusite.com

# JWT (gere uma chave aleatória)
JWT_SECRET=sua_chave_secreta_aleatoria_aqui

# PayPal (opcional)
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox
```

#### Passo 3: Criar Diretórios

```bash
# Criar diretórios necessários
mkdir -p storage/cache storage/logs storage/uploads storage/sessions

# Configurar permissões
chmod -R 775 storage
```

#### Passo 4: Instalar Banco de Dados

Conecte ao seu SQL Server e execute o script:

```bash
# Via SQL Server Management Studio
# Abra e execute: database/schema.sql

# Ou via linha de comando (sqlcmd)
sqlcmd -S localhost -U sa -P sua_senha -d MuOnline -i database/schema.sql
```

#### Passo 5: Configurar Servidor Web

**Apache (.htaccess já incluído)**

```apache
<VirtualHost *:80>
    ServerName seusite.com
    DocumentRoot /caminho/para/ggc-website/public

    <Directory /caminho/para/ggc-website/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/webengine-error.log
    CustomLog ${APACHE_LOG_DIR}/webengine-access.log combined
</VirtualHost>
```

**Nginx**

```nginx
server {
    listen 80;
    server_name seusite.com;
    root /caminho/para/ggc-website/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Passo 6: Reiniciar Servidor Web

```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart nginx
```

---

## 🔐 Configuração do Administrador

Após a instalação, você precisa criar um administrador:

1. **Registre uma conta** no site
2. **Acesse o banco de dados** e execute:

```sql
-- Definir como administrador (admin_level = 1)
UPDATE MEMB_INFO
SET admin_level = 1
WHERE memb___id = 'seu_usuario';
```

3. **Acesse o painel admin**: `http://seusite.com/admin`

### Níveis de Acesso

- `admin_level = 0` - Usuário comum
- `admin_level = 1` - Administrador completo
- `admin_level = 2` - Super administrador (futuro)

---

## 🎨 Personalização

### Alterar Template

Os templates estão em `/templates` usando Twig:

```
templates/
├── layout.twig          # Layout principal
├── home.twig           # Página inicial
├── auth/               # Login/Registro
├── admin/              # Painel admin
└── ...
```

### Modificar CSS/JS

Arquivos estáticos em `/public`:

```
public/
├── css/
├── js/
└── images/
```

---

## 🔧 Configurações Avançadas

### Cache

Configure no `.env`:

```env
CACHE_DRIVER=file
CACHE_TTL=3600
```

Limpar cache:

```bash
rm -rf storage/cache/*
```

### Email com Gmail

1. Ative "Verificação em 2 etapas" na conta Google
2. Gere uma "Senha de App" em: https://myaccount.google.com/apppasswords
3. Use a senha gerada no `.env`:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=senha_de_app_gerada
```

### PayPal

1. Crie uma conta de desenvolvedor: https://developer.paypal.com
2. Crie um app e obtenha as credenciais
3. Configure no `.env`:

```env
PAYPAL_CLIENT_ID=seu_client_id
PAYPAL_CLIENT_SECRET=seu_secret
PAYPAL_MODE=sandbox  # ou 'live' para produção
```

### Configurar Sites de Voto

Acesse o banco de dados e edite a tabela `GGC_VOTE_SITES`:

```sql
INSERT INTO GGC_VOTE_SITES (name, url, credits_reward, active, sort_order)
VALUES ('MeuSite', 'https://meusite.com/vote', 100, 1, 1);
```

---

## 🐛 Solução de Problemas

### Erro: "Cannot connect to database"

**Solução:**
1. Verifique se o SQL Server está rodando
2. Confirme as credenciais no `.env`
3. Verifique se a extensão PDO está instalada
4. Teste a conexão manualmente

### Erro: "Permission denied" em storage/

**Solução:**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Erro 500 ao acessar o site

**Solução:**
1. Verifique os logs em `storage/logs/`
2. Ative o modo debug no `.env`:
   ```env
   APP_DEBUG=true
   APP_ENV=development
   ```
3. Verifique se o `.htaccess` existe em `/public`

### Extensão pdo_sqlsrv não encontrada

**Windows:**
1. Baixe o driver: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
2. Copie para `php/ext/`
3. Adicione no `php.ini`: `extension=php_pdo_sqlsrv.dll`

**Linux:**
```bash
# Ubuntu/Debian
sudo apt-get install php8.1-sybase  # Para pdo_dblib

# Ou instale o driver Microsoft
curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql17 mssql-tools
sudo pecl install sqlsrv pdo_sqlsrv
```

---

## 📚 Recursos Adicionais

- **README.md** - Documentação geral do projeto
- **PLUGINS.md** - Sistema de plugins
- **database/schema.sql** - Estrutura do banco de dados

---

## 💬 Suporte

Se encontrar problemas:

1. Verifique os logs em `storage/logs/`
2. Consulte a documentação
3. Verifique se todos os requisitos foram atendidos

---

## 🎉 Pronto!

Após seguir este guia, seu WebEngine CMS estará instalado e pronto para uso.

**Não esqueça de remover o diretório `/install` após a instalação!**

```bash
rm -rf install/
```

Divirta-se gerenciando seu servidor MU Online! 🎮
