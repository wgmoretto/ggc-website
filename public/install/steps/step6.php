<h2>📝 Finalizando Instalação</h2>

<p>Criando arquivos de configuração e finalizando a instalação.</p>

<?php if (isset($_SESSION['install_summary'])):
    $summary = $_SESSION['install_summary'];
    $hasErrors = $summary['errors'] > 0;
?>
    <div class="alert <?php echo $hasErrors ? 'alert-warning' : 'alert-success'; ?>">
        <strong><?php echo $hasErrors ? '⚠️' : '✓'; ?> Banco de dados instalado<?php echo $hasErrors ? ' com avisos' : ' com sucesso'; ?>!</strong><br>

        <div style="margin-top: 10px; font-size: 14px;">
            <strong>Resumo da instalação:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>✓ Executados: <strong><?php echo $summary['success']; ?></strong> statements</li>
                <li>○ Pulados: <strong><?php echo $summary['skipped']; ?></strong> (já existentes)</li>
                <?php if ($summary['errors'] > 0): ?>
                    <li>⚠️ Erros não críticos: <strong><?php echo $summary['errors']; ?></strong></li>
                <?php endif; ?>
            </ul>

            <?php if ($hasErrors && !empty($summary['error_details'])): ?>
                <details style="margin-top: 10px;">
                    <summary style="cursor: pointer; font-weight: bold;">Ver detalhes dos erros</summary>
                    <div style="margin-top: 10px; padding: 10px; background: rgba(0,0,0,0.05); border-radius: 4px; font-size: 12px; max-height: 200px; overflow-y: auto;">
                        <?php foreach (array_slice($summary['error_details'], 0, 5) as $err): ?>
                            <div style="margin-bottom: 8px;">
                                <strong>Statement #<?php echo $err['statement']; ?>:</strong><br>
                                <?php echo htmlspecialchars(substr($err['message'], 0, 200)); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success">
        <strong>✓ Banco de dados instalado com sucesso!</strong><br>
        Todas as tabelas foram criadas corretamente.
    </div>
<?php endif; ?>

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
