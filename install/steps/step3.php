<h2>🗄️ Configuração do Banco de Dados</h2>

<p>Configure a conexão com o banco de dados MSSQL do seu servidor MU Online.</p>

<form method="POST">
    <div class="form-group">
        <label for="db_connection">Driver do Banco de Dados</label>
        <select name="db_connection" id="db_connection" required>
            <option value="sqlsrv" <?php echo (isset($_POST['db_connection']) && $_POST['db_connection'] === 'sqlsrv') ? 'selected' : ''; ?>>
                SQL Server (pdo_sqlsrv) - Recomendado
            </option>
            <option value="dblib" <?php echo (isset($_POST['db_connection']) && $_POST['db_connection'] === 'dblib') ? 'selected' : ''; ?>>
                SQL Server (pdo_dblib) - Linux
            </option>
            <option value="odbc" <?php echo (isset($_POST['db_connection']) && $_POST['db_connection'] === 'odbc') ? 'selected' : ''; ?>>
                SQL Server (pdo_odbc) - ODBC
            </option>
        </select>
        <div class="help-text">Escolha o driver PDO disponível no seu servidor</div>
    </div>

    <div class="two-columns">
        <div class="form-group">
            <label for="db_host">Host do Banco de Dados</label>
            <input type="text" name="db_host" id="db_host" value="<?php echo $_POST['db_host'] ?? 'localhost'; ?>" required>
            <div class="help-text">Geralmente: localhost ou 127.0.0.1</div>
        </div>

        <div class="form-group">
            <label for="db_port">Porta</label>
            <input type="text" name="db_port" id="db_port" value="<?php echo $_POST['db_port'] ?? '1433'; ?>" required>
            <div class="help-text">Porta padrão do MSSQL: 1433</div>
        </div>
    </div>

    <div class="form-group">
        <label for="db_database">Nome do Banco de Dados</label>
        <input type="text" name="db_database" id="db_database" value="<?php echo $_POST['db_database'] ?? 'MuOnline'; ?>" required>
        <div class="help-text">Nome do banco de dados do MU Online (padrão: MuOnline)</div>
    </div>

    <div class="two-columns">
        <div class="form-group">
            <label for="db_username">Usuário</label>
            <input type="text" name="db_username" id="db_username" value="<?php echo $_POST['db_username'] ?? 'sa'; ?>" required>
            <div class="help-text">Usuário do banco de dados</div>
        </div>

        <div class="form-group">
            <label for="db_password">Senha</label>
            <input type="password" name="db_password" id="db_password" value="<?php echo $_POST['db_password'] ?? ''; ?>">
            <div class="help-text">Senha do banco de dados (deixe vazio se não houver)</div>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>⚠️ Importante:</strong> O instalador irá criar novas tabelas (GGC_*) no banco de dados existente.
        As tabelas do seu servidor MU Online não serão modificadas, exceto pela adição de algumas colunas opcionais na tabela MEMB_INFO e Character.
    </div>

    <div class="buttons">
        <a href="?step=2" class="btn btn-secondary">← Voltar</a>
        <button type="submit" class="btn btn-primary">Testar Conexão →</button>
    </div>
</form>
