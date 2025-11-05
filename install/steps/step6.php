<h2>📝 Finalizando Instalação</h2>

<p>Criando arquivos de configuração e finalizando a instalação.</p>

<div class="alert alert-success">
    <strong>✓ Banco de dados instalado com sucesso!</strong><br>
    Todas as tabelas foram criadas corretamente.
</div>

<div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 6px;">
    <h3>📋 Ações que serão executadas:</h3>
    <ul style="line-height: 2; margin-top: 15px;">
        <li>✓ Criar diretórios necessários (storage/, cache/, logs/)</li>
        <li>✓ Gerar arquivo de configuração .env</li>
        <li>✓ Configurar permissões de arquivos</li>
        <li>✓ Gerar chaves de segurança (JWT)</li>
        <li>✓ Criar arquivos .gitignore</li>
    </ul>
</div>

<div class="alert alert-warning">
    <strong>🔐 Segurança:</strong> Após a instalação, é recomendado remover ou proteger o diretório <code>/install</code> para evitar reinstalações não autorizadas.
</div>

<form method="POST">
    <div class="buttons">
        <a href="?step=5" class="btn btn-secondary">← Voltar</a>
        <button type="submit" class="btn btn-primary">Finalizar Instalação →</button>
    </div>
</form>

<script>
// Mostrar loading ao submeter
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('.btn-primary');
    btn.innerHTML = '⏳ Finalizando...';
    btn.disabled = true;
});
</script>
