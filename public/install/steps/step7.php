<?php
// Limpar sessão de instalação
$_SESSION = [];
?>

<div class="success-icon">🎉</div>

<h2 style="text-align: center; color: #28a745;">Instalação Concluída com Sucesso!</h2>

<p style="text-align: center; font-size: 16px; color: #6c757d; margin: 20px 0;">
    O WebEngine CMS foi instalado e está pronto para uso!
</p>

<div class="alert alert-success">
    <strong>✓ Tudo pronto!</strong><br>
    Seu site está configurado e pronto para receber visitantes.
</div>

<div style="margin: 30px 0; padding: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
    <h3 style="color: white; margin-bottom: 20px;">🚀 Próximos Passos</h3>

    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; margin-bottom: 15px;">
        <strong>1. Acesse o Painel Administrativo</strong><br>
        <span style="opacity: 0.9;">Crie uma conta e defina o campo <code>admin_level = 1</code> na tabela MEMB_INFO para ter acesso administrativo.</span>
    </div>

    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; margin-bottom: 15px;">
        <strong>2. Segurança</strong><br>
        <span style="opacity: 0.9;">Remova ou proteja o diretório <code>/install</code> para evitar reinstalações não autorizadas.</span>
    </div>

    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; margin-bottom: 15px;">
        <strong>3. Configure o PayPal (Opcional)</strong><br>
        <span style="opacity: 0.9;">Adicione suas credenciais do PayPal no arquivo <code>.env</code> para ativar doações.</span>
    </div>

    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px;">
        <strong>4. Personalize seu Site</strong><br>
        <span style="opacity: 0.9;">Edite os templates em <code>/templates</code> para personalizar o visual do seu site.</span>
    </div>
</div>

<div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 6px;">
    <h3>📚 Recursos Úteis</h3>
    <ul style="line-height: 2; margin-top: 15px;">
        <li><strong>Documentação:</strong> Consulte o README.md para mais informações</li>
        <li><strong>Configurações:</strong> Arquivo .env contém todas as configurações</li>
        <li><strong>Logs:</strong> Verifique storage/logs/ em caso de problemas</li>
        <li><strong>Cache:</strong> Limpe storage/cache/ se necessário</li>
    </ul>
</div>

<div class="alert alert-warning">
    <strong>🔒 Importante - Segurança:</strong><br>
    <div style="margin-top: 10px;">
        <strong>Remover diretório de instalação:</strong><br>
        Execute no terminal: <code>rm -rf public/install/</code><br>
        Ou renomeie o diretório para evitar acesso não autorizado.
    </div>
</div>

<div style="text-align: center; margin-top: 40px;">
    <a href="../" class="btn btn-primary" style="font-size: 18px; padding: 15px 40px;">
        🎮 Ir para o Site →
    </a>
</div>

<div style="margin-top: 40px; padding: 20px; text-align: center; color: #6c757d; border-top: 2px solid #e9ecef;">
    <p><strong>WebEngine CMS</strong> - Versão 2.0.0</p>
    <p style="font-size: 12px; margin-top: 10px;">
        Desenvolvido com ❤️ para a comunidade MU Online
    </p>
</div>
