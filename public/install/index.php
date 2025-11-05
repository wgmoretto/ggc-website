<?php
/**
 * WebEngine CMS - Instalador Web
 *
 * Sistema de instalação interativo para o WebEngine CMS
 */

session_start();

// Prevenir acesso se já instalado
if (file_exists('../../.env') && !isset($_GET['reinstall'])) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Já Instalado</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 50px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #e74c3c; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>⚠️ Sistema Já Instalado</h1>
            <p>O WebEngine CMS já está instalado. Para reinstalar, remova o arquivo <code>.env</code> primeiro.</p>
            <p><a href="../">← Ir para o site</a></p>
        </div>
    </body>
    </html>
    ');
}

require_once 'Installer.php';

$installer = new Installer();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            // Verificar requisitos
            $_SESSION['requirements_checked'] = true;
            header('Location: ?step=3');
            exit;

        case 3:
            // Configurar banco de dados
            if ($installer->testDatabaseConnection($_POST)) {
                $_SESSION['db_config'] = $_POST;
                header('Location: ?step=4');
                exit;
            } else {
                $error = $installer->getLastError();
            }
            break;

        case 4:
            // Configurações gerais
            $_SESSION['app_config'] = $_POST;
            header('Location: ?step=5');
            exit;

        case 5:
            // Instalar banco de dados
            if ($installer->installDatabase($_SESSION['db_config'])) {
                $_SESSION['db_installed'] = true;
                header('Location: ?step=6');
                exit;
            } else {
                $error = $installer->getLastError();
            }
            break;

        case 6:
            // Criar arquivos e finalizar
            if ($installer->createConfigFiles($_SESSION['db_config'], $_SESSION['app_config'])) {
                $_SESSION['installation_complete'] = true;
                header('Location: ?step=7');
                exit;
            } else {
                $error = $installer->getLastError();
            }
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebEngine CMS - Instalador</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .progress {
            display: flex;
            background: #f8f9fa;
            padding: 20px;
            justify-content: space-between;
        }
        .progress-step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
            font-size: 12px;
            color: #6c757d;
        }
        .progress-step::before {
            content: attr(data-step);
            display: block;
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            margin: 0 auto 10px;
            line-height: 40px;
            font-weight: bold;
        }
        .progress-step.active {
            color: #667eea;
            font-weight: bold;
        }
        .progress-step.active::before {
            background: #667eea;
            color: white;
        }
        .progress-step.completed::before {
            background: #28a745;
            color: white;
            content: '✓';
        }
        .content {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .requirement-list {
            list-style: none;
        }
        .requirement-list li {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 6px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-ok {
            background: #d4edda;
            color: #155724;
        }
        .status-fail {
            background: #f8d7da;
            color: #721c24;
        }
        .buttons {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .success-icon {
            font-size: 80px;
            text-align: center;
            margin: 30px 0;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 WebEngine CMS</h1>
            <p>Sistema de Instalação Interativo</p>
        </div>

        <div class="progress">
            <div class="progress-step <?php echo $step >= 1 ? 'completed' : ''; ?>" data-step="1">Bem-vindo</div>
            <div class="progress-step <?php echo $step == 2 ? 'active' : ($step > 2 ? 'completed' : ''); ?>" data-step="2">Requisitos</div>
            <div class="progress-step <?php echo $step == 3 ? 'active' : ($step > 3 ? 'completed' : ''); ?>" data-step="3">Banco de Dados</div>
            <div class="progress-step <?php echo $step == 4 ? 'active' : ($step > 4 ? 'completed' : ''); ?>" data-step="4">Configurações</div>
            <div class="progress-step <?php echo $step == 5 ? 'active' : ($step > 5 ? 'completed' : ''); ?>" data-step="5">Instalação</div>
            <div class="progress-step <?php echo $step == 6 ? 'active' : ($step > 6 ? 'completed' : ''); ?>" data-step="6">Finalizar</div>
            <div class="progress-step <?php echo $step == 7 ? 'active' : ''; ?>" data-step="7">Concluído</div>
        </div>

        <div class="content">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php
            // Incluir o arquivo de step apropriado
            $stepFile = __DIR__ . '/steps/step' . $step . '.php';
            if (file_exists($stepFile)) {
                include $stepFile;
            } else {
                echo '<div class="alert alert-danger">Passo inválido!</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
