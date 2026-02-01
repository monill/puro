<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao <?= $server_name ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .footer {
            background: #333;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 5px 5px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }

        .btn:hover {
            background: #45a049;
        }

        .info-box {
            background: #e8f5e8;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }

        .stat {
            background: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏰 Bem-vindo ao <?= $server_name ?>!</h1>
        </div>

        <div class="content">
            <h2>Olá, <?= $user->username ?>!</h2>

            <p>Seja bem-vindo ao nosso servidor de ! Sua conta foi criada com sucesso e você está pronto para começar sua jornada.</p>

            <?php if ($password): ?>
                <div class="info-box">
                    <h3>🔐 Suas Credenciais de Acesso:</h3>
                    <p><strong>Usuário:</strong> <?= $user->username ?></p>
                    <p><strong>Senha:</strong> <?= $password ?></p>
                    <p><strong>Email:</strong> <?= $user->email ?></p>
                    <p><strong>Tribo:</strong> <?= ucfirst($user->tribe) ?></p>
                </div>
            <?php endif; ?>

            <div class="stats">
                <div class="stat">
                    <h3>🎮 Sua Aldeia Inicial</h3>
                    <p>Você começa com uma aldeia completa com:</p>
                    <ul>
                        <li>🏘️ 2 habitantes</li>
                        <li>🪵 500 de madeira</li>
                        <li>🧱 500 de argila</li>
                        <li>⚒️ 500 de ferro</li>
                        <li>🌾 500 de cereais</li>
                    </ul>
                </div>

                <div class="stat">
                    <h3>🚀 Próximos Passos</h3>
                    <ol>
                        <li>Faça login no servidor</li>
                        <li>Explore sua aldeia</li>
                        <li>Construa edifícios</li>
                        <li>Treine tropas</li>
                        <li>Expanda seu império!</li>
                    </ol>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?= $login_url ?>" class="btn">🎮 Fazer Login Agora</a>
            </div>

            <div class="info-box">
                <h3>💡 Dicas para Iniciantes:</h3>
                <ul>
                    <li>🏗️ Comece construindo o Edifício Principal para aumentar a velocidade de construção</li>
                    <li>🌾 Mantenha sempre cereais suficientes para alimentar sua população e tropas</li>
                    <li>⚔️ Treine tropas básicas para se defender de ataques</li>
                    <li>🤝 Procure uma aliança para obter proteção e suporte</li>
                    <li>📈 Expanda sua aldeia para aumentar sua produção de recursos</li>
                </ul>
            </div>

            <div class="info-box">
                <h3>📋 Informações do Servidor:</h3>
                <ul>
                    <li><strong>Servidor:</strong> <?= $server_name ?></li>
                    <li><strong>Velocidade:</strong> <?= config('game.speed', 1) ?>x</li>
                    <li><strong>Jogadores Máximos:</strong> <?= config('game.max_players', 1000) ?></li>
                    <li><strong>Tamanho do Mapa:</strong> <?= config('game.map_size', 400) ?>x<?= config('game.map_size', 400) ?></li>
                    <li><strong>Proteção Inicial:</strong> <?= config('game.protection_time', 72) ?> horas</li>
                </ul>
            </div>

            <h3>🆘 Precisa de Ajuda?</h3>
            <p>Nossa equipe de suporte está sempre disponível para ajudar você. Entre em contato conosco:</p>
            <ul>
                <li>📧 Email: <?= $support_email ?></li>
                <li>💬 Chat: Disponível no jogo</li>
                <li>📖 Wiki: <a href="<?= config('url') ?>/wiki">Clique aqui</a></li>
                <li>❓ FAQ: <a href="<?= config('url') ?>/faq">Clique aqui</a></li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Boa sorte e divirta-se!</strong></p>
                <p>🏰 Equipe <?= $server_name ?></p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= $server_name ?>. Todos os direitos reservados.</p>
            <p>Este é um email automático, por favor não responda.</p>
            <p>Se você não criou esta conta, entre em contato conosco imediatamente.</p>
        </div>
    </div>
</body>

</html>
