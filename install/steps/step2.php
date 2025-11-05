<?php
$requirements = $installer->checkRequirements();
$allMet = $installer->allRequirementsMet();
?>

<h2>🔍 Verificação de Requisitos</h2>

<p>Verificando se o seu servidor atende aos requisitos mínimos para executar o WebEngine CMS.</p>

<div style="margin: 30px 0;">
    <ul class="requirement-list">
        <?php foreach ($requirements as $key => $req): ?>
            <li>
                <div>
                    <strong><?php echo $req['name']; ?></strong><br>
                    <small style="color: #6c757d;"><?php echo $req['value']; ?></small>
                </div>
                <span class="status-badge <?php echo $req['status'] ? 'status-ok' : 'status-fail'; ?>">
                    <?php echo $req['status'] ? '✓ OK' : '✗ FALHOU'; ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php if (!$allMet): ?>
    <div class="alert alert-danger">
        <strong>❌ Requisitos não atendidos!</strong><br>
        Alguns requisitos não foram atendidos. Por favor, corrija os problemas antes de continuar.
        <br><br>
        <strong>Problemas comuns:</strong>
        <ul style="margin-top: 10px;">
            <li>Execute <code>composer install</code> para instalar as dependências</li>
            <li>Instale as extensões PHP necessárias</li>
            <li>Configure permissões de escrita no diretório storage/</li>
            <li>Certifique-se de ter PHP 8.1 ou superior</li>
        </ul>
    </div>
<?php else: ?>
    <div class="alert alert-success">
        <strong>✓ Todos os requisitos foram atendidos!</strong><br>
        Seu servidor está pronto para executar o WebEngine CMS.
    </div>
<?php endif; ?>

<div class="buttons">
    <a href="?step=1" class="btn btn-secondary">← Voltar</a>
    <?php if ($allMet): ?>
        <form method="POST" style="margin: 0;">
            <button type="submit" class="btn btn-primary">Continuar →</button>
        </form>
    <?php else: ?>
        <button type="button" class="btn btn-primary" onclick="location.reload()">Verificar Novamente</button>
    <?php endif; ?>
</div>
