<h2>💾 Instalação do Banco de Dados</h2>

<p>Esta etapa irá criar todas as tabelas necessárias para o WebEngine CMS funcionar.</p>

<div class="alert alert-warning">
    <strong>⚠️ O que será feito:</strong>
    <ul style="margin-top: 10px; margin-left: 20px;">
        <li>Criar tabelas GGC_* (Bans, News, Credits, Votes, etc.)</li>
        <li>Adicionar colunas 'credits' e 'admin_level' na tabela MEMB_INFO</li>
        <li>Adicionar colunas 'Resets' e 'GrandResets' na tabela Character</li>
        <li>Criar stored procedures para operações comuns</li>
        <li>Inserir dados iniciais (sites de voto, pacotes de doação, etc.)</li>
    </ul>
</div>

<div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 6px;">
    <h3>📊 Configuração atual:</h3>
    <table style="width: 100%;">
        <tr>
            <td style="padding: 8px;"><strong>Host:</strong></td>
            <td style="padding: 8px;"><?php echo htmlspecialchars($_SESSION['db_config']['db_host'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td style="padding: 8px;"><strong>Porta:</strong></td>
            <td style="padding: 8px;"><?php echo htmlspecialchars($_SESSION['db_config']['db_port'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td style="padding: 8px;"><strong>Banco:</strong></td>
            <td style="padding: 8px;"><?php echo htmlspecialchars($_SESSION['db_config']['db_database'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td style="padding: 8px;"><strong>Driver:</strong></td>
            <td style="padding: 8px;"><?php echo htmlspecialchars($_SESSION['db_config']['db_connection'] ?? 'N/A'); ?></td>
        </tr>
    </table>
</div>

<div class="alert alert-success">
    <strong>✓ Pronto para instalar</strong><br>
    Clique no botão abaixo para iniciar a instalação das tabelas do banco de dados.
    Este processo pode levar alguns segundos.
</div>

<form method="POST">
    <div class="buttons">
        <a href="?step=4" class="btn btn-secondary">← Voltar</a>
        <button type="submit" class="btn btn-primary">Instalar Banco de Dados →</button>
    </div>
</form>

<script>
// Mostrar loading ao submeter
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('.btn-primary');
    btn.innerHTML = '⏳ Instalando...';
    btn.disabled = true;
});
</script>
