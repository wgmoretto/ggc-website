<h2>⚙️ Configurações Gerais</h2>

<p>Configure as informações básicas do seu servidor e site.</p>

<form method="POST">
    <h3>📱 Informações do Servidor</h3>

    <div class="form-group">
        <label for="app_name">Nome do Servidor</label>
        <input type="text" name="app_name" id="app_name" value="<?php echo $_SESSION['app_config']['app_name'] ?? 'MU Online Server'; ?>" required>
        <div class="help-text">Nome que será exibido no site</div>
    </div>

    <div class="form-group">
        <label for="app_url">URL do Site</label>
        <input type="text" name="app_url" id="app_url" value="<?php echo $_SESSION['app_config']['app_url'] ?? 'http://localhost'; ?>" required>
        <div class="help-text">URL completa do seu site (ex: http://meuservidor.com)</div>
    </div>

    <div class="form-group">
        <label for="app_env">Ambiente</label>
        <select name="app_env" id="app_env">
            <option value="production" selected>Produção</option>
            <option value="development">Desenvolvimento</option>
        </select>
        <div class="help-text">Use "Produção" para servidores online</div>
    </div>

    <hr style="margin: 30px 0; border: none; border-top: 2px solid #e9ecef;">

    <h3>📧 Configurações de Email (Opcional)</h3>
    <p style="margin-bottom: 20px; color: #6c757d;">
        Configure se deseja enviar emails de notificação e recuperação de senha.
        Você pode pular esta etapa e configurar depois no arquivo .env
    </p>

    <div class="two-columns">
        <div class="form-group">
            <label for="mail_host">Servidor SMTP</label>
            <input type="text" name="mail_host" id="mail_host" value="<?php echo $_SESSION['app_config']['mail_host'] ?? 'smtp.gmail.com'; ?>">
            <div class="help-text">Ex: smtp.gmail.com</div>
        </div>

        <div class="form-group">
            <label for="mail_port">Porta SMTP</label>
            <input type="text" name="mail_port" id="mail_port" value="<?php echo $_SESSION['app_config']['mail_port'] ?? '587'; ?>">
            <div class="help-text">Geralmente 587 ou 465</div>
        </div>
    </div>

    <div class="form-group">
        <label for="mail_username">Email de Envio</label>
        <input type="email" name="mail_username" id="mail_username" value="<?php echo $_SESSION['app_config']['mail_username'] ?? ''; ?>">
        <div class="help-text">Email que será usado para enviar mensagens</div>
    </div>

    <div class="form-group">
        <label for="mail_password">Senha do Email</label>
        <input type="password" name="mail_password" id="mail_password" value="<?php echo $_SESSION['app_config']['mail_password'] ?? ''; ?>">
        <div class="help-text">Senha do email ou senha de aplicativo</div>
    </div>

    <div class="form-group">
        <label for="mail_from">Endereço de Remetente</label>
        <input type="email" name="mail_from" id="mail_from" value="<?php echo $_SESSION['app_config']['mail_from'] ?? ''; ?>">
        <div class="help-text">Email que aparecerá como remetente (pode ser o mesmo do campo acima)</div>
    </div>

    <div class="alert alert-warning">
        <strong>💡 Dica:</strong> Se estiver usando Gmail, você precisará criar uma "Senha de App" nas configurações de segurança da sua conta Google.
    </div>

    <div class="buttons">
        <a href="?step=3" class="btn btn-secondary">← Voltar</a>
        <button type="submit" class="btn btn-primary">Continuar →</button>
    </div>
</form>
