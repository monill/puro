</div>
</main>

<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Servidor</h3>
                <ul>
                    <li><a href="/rules">Regras</a></li>
                    <li><a href="/statistics">Estatísticas</a></li>
                    <li><a href="/ranking">Ranking</a></li>
                    <li><a href="/support">Suporte</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Comunidade</h3>
                <ul>
                    <li><a href="/forum">Fórum</a></li>
                    <li><a href="/chat">Chat</a></li>
                    <li><a href="/alliances">Alianças</a></li>
                    <li><a href="/players">Jogadores</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Ajuda</h3>
                <ul>
                    <li><a href="/tutorial">Tutorial</a></li>
                    <li><a href="/faq">FAQ</a></li>
                    <li><a href="/wiki">Wiki</a></li>
                    <li><a href="/contact">Contato</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Informações</h3>
                <ul>
                    <li>Servidor: <?= config('name', ' Puro') ?></li>
                    <li>Versão: <?= config('version', '1.0.0') ?></li>
                    <li>Jogadores: <?= $total_users ?? 0 ?></li>
                    <li>Aldeias: <?= $total_villages ?? 0 ?></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= config('name', ' Puro') ?>. Todos os direitos reservados.</p>
            <p>
                <a href="/terms">Termos de Uso</a> |
                <a href="/privacy">Política de Privacidade</a> |
                <a href="/imprint">Imprint</a>
            </p>
        </div>
    </div>
</footer>

<!-- JavaScript Bottom -->
<script src="/assets/js/main.js" type="text/javascript"></script>
<script src="/assets/js/game.js" type="text/javascript"></script>

<?php if (isset($extra_js)): ?>
    <?php foreach ($extra_js as $js): ?>
        <script src="<?= $js ?>" type="text/javascript"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- CSRF Token -->
<?php if (config('csrf.enabled', true)): ?>
    <script type="text/javascript">
        window.csrfToken = '<?= csrf_token() ?>';
    </script>
<?php endif; ?>

<!-- Game Configuration -->
<script type="text/javascript">
    window.gameConfig = {
        baseUrl: '<?= config('url', 'http://localhost/puro') ?>',
        userId: <?= auth()->check() ? auth()->user()->id : 'null' ?>,
        gameSpeed: <?= config('game.speed', 1) ?>,
        mapSize: <?= config('game.map_size', 400) ?>,
        maxVillages: <?= config('game.max_villages', 10) ?>,
        resourceProduction: <?= config('game.resource_production', 1) ?>,
        troopSpeed: <?= config('game.troop_speed', 1) ?>,
        buildingSpeed: <?= config('game.building_speed', 1) ?>,
        researchSpeed: <?= config('game.research_speed', 1) ?>,
        protectionTime: <?= config('game.protection_time', 72) ?>,
        tradeRatio: <?= config('game.trade_ratio', 1) ?>,
        catapultDamage: <?= config('game.catapult_damage', 1) ?>,
        serverTime: '<?= date('Y-m-d H:i:s') ?>',
        serverTimezone: '<?= config('timezone', 'America/Sao_Paulo') ?>',
        language: '<?= config('locale', 'pt-br') ?>',
        debugMode: <?= config('debug', false) ? 'true' : 'false' ?>,
        maintenanceMode: <?= config('maintenance_mode', false) ? 'true' : 'false' ?>,
        registrationEnabled: <?= config('registration.enabled', true) ? 'true' : 'false' ?> ',
        maxPlayers: <?= config('game.max_players', 1000) ?>,
        currentPlayers: <?= $online_users ?? 0 ?>,
        allianceLimit: <?= config('game.alliance_limit', 50) ?>,
        villageLimit: <?= config('game.village_limit', 10) ?>,
        celebrationCost: <?= config('game.celebration_cost', 10000) ?> ',
        celebrationDuration: <?= config('game.celebration_duration', 24) ?> ',
        spyReportsEnabled: <?= config('spy_reports', true) ? 'true' : 'false' ?> ',
        battleReportsEnabled: <?= config('battle_reports', true) ? 'true' : 'false' ?> ',
        tradeReportsEnabled: <?= config('trade_reports', true) ? 'true' : 'false' ?> ',
        constructionReportsEnabled: <?= config('construction_reports', true) ? 'true' : 'false' ?> ',
        researchReportsEnabled: <?= config('research_reports', true) ? 'true' : 'false' ?> ',
        allianceReportsEnabled: <?= config('alliance_reports', true) ? 'true' : 'false' ?> ',
        messageLimitPerHour: <?= config('message_limit_per_hour', 10) ?> ',
        reportRetentionDays: <?= config('report_retention_days', 30) ?> ',
        messageRetentionDays: <?= config('message_retention_days', 30) ?> ',
        logRetentionDays: <?= config('log_retention_days', 7) ?> ',
        cacheCleanupInterval: <?= config('cache_cleanup_interval', 3600) ?> ',
        sessionTimeout: <?= config('session_timeout', 7200) ?> ',
        loginAttemptsLimit: <?= config('login_attempts_limit', 5) ?> ',
        loginLockoutTime: <?= config('login_lockout_time', 900) ?> ',
        passwordMinLength: <?= config('password_min_length', 8) ?> ',
        usernameMinLength: <?= config('username_min_length', 3) ?> ',
        usernameMaxLength: <?= config('username_max_length', 20) ?> ',
        villageNameMinLength: <?= config('village_name_min_length', 3) ?> ',
        villageNameMaxLength: <?= config('village_name_max_length', 30) ?> ',
        allianceNameMinLength: <?= config('alliance_name_min_length', 3) ?> ',
        allianceNameMaxLength: <?= config('alliance_name_max_length', 20) ?> ',
        allianceTagMinLength: <?= config('alliance_tag_min_length', 2) ?> ',
        allianceTagMaxLength: <?= config('alliance_tag_max_length', 5) ?> ',
        messageSubjectMaxLength: <?= config('message_subject_max_length', 100) ?> ',
        messageContentMaxLength: <?= config('message_content_max_length', 1000) ?> ',
        reportTitleMaxLength: <?= config('report_title_max_length', 200) ?> ',
        reportContentMaxLength: <?= config('report_content_max_length', 5000) ?> ',
        marketOfferMaxAmount: <?= config('market_offer_max_amount', 100000) ?> ',
        marketMinTradeRatio: <?= config('market_min_trade_ratio', 0.5) ?> ',
        marketMaxTradeRatio: <?= config('market_max_trade_ratio', 3) ?> ',
        troopMovementSpeedBase: <?= config('troop_movement_speed_base', 1) ?> ',
        troopMovementSpeedCavalry: <?= config('troop_movement_speed_cavalry', 2) ?> ',
        troopMovementSpeedSiege: <?= config('troop_movement_speed_siege', 0.5) ?> ',
        buildingConstructionTimeBase: <?= config('building_construction_time_base', 1) ?> ',
        buildingConstructionTimeMain: <?= config('building_construction_time_main', 0.5) ?> ',
        buildingConstructionTimePalace: <?= config('building_construction_time_palace', 2) ?> ',
        resourceProductionBase: <?= config('resource_production_base', 1) ?> ',
        resourceProductionBonus: <?= config('resource_production_bonus', 0.25) ?> ',
        storageCapacityBase: <?= config('storage_capacity_base', 80000) ?> ',
        storageCapacityBonus: <?= config('storage_capacity_bonus', 1000) ?> ',
        cropConsumptionBase: <?= config('crop_consumption_base', 1) ?> ',
        cropConsumptionCavalry: <?= config('crop_consumption_cavalry', 2) ?> ',
        cropConsumptionSiege: <?= config('crop_consumption_siege', 3) ?> ',
        troopTrainingTimeBase: <?= config('troop_training_time_base', 1) ?> ',
        troopTrainingTimeBarracks: <?= config('troop_training_time_barracks', 1) ?> ',
        troopTrainingTimeStable: <?= config('troop_training_time_stable', 1.5) ?> ',
        troopTrainingTimeWorkshop: <?= config('troop_training_time_workshop', 2) ?> ',
        researchTimeBase: <?= config('research_time_base', 1) ?> ',
        researchTimeAcademy: <?= config('research_time_academy', 1) ?> ',
        celebrationTypeSmall: '<?= config('celebration_type_small', 'small') ?>',
        celebrationTypeLarge: '<?= config('celebration_type_large', 'large') ?>',
        celebrationBonusSmall: <?= config('celebration_bonus_small', 200) ?> ',
        celebrationBonusLarge: <?= config('celebration_bonus_large', 1000) ?> ',
        culturePointsPerVillage: <?= config('culture_points_per_village', 1) ?> ',
        culturePointsPerPopulation: <?= config('culture_points_per_population', 0.01) ?> ',
        allianceCreationCulturePoints: <?= config('alliance_creation_culture_points', 10000) ?> ',
        villageExpansionCulturePoints: <?= config('village_expansion_culture_points', 1000) ?> ',
        maxVillagesPerPlayer: <?= config('max_villages_per_player', 10) ?> ',
        villageExpansionDistance: <?= config('village_expansion_distance', 30) ?> ',
        villageExpansionCostBase: <?= config('village_expansion_cost_base', 750) ?> ',
        villageExpansionCostMultiplier: <?= config('village_expansion_cost_multiplier', 1.5) ?> ',
        wonderConstructionTime: <?= config('wonder_construction_time', 100) ?> ',
        wonderLevels: <?= config('wonder_levels', 50) ?> ',
        wonderVillagesCount: <?= config('wonder_villages_count', 13) ?> ',
        wonderActivationLevel: <?= config('wonder_activation_level', 50) ?> ',
        endGameDuration: <?= config('end_game_duration', 168) ?> ',
        natarsAttackInterval: <?= config('natars_attack_interval', 24) ?> ',
        natarsTroopStrength: <?= config('natars_troop_strength', 2) ?> ',
        artifactSpawnInterval: <?= config('artifact_spawn_interval', 168) ?> ',
        artifactDuration: <?= config('artifact_duration', 168) ?> ',
        artifactTypes: '<?= config('artifact_types', 'attack,defense,building,speed,resource') ?>',
        artifactStrength: <?= config('artifact_strength', 1.5) ?> ',
        heroExperienceRate: <?= config('hero_experience_rate', 1) ?> ',
        heroLevelMax: <?= config('hero_level_max', 100) ?> ',
        heroAttributesPointsPerLevel: <?= config('hero_attributes_points_per_level', 5) ?> ',
        heroRevivalCostBase: <?= config('hero_revival_cost_base', 100) ?> ',
        heroRevivalCostMultiplier: <?= config('hero_revival_cost_multiplier', 1.5) ?> ',
        spySuccessRateBase: <?= config('spy_success_rate_base', 0.5) ?> ',
        spyDetectionRateBase: <?= config('spy_detection_rate_base', 0.3) ?> ',
        spyCostBase: <?= config('spy_cost_base', 100) ?> ',
        spyCostMultiplier: <?= config('spy_cost_multiplier', 1.5) ?> ',
        battleCalculationVersion: '<?= config('battle_calculation_version', '1') ?>',
        battleRoundsMax: <?= config('battle_rounds_max', 100) ?> ',
        battleFleeRate: <?= config('battle_flee_rate', 0.1) ?> ',
        battleMoraleEffect: <?= config('battle_morale_effect', 0.2) ?> ',
        battleTerrainEffect: <?= config('battle_terrain_effect', 0.1) ?> ',
        battleWeatherEffect: <?= config('battle_weather_effect', 0.05) ?> ',
        battleLuckFactor: <?= config('battle_luck_factor', 0.05) ?> ',
        battleExperienceRate: <?= config('battle_experience_rate', 1) ?> ',
        battleReportDetailLevel: '<?= config('battle_report_detail_level', 'full') ?>',
        troopUpkeepRate: <?= config('troop_upkeep_rate', 1) ?> ',
        troopUpkeepCavalry: <?= config('troop_upkeep_cavalry', 2) ?> ',
        troopUpkeepSiege: <?= config('troop_upkeep_siege', 3) ?> ',
        troopUpkeepHero: <?= config('troop_upkeep_hero', 5) ?> ',
        marketMerchantSpeed: <?= config('market_merchant_speed', 1) ?> ',
        marketTradeDurationBase: <?= config('market_trade_duration_base', 1) ?> ',
        marketTradeDurationDistance: <?= config('market_trade_duration_distance', 1) ?> ',
        marketFeeRate: <?= config('market_fee_rate', 0.1) ?> ',
        marketFeeMin: <?= config('market_fee_min', 1) ?> ',
        marketFeeMax: <?= config('market_fee_max', 10000) ?> ',
        allianceDiplomacyTypes: '<?= config('alliance_diplomacy_types', 'peace,war,confederation') ?>',
        allianceDiplomacyChangeCooldown: <?= config('alliance_diplomacy_change_cooldown', 24) ?> ',
        allianceInviteTimeout: <?= config('alliance_invite_timeout', 168) ?> ',
        allianceKickCooldown: <?= config('alliance_kick_cooldown', 24) ?> ',
        allianceLeaderTransferCooldown: <?= config('alliance_leader_transfer_cooldown', 168) ?> ',
        playerRankingUpdateInterval: <?= config('player_ranking_update_interval', 3600) ?> ',
        allianceRankingUpdateInterval: <?= config('alliance_ranking_update_interval', 3600) ?> ',
        villageRankingUpdateInterval: <?= config('village_ranking_update_interval', 3600) ?> ',
        resourceUpdateInterval: <?= config('resource_update_interval', 60) ?> ',
        populationUpdateInterval: <?= config('population_update_interval', 300) ?> ',
        constructionUpdateInterval: <?= config('construction_update_interval', 60) ?> ',
        troopTrainingUpdateInterval: <?= config('troop_training_update_interval', 60) ?> ',
        battleUpdateInterval: <?= config('battle_update_interval', 60) ?> ',
        marketUpdateInterval: <?= config('market_update_interval', 60) ?> ',
        messageCleanupInterval: <?= config('message_cleanup_interval', 3600) ?> ',
        reportCleanupInterval: <?= config('report_cleanup_interval', 3600) ?> ',
        sessionCleanupInterval: <?= config('session_cleanup_interval', 300) ?> ',
        cacheCleanupInterval: <?= config('cache_cleanup_interval', 3600) ?> ',
        statisticsUpdateInterval: <?= config('statistics_update_interval', 3600) ?> ',
        rankingUpdateInterval: <?= config('ranking_update_interval', 3600) ?> ',
        backupInterval: <?= config('backup_interval', 86400) ?> ',
        backupRetentionDays: <?= config('backup_retention_days', 7) ?> ',
        logLevel: '<?= config('log_level', 'info') ?>',
        debugMode: <?= config('debug_mode', false) ? 'true' : 'false' ?> ',
        performanceMonitoring: <?= config('performance_monitoring', false) ? 'true' : 'false' ?> ',
        errorReporting: <?= config('error_reporting', true) ? 'true' : 'false' ?> ',
        emailNotifications: <?= config('email_notifications', true) ? 'true' : 'false' ?> ',
        smsNotifications: <?= config('sms_notifications', false) ? 'true' : 'false' ?> ',
        pushNotifications: <?= config('push_notifications', false) ? 'true' : 'false' ?> ',
        apiEnabled: <?= config('api_enabled', true) ? 'true' : 'false' ?> ',
        apiRateLimit: <?= config('api_rate_limit', 100) ?> ',
        apiKeyRequired: <?= config('api_key_required', true) ? 'true' : 'false' ?> ',
        mobileVersion: <?= config('mobile_version', true) ? 'true' : 'false' ?> ',
        responsiveDesign: <?= config('responsive_design', true) ? 'true' : 'false' ?> ',
        darkMode: <?= config('dark_mode', false) ? 'true' : 'false' ?> ',
        languageSelection: <?= config('language_selection', true) ? 'true' : 'false' ?> ',
        timezoneSelection: <?= config('timezone_selection', true) ? 'true' : 'false' ?> ',
        currencySelection: <?= config('currency_selection', false) ? 'true' : 'false' ?> ',
        themeSelection: <?= config('theme_selection', true) ? 'true' : 'false' ?> ',
        soundEffects: <?= config('sound_effects', true) ? 'true' : 'false' ?> ',
        musicEnabled: <?= config('music_enabled', false) ? 'true' : 'false' ?> ',
        animationsEnabled: <?= config('animations_enabled', true) ? 'true' : 'false' ?> ',
        tooltipsEnabled: <?= config('tooltips_enabled', true) ? 'true' : 'false' ?> ',
        tutorialsEnabled: <?= config('tutorials_enabled', true) ? 'true' : 'false' ?> ',
        helpSystem: <?= config('help_system', true) ? 'true' : 'false' ?> ',
        faqEnabled: <?= config('faq_enabled', true) ? 'true' : 'false' ?> ',
        wikiEnabled: <?= config('wiki_enabled', true) ? 'true' : 'false' ?> ',
        forumEnabled: <?= config('forum_enabled', true) ? 'true' : 'false' ?> ',
        chatEnabled: <?= config('chat_enabled', true) ? 'true' : 'false' ?> ',
        privateMessaging: <?= config('private_messaging', true) ? 'true' : 'false' ?> ',
        publicMessaging: <?= config('public_messaging', true) ? 'true' : 'false' ?> ',
        allianceMessaging: <?= config('alliance_messaging', true) ? 'true' : 'false' ?> ',
        systemMessaging: <?= config('system_messaging', true) ? 'true' : 'false' ?> ',
        notificationSystem: <?= config('notification_system', true) ? 'true' : 'false' ?> ',
        alertSystem: <?= config('alert_system', true) ? 'true' : 'false' ?> ',
        warningSystem: <?= config('warning_system', true) ? 'true' : 'false' ?> ',
        errorSystem: <?= config('error_system', true) ? 'true' : 'false' ?> ',
        loggingSystem: <?= config('logging_system', true) ? 'true' : 'false' ?> ',
        auditSystem: <?= config('audit_system', true) ? 'true' : 'false' ?> ',
        monitoringSystem: <?= config('monitoring_system', true) ? 'true' : 'false' ?> ',
        analyticsSystem: <?= config('analytics_system', true) ? 'true' : 'false' ?> ',
        reportingSystem: <?= config('reporting_system', true) ? 'true' : 'false' ?> ',
        dashboardSystem: <?= config('dashboard_system', true) ? 'true' : 'false' ?> ',
        adminPanel: <?= config('admin_panel', true) ? 'true' : 'false' ?> ',
        moderatorPanel: <?= config('moderator_panel', true) ? 'true' : 'false' ?> ',
        playerPanel: <?= config('player_panel', true) ? 'true' : 'false' ?> ',
        guestPanel: <?= config('guest_panel', true) ? 'true' : 'false' ?> ',
        registrationSystem: <?= config('registration_system', true) ? 'true' : 'false' ?> ',
        loginSystem: <?= config('login_system', true) ? 'true' : 'false' ?> ',
        logoutSystem: <?= config('logout_system', true) ? 'true' : 'false' ?> ',
        passwordResetSystem: <?= config('password_reset_system', true) ? 'true' : 'false' ?> ',
        emailVerificationSystem: <?= config('email_verification_system', false) ? 'true' : 'false' ?> ',
        accountActivationSystem: <?= config('account_activation_system', false) ? 'true' : 'false' ?> ',
        accountDeletionSystem: <?= config('account_deletion_system', true) ? 'true' : 'false' ?> ',
        accountSuspensionSystem: <?= config('account_suspension_system', true) ? 'true' : 'false' ?> ',
        accountBanningSystem: <?= config('account_banning_system', true) ? 'true' : 'false' ?> ',
        accountMergingSystem: <?= config('account_merging_system', false) ? 'true' : 'false' ?> ',
        accountTransferSystem: <?= config('account_transfer_system', false) ? 'true' : 'false' ?> ',
        characterCreationSystem: <?= config('character_creation_system', true) ? 'true' : 'false' ?> ',
        characterDeletionSystem: <?= config('character_deletion_system', true) ? 'true' : 'false' ?> ',
        characterTransferSystem: <?= config('character_transfer_system', false) ? 'true' : 'false' ?> ',
        villageCreationSystem: <?= config('village_creation_system', true) ? 'true' : 'false' ?> ',
        villageDeletionSystem: <?= config('village_deletion_system', true) ? 'true' : 'false' ?> ',
        villageTransferSystem: <?= config('village_transfer_system', false) ? 'true' : 'false' ?> ',
        buildingConstructionSystem: <?= config('building_construction_system', true) ? 'true' : 'false' ?> ',
        buildingDestructionSystem: <?= config('building_destruction_system', true) ? 'true' : 'false' ?> ',
        buildingUpgradeSystem: <?= config('building_upgrade_system', true) ? 'true' : 'false' ?> ',
        buildingDowngradeSystem: <?= config('building_downgrade_system', true) ? 'true' : 'false' ?> ',
        troopTrainingSystem: <?= config('troop_training_system', true) ? 'true' : 'false' ?> ',
        troopDisbandSystem: <?= config('troop_disband_system', true) ? 'true' : 'false' ?> ',
        troopTransferSystem: <?= config('troop_transfer_system', true) ? 'true' : 'false' ?> ',
        battleSystem: <?= config('battle_system', true) ? 'true' : 'false' ?> ',
        raidSystem: <?= config('raid_system', true) ? 'true' : 'false' ?> ',
        siegeSystem: <?= config('siege_system', true) ? 'true' : 'false' ?> ',
        reinforcementSystem: <?= config('reinforcement_system', true) ? 'true' : 'false' ?> ',
        retreatSystem: <?= config('retreat_system', true) ? 'true' : 'false' ?> ',
        surrenderSystem: <?= config('surrender_system', true) ? 'true' : 'false' ?> ',
        tradingSystem: <?= config('trading_system', true) ? 'true' : 'false' ?> ',
        marketSystem: <?= config('market_system', true) ? 'true' : 'false' ?> ',
        auctionSystem: <?= config('auction_system', false) ? 'true' : 'false' ?> ',
        exchangeSystem: <?= config('exchange_system', false) ? 'true' : 'false' ?> ',
        bankingSystem: <?= config('banking_system', false) ? 'true' : 'false' ?> ',
        taxationSystem: <?= config('taxation_system', false) ? 'true' : 'false' ?> ',
        allianceSystem: <?= config('alliance_system', true) ? 'true' : 'false' ?> ',
        diplomacySystem: <?= config('diplomacy_system', true) ? 'true' : 'false' ?> ',
        warSystem: <?= config('war_system', true) ? 'true' : 'false' ?> ',
        peaceSystem: <?= config('peace_system', true) ? 'true' : 'false' ?> ',
        confederationSystem: <?= config('confederation_system', true) ? 'true' : 'false' ?> ',
        nonAggressionPactSystem: <?= config('non_aggression_pact_system', true) ? 'true' : 'false' ?> '
    };
</script>

<!-- Performance Monitoring (if enabled) -->
<?php if (config('performance_monitoring', false)): ?>
    <script type="text/javascript">
        // Performance monitoring
        window.addEventListener('load', function() {
            if (window.performance && window.performance.timing) {
                var loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                console.log('Page load time: ' + loadTime + 'ms');

                // Send to server if needed
                if (window.gameConfig && window.gameConfig.debugMode) {
                    fetch('/api/performance', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            loadTime: loadTime,
                            url: window.location.href,
                            timestamp: new Date().toISOString()
                        })
                    });
                }
            }
        });
    </script>
<?php endif; ?>

<!-- Error Reporting (if enabled) -->
<?php if (config('error_reporting', true)): ?>
    <script type="text/javascript">
        // Global error handler
        window.addEventListener('error', function(e) {
            if (window.gameConfig && window.gameConfig.debugMode) {
                console.error('JavaScript Error:', e.error);

                // Send to server if needed
                fetch('/api/js-error', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: e.message,
                        filename: e.filename,
                        lineno: e.lineno,
                        colno: e.colno,
                        stack: e.error ? e.error.stack : '',
                        url: window.location.href,
                        timestamp: new Date().toISOString()
                    })
                });
            }
        });
    </script>
<?php endif; ?>
</body>

</html>
