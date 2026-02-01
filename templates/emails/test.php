<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Email - <?= $server_name ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; border-radius: 0 0 5px 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .config-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .config-item { background: white; padding: 15px; border-radius: 5px; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 0.8em; }
        .status.success { background: #28a745; color: white; }
        .status.error { background: #dc3545; color: white; }
        .status.warning { background: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Teste de Configuração de Email</h1>
        </div>
        
        <div class="content">
            <div class="success">
                <h3>✅ Email Enviado com Sucesso!</h3>
                <p>Este email confirma que a configuração de email do servidor <?= $server_name ?> está funcionando corretamente.</p>
            </div>
            
            <div class="info">
                <h3>📋 Informações do Teste:</h3>
                <ul>
                    <li><strong>Servidor:</strong> <?= $server_name ?></li>
                    <li><strong>Data/Hora:</strong> <?= $test_time ?></li>
                    <li><strong>Driver:</strong> <?= $config['driver'] ?></li>
                    <li><strong>Host SMTP:</strong> <?= $config['host'] ?></li>
                    <li><strong>Porta:</strong> <?= $config['port'] ?></li>
                    <li><strong>Criptografia:</strong> <?= $config['encryption'] ?? 'Nenhuma' ?></li>
                </ul>
            </div>
            
            <div class="info">
                <h3>⚙️ Configuração Atual:</h3>
                <div class="config-grid">
                    <div class="config-item">
                        <h4>📧 Configuração SMTP</h4>
                        <ul>
                            <li><strong>Driver:</strong> <?= $config['driver'] ?></li>
                            <li><strong>Host:</strong> <?= $config['host'] ?></li>
                            <li><strong>Porta:</strong> <?= $config['port'] ?></li>
                            <li><strong>Usuário:</strong> <?= $config['username'] ?: 'Não configurado' ?></li>
                            <li><strong>Senha:</strong> <?= $config['password'] ? 'Configurada' : 'Não configurada' ?></li>
                            <li><strong>Criptografia:</strong> <?= $config['encryption'] ?? 'Nenhuma' ?></li>
                        </ul>
                    </div>
                    
                    <div class="config-item">
                        <h4>📨 Configuração de Envio</h4>
                        <ul>
                            <li><strong>De:</strong> <?= $config['from']['address'] ?></li>
                            <li><strong>Nome:</strong> <?= $config['from']['name'] ?></li>
                            <li><strong>Reply-To:</strong> <?= $config['reply_to']['address'] ?></li>
                            <li><strong>Logging:</strong> <?= $config['logging'] ? 'Ativado' : 'Desativado' ?></li>
                            <li><strong>Debug:</strong> <?= $config['debug'] ? 'Ativado' : 'Desativado' ?></li>
                            <li><strong>Charset:</strong> UTF-8</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="info">
                <h3>🔍 Status dos Componentes:</h3>
                <ul>
                    <li><strong>PHPMailer:</strong> <span class="status success">Instalado</span></li>
                    <li><strong>Extensão OpenSSL:</strong> <span class="status success">Ativa</span></li>
                    <li><strong>Extensão MBString:</strong> <span class="status success">Ativa</span></li>
                    <li><strong>Função mail():</strong> <span class="<?= function_exists('mail') ? 'status success' : 'status error' ?>"><?= function_exists('mail') ? 'Disponível' : 'Não disponível' ?></span></li>
                    <li><strong>Conexão SMTP:</strong> <span class="status success">Funcionando</span></li>
                    <li><strong>Autenticação:</strong> <span class="<?= !empty($config['username']) && !empty($config['password']) ? 'status success' : 'status warning' ?>"><?= !empty($config['username']) && !empty($config['password']) ? 'Configurada' : 'Não configurada' ?></span></li>
                </ul>
            </div>
            
            <div class="info">
                <h3>📊 Recursos Disponíveis:</h3>
                <ul>
                    <li>✅ Envio de emails HTML</li>
                    <li>✅ Envio de emails texto</li>
                    <li>✅ Anexos de arquivos</li>
                    <li>✅ Múltiplos destinatários</li>
                    <li>✅ CC e BCC</li>
                    <li>✅ Headers personalizados</li>
                    <li>✅ Logging de envio</li>
                    <li>✅ Debug mode</li>
                </ul>
            </div>
            
            <div class="info">
                <h3>🎮 Tipos de Email Disponíveis:</h3>
                <ul>
                    <li>📧 Email de boas-vindas</li>
                    <li>🔐 Redefinição de senha</li>
                    <li>✅ Verificação de email</li>
                    <li>📢 Notificações do jogo</li>
                    <li>⚔️ Relatórios de batalha</li>
                    <li>🤝 Convites de aliança</li>
                    <li>📊 Relatórios de sistema</li>
                    <li>🎉 Mensagens de celebração</li>
                </ul>
            </div>
            
            <div class="info">
                <h3>🚀 Próximos Passos:</h3>
                <ol>
                    <li>Verifique se este email foi recebido corretamente</li>
                    <li>Teste o envio para diferentes destinatários</li>
                    <li>Configure os templates de email personalizados</li>
                    <li>Ative o logging para monitorar envios</li>
                    <li>Teste o envio de emails com anexos</li>
                </ol>
            </div>
            
            <div class="info">
                <h3>🔧 Configurações Avançadas:</h3>
                <ul>
                    <li><strong>Rate Limiting:</strong> <?= $config['rate_limit']['enabled'] ? 'Ativado' : 'Desativado' ?> (<?= $config['rate_limit']['max_per_hour'] ?> por hora)</li>
                    <li><strong>Email Queue:</strong> <?= $config['queue']['enabled'] ? 'Ativado' : 'Desativado' ?></li>
                    <li><strong>SSL Verify:</strong> <?= $config['verify_ssl'] ? 'Ativado' : 'Desativado' ?></li>
                    <li><strong>Self Signed:</strong> <?= $config['allow_self_signed'] ? 'Permitido' : 'Não permitido' ?></li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <p><strong>✨ Sistema de email configurado com sucesso!</strong></p>
                <p>🏰 Equipe <?= $server_name ?></p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= $server_name ?>. Todos os direitos reservados.</p>
            <p>Este é um email de teste automático.</p>
        </div>
    </div>
</body>
</html>
