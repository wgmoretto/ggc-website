<?php
/**
 * Classe Installer
 *
 * Gerencia todo o processo de instalação do WebEngine CMS
 */

class Installer
{
    private $lastError = '';
    private $rootPath;

    public function __construct()
    {
        // /public/install -> go up 2 levels to root
        $this->rootPath = dirname(dirname(__DIR__));
    }

    /**
     * Verifica os requisitos do sistema
     */
    public function checkRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP 8.1 ou superior',
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'value' => PHP_VERSION
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'value' => extension_loaded('pdo') ? 'Instalado' : 'Não instalado'
            ],
            'pdo_sqlsrv' => [
                'name' => 'PDO SQL Server Driver',
                'status' => extension_loaded('pdo_sqlsrv') || extension_loaded('pdo_dblib') || extension_loaded('pdo_odbc'),
                'value' => $this->getSqlServerDriver()
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'status' => extension_loaded('curl'),
                'value' => extension_loaded('curl') ? 'Instalado' : 'Não instalado'
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'status' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'Instalado' : 'Não instalado'
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'status' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'Instalado' : 'Não instalado'
            ],
            'json' => [
                'name' => 'JSON Extension',
                'status' => extension_loaded('json'),
                'value' => extension_loaded('json') ? 'Instalado' : 'Não instalado'
            ],
            'storage_writable' => [
                'name' => 'Diretório storage/ gravável',
                'status' => $this->checkStorageWritable(),
                'value' => $this->checkStorageWritable() ? 'Gravável' : 'Não gravável'
            ],
            'composer' => [
                'name' => 'Dependências Composer',
                'status' => file_exists($this->rootPath . '/vendor/autoload.php'),
                'value' => file_exists($this->rootPath . '/vendor/autoload.php') ? 'Instaladas' : 'Não instaladas'
            ]
        ];

        return $requirements;
    }

    /**
     * Obtém o driver SQL Server disponível
     */
    private function getSqlServerDriver()
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return 'pdo_sqlsrv (Recomendado)';
        } elseif (extension_loaded('pdo_dblib')) {
            return 'pdo_dblib';
        } elseif (extension_loaded('pdo_odbc')) {
            return 'pdo_odbc';
        }
        return 'Nenhum driver encontrado';
    }

    /**
     * Verifica se o diretório storage é gravável
     */
    private function checkStorageWritable()
    {
        $storagePath = $this->rootPath . '/storage';

        if (!file_exists($storagePath)) {
            @mkdir($storagePath, 0775, true);
        }

        return is_writable($storagePath);
    }

    /**
     * Testa a conexão com o banco de dados
     */
    public function testDatabaseConnection($config)
    {
        try {
            $driver = $config['db_connection'] ?? 'sqlsrv';
            $host = $config['db_host'] ?? 'localhost';
            $port = $config['db_port'] ?? '1433';
            $database = $config['db_database'] ?? 'MuOnline';
            $username = $config['db_username'] ?? '';
            $password = $config['db_password'] ?? '';

            // Construir DSN baseado no driver
            if ($driver === 'sqlsrv') {
                $dsn = "sqlsrv:Server={$host},{$port};Database={$database}";
            } elseif ($driver === 'dblib') {
                $dsn = "dblib:host={$host}:{$port};dbname={$database}";
            } elseif ($driver === 'odbc') {
                $dsn = "odbc:Driver={SQL Server};Server={$host},{$port};Database={$database}";
            } else {
                throw new Exception("Driver '{$driver}' não suportado");
            }

            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Testar se a tabela MEMB_INFO existe
            $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'MEMB_INFO'");
            $exists = $stmt->fetchColumn() > 0;

            if (!$exists) {
                $this->lastError = 'Banco de dados MU Online não encontrado. Verifique se as tabelas do servidor existem.';
                return false;
            }

            return true;

        } catch (Exception $e) {
            $this->lastError = 'Erro ao conectar: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Instala as tabelas do WebEngine no banco de dados
     */
    public function installDatabase($config)
    {
        try {
            $driver = $config['db_connection'] ?? 'sqlsrv';
            $host = $config['db_host'] ?? 'localhost';
            $port = $config['db_port'] ?? '1433';
            $database = $config['db_database'] ?? 'MuOnline';
            $username = $config['db_username'] ?? '';
            $password = $config['db_password'] ?? '';

            // Construir DSN
            if ($driver === 'sqlsrv') {
                $dsn = "sqlsrv:Server={$host},{$port};Database={$database}";
            } elseif ($driver === 'dblib') {
                $dsn = "dblib:host={$host}:{$port};dbname={$database}";
            } else {
                $dsn = "odbc:Driver={SQL Server};Server={$host},{$port};Database={$database}";
            }

            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Ler o arquivo schema.sql
            $schemaFile = $this->rootPath . '/database/schema.sql';
            if (!file_exists($schemaFile)) {
                throw new Exception('Arquivo schema.sql não encontrado em: ' . $schemaFile);
            }

            $sql = file_get_contents($schemaFile);

            // Remover comentários de linha única (-- comentário)
            $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);

            // Remover comentários multi-linha (/* comentário */)
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

            // Dividir em statements individuais (separados por GO)
            $statements = preg_split('/^\s*GO\s*$/mi', $sql);

            $executedCount = 0;
            $errorCount = 0;
            $errors = [];

            // Executar cada statement
            foreach ($statements as $index => $statement) {
                $statement = trim($statement);

                // Pular statements vazios
                if (empty($statement)) {
                    continue;
                }

                try {
                    $pdo->exec($statement);
                    $executedCount++;
                } catch (Exception $e) {
                    $errorMsg = $e->getMessage();

                    // Ignorar erros de objetos já existentes
                    if (
                        strpos($errorMsg, 'already exists') !== false ||
                        strpos($errorMsg, 'There is already') !== false ||
                        strpos($errorMsg, 'Cannot drop the') !== false
                    ) {
                        // Erro aceitável, continuar
                        continue;
                    }

                    // Erro crítico - registrar e lançar
                    $errorCount++;
                    $errors[] = "Statement #" . ($index + 1) . ": " . $errorMsg;

                    // Para debug - salvar statement com problema
                    $this->lastError = "Erro no SQL (statement #" . ($index + 1) . "): " . $errorMsg . "\n\nSQL: " . substr($statement, 0, 200);
                    throw $e;
                }
            }

            // Se nenhum statement foi executado, algo está errado
            if ($executedCount === 0 && $errorCount === 0) {
                throw new Exception('Nenhum comando SQL foi executado. Verifique o arquivo schema.sql');
            }

            return true;

        } catch (Exception $e) {
            $this->lastError = 'Erro ao instalar banco de dados: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Cria os diretórios necessários
     */
    public function createDirectories()
    {
        $directories = [
            '/storage',
            '/storage/cache',
            '/storage/logs',
            '/storage/uploads',
            '/storage/sessions'
        ];

        foreach ($directories as $dir) {
            $path = $this->rootPath . $dir;
            if (!file_exists($path)) {
                if (!@mkdir($path, 0775, true)) {
                    $this->lastError = "Não foi possível criar o diretório: {$dir}";
                    return false;
                }
            }

            // Adicionar .gitignore
            $gitignore = $path . '/.gitignore';
            if (!file_exists($gitignore)) {
                file_put_contents($gitignore, "*\n!.gitignore\n");
            }
        }

        return true;
    }

    /**
     * Cria o arquivo .env
     */
    public function createConfigFiles($dbConfig, $appConfig)
    {
        try {
            // Criar diretórios
            if (!$this->createDirectories()) {
                return false;
            }

            // Ler o template .env.example
            $envExample = $this->rootPath . '/.env.example';
            if (!file_exists($envExample)) {
                throw new Exception('Arquivo .env.example não encontrado');
            }

            $envContent = file_get_contents($envExample);

            // Helper function to escape env values
            $escapeEnvValue = function($value) {
                // Se contém espaços, aspas ou caracteres especiais, precisa de aspas
                if (empty($value)) {
                    return '';
                }
                if (preg_match('/[\s"#]/', $value)) {
                    // Escapar aspas duplas dentro do valor
                    $value = str_replace('"', '\\"', $value);
                    return '"' . $value . '"';
                }
                return $value;
            };

            // Substituir valores do banco de dados
            $envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=' . ($dbConfig['db_connection'] ?? 'sqlsrv'), $envContent);
            $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . ($dbConfig['db_host'] ?? 'localhost'), $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . ($dbConfig['db_port'] ?? '1433'), $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . ($dbConfig['db_database'] ?? 'MuOnline'), $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . ($dbConfig['db_username'] ?? 'sa'), $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $escapeEnvValue($dbConfig['db_password'] ?? ''), $envContent);

            // Substituir configurações da aplicação
            $envContent = preg_replace('/APP_NAME=.*/', 'APP_NAME=' . $escapeEnvValue($appConfig['app_name'] ?? 'WebEngine CMS'), $envContent);
            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . ($appConfig['app_url'] ?? 'http://localhost'), $envContent);
            $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=' . ($appConfig['app_env'] ?? 'production'), $envContent);

            // Configurações de email (opcional)
            if (!empty($appConfig['mail_host'])) {
                $envContent = preg_replace('/MAIL_HOST=.*/', 'MAIL_HOST=' . $appConfig['mail_host'], $envContent);
                $envContent = preg_replace('/MAIL_PORT=.*/', 'MAIL_PORT=' . ($appConfig['mail_port'] ?? '587'), $envContent);
                $envContent = preg_replace('/MAIL_USERNAME=.*/', 'MAIL_USERNAME=' . $escapeEnvValue($appConfig['mail_username'] ?? ''), $envContent);
                $envContent = preg_replace('/MAIL_PASSWORD=.*/', 'MAIL_PASSWORD=' . $escapeEnvValue($appConfig['mail_password'] ?? ''), $envContent);
                $envContent = preg_replace('/MAIL_FROM_ADDRESS=.*/', 'MAIL_FROM_ADDRESS=' . $escapeEnvValue($appConfig['mail_from'] ?? ''), $envContent);
            }

            // Gerar JWT secret aleatório
            $jwtSecret = bin2hex(random_bytes(32));
            $envContent = preg_replace('/JWT_SECRET=.*/', 'JWT_SECRET=' . $jwtSecret, $envContent);

            // Salvar arquivo .env
            $envFile = $this->rootPath . '/.env';
            if (file_put_contents($envFile, $envContent) === false) {
                throw new Exception('Não foi possível criar o arquivo .env');
            }

            return true;

        } catch (Exception $e) {
            $this->lastError = 'Erro ao criar arquivos de configuração: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Obtém o último erro
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Verifica se todos os requisitos foram atendidos
     */
    public function allRequirementsMet()
    {
        $requirements = $this->checkRequirements();
        foreach ($requirements as $req) {
            if (!$req['status']) {
                return false;
            }
        }
        return true;
    }
}
