<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - <?= $server_name ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f44336; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; border-radius: 0 0 5px 5px; }
        .btn { display: inline-block; padding: 12px 24px; background: #f44336; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .btn:hover { background: #d32f2f; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Redefinir Senha</h1>
        </div>
        
        <div class="content">
            <h2>Olá, <?= $user->username ?>!</h2>
            
            <p>Recebemos uma solicitação para redefinir sua senha no servidor <?= $server_name ?>.</p>
            
            <div class="warning">
                <h3>⚠️ Importante:</h3>
                <p>Se você não solicitou esta redefinição de senha, por favor ignore este email. Sua conta permanecerá segura.</p>
            </div>
            
            <div class="info">
                <h3>📋 Para redefinir sua senha:</h3>
                <ol>
                    <li>Clique no botão abaixo ou copie o link</li>
                    <li>Digite sua nova senha</li>
                    <li>Confirme sua nova senha</li>
                    <li>Sua senha será atualizada imediatamente</li>
                </ol>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?= $reset_url ?>" class="btn">🔐 Redefinir Senha</a>
            </div>
            
            <div class="info">
                <h3>🔗 Link de Redefinição:</h3>
                <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
                <div class="code"><?= $reset_url ?></div>
            </div>
            
            <div class="warning">
                <h3>⏰ Validade do Link:</h3>
                <p>Este link expirará em 24 horas por motivos de segurança. Após esse período, você precisará solicitar uma nova redefinição de senha.</p>
            </div>
            
            <div class="info">
                <h3>🔒 Requisitos de Senha:</h3>
                <ul>
                    <li>Mínimo de 8 caracteres</li>
                    <li>Pelo menos 1 número</li>
                    <li>Recomendado usar letras maiúsculas e minúsculas</li>
                    <li>Evite usar informações pessoais óbvias</li>
                </ul>
            </div>
            
            <div class="info">
                <h3>💡 Dicas de Segurança:</h3>
                <ul>
                    <li>🔐 Use uma senha única para esta conta</li>
                    <li>🚫 Não compartilhe sua senha com ninguém</li>
                    <li>📝 Anote sua senha em local seguro</li>
                    <li>🔄 Altere sua senha regularmente</li>
                    <li>🛡️ Use autenticação de dois fatores se disponível</li>
                </ul>
            </div>
            
            <h3>🆘 Problemas com a Redefinição?</h3>
            <p>Se você estiver enfrentando problemas para redefinir sua senha:</p>
            <ul>
                <li>📧 Entre em contato com nosso suporte: <?= $support_email ?></li>
                <li>💬 Use o chat de suporte no jogo</li>
                <li>📖 Verifique nosso FAQ: <a href="<?= config('url') ?>/faq">Clique aqui</a></li>
            </ul>
            
            <div class="info">
                <h3>📊 Informações da Solicitação:</h3>
                <ul>
                    <li><strong>Usuário:</strong> <?= $user->username ?></li>
                    <li><strong>Email:</strong> <?= $user->email ?></li>
                    <li><strong>IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido' ?></li>
                    <li><strong>Data:</strong> <?= date('d/m/Y H:i:s') ?></li>
                    <li><strong>Servidor:</strong> <?= $server_name ?></li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Atenciosamente,</strong></p>
                <p>🏰 Equipe <?= $server_name ?></p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= $server_name ?>. Todos os direitos reservados.</p>
            <p>Este é um email automático, por favor não responda.</p>
            <p>Se você não solicitou esta redefinição, sua conta está segura e pode ignorar este email.</p>
        </div>
    </div>
</body>
</html>
