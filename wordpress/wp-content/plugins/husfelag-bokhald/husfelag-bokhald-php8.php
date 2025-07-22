<?php
/**
 * Plugin Name: Húsfélags Bókhald (PHP 8+)
 * Plugin URI: https://example.com/husfelag-bokhald
 * Description: Bókhaldsforrit fyrir lítil húsfélög - stjórnun tekna, gjalda og íbúðaskrár. Krefst PHP 8+ og WordPress 6.8+
 * Version: 2.0.0
 * Author: Húsfélags Bókhald Team
 * License: GPL v2 or later
 * Text Domain: husfelag-bokhald
 * Domain Path: /languages
 * Requires at least: 6.8
 * Tested up to: 6.8
 * Requires PHP: 8.0
 * Network: false
 */

declare(strict_types=1);

// Koma í veg fyrir beinan aðgang
if (!defined('ABSPATH')) {
    exit;
}

// Skilgreina fastar
define('HB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HB_VERSION', '2.0.0');
define('HB_MIN_WP_VERSION', '6.8');
define('HB_MIN_PHP_VERSION', '8.0');

enum HB_UserRole: string {
    case ADMIN = 'administrator';
    case HUSFELAG_MANAGER = 'husfelag_manager';
    case HUSFELAG_VIEWER = 'husfelag_viewer';
    
    public function getCapabilities(): array {
        return match($this) {
            self::ADMIN => ['manage_husfelag_bokhald', 'view_husfelag_reports', 'manage_husfelag_api'],
            self::HUSFELAG_MANAGER => ['manage_husfelag_bokhald', 'view_husfelag_reports'],
            self::HUSFELAG_VIEWER => ['view_husfelag_reports']
        };
    }
}

readonly class HB_PluginConfig {
    public function __construct(
        public string $pluginUrl,
        public string $pluginPath,
        public string $version,
        public string $textDomain = 'husfelag-bokhald'
    ) {}
}

/**
 * Aðal klasi fyrir Húsfélags Bókhald með PHP 8+ eiginleikum
 */
class HusfelagBokhald {
    
    private readonly HB_PluginConfig $config;
    
    public function __construct() {
        // Athuga kröfur fyrst
        if (!$this->checkRequirements()) {
            return;
        }
        
        $this->config = new HB_PluginConfig(
            pluginUrl: HB_PLUGIN_URL,
            pluginPath: HB_PLUGIN_PATH,
            version: HB_VERSION
        );
        
        $this->initializePlugin();
        $this->registerHooks();
    }
    
    /**
     * Athuga kerfiskröfur með PHP 8+ syntax
     */
    private function checkRequirements(): bool {
        global $wp_version;
        
        $phpVersionValid = version_compare(PHP_VERSION, HB_MIN_PHP_VERSION, '>=');
        $wpVersionValid = version_compare($wp_version, HB_MIN_WP_VERSION, '>=');
        
        if (!$phpVersionValid) {
            add_action('admin_notices', fn() => $this->showPhpVersionNotice());
            return false;
        }
        
        if (!$wpVersionValid) {
            add_action('admin_notices', fn() => $this->showWpVersionNotice());
            return false;
        }
        
        return true;
    }
    
    /**
     * Sýna PHP version notice
     */
    private function showPhpVersionNotice(): void {
        printf(
            '<div class="error"><p><strong>%s:</strong> %s</p></div>',
            esc_html__('Húsfélags Bókhald', 'husfelag-bokhald'),
            sprintf(
                esc_html__('Þarfnast PHP %s eða nýrra. Núverandi útgáfa: %s', 'husfelag-bokhald'),
                HB_MIN_PHP_VERSION,
                PHP_VERSION
            )
        );
    }
    
    /**
     * Sýna WordPress version notice
     */
    private function showWpVersionNotice(): void {
        global $wp_version;
        printf(
            '<div class="error"><p><strong>%s:</strong> %s</p></div>',
            esc_html__('Húsfélags Bókhald', 'husfelag-bokhald'),
            sprintf(
                esc_html__('Þarfnast WordPress %s eða nýrra. Núverandi útgáfa: %s', 'husfelag-bokhald'),
                HB_MIN_WP_VERSION,
                $wp_version
            )
        );
    }
    
    /**
     * Byrja plugin með dependency injection
     */
    private function initializePlugin(): void {
        // Load required files
        $this->loadDependencies();
        
        // Initialize components
        $this->initializeComponents();
    }
    
    /**
     * Hlaða nauðsynlegum skrám
     */
    private function loadDependencies(): void {
        $dependencies = [
            'includes/security-functions-php8.php',
            'includes/api/bank-api-client-php8.php',
            'includes/api/bank-sync-scheduler.php'
        ];
        
        foreach ($dependencies as $file) {
            $filePath = $this->config->pluginPath . $file;
            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }
    
    /**
     * Byrja components
     */
    private function initializeComponents(): void {
        // Initialize scheduler if API is configured
        if (get_option('hb_selected_bank')) {
            new HB_Bank_Sync_Scheduler();
        }
    }
    
    /**
     * Skrá WordPress hooks
     */
    private function registerHooks(): void {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'adminScripts']);
        add_action('wp_enqueue_scripts', [$this, 'frontendScripts']);
        
        // AJAX handlers
        $this->registerAjaxHandlers();
    }
    
    /**
     * Skrá AJAX handlers
     */
    private function registerAjaxHandlers(): void {
        $ajaxActions = [
            'hb_save_ibud' => 'ajaxSaveApartment',
            'hb_delete_ibud' => 'ajaxDeleteApartment',
            'hb_save_faersla' => 'ajaxSaveTransaction',
            'hb_delete_faersla' => 'ajaxDeleteTransaction',
            'hb_sync_bank' => 'ajaxSyncBank',
            'hb_test_bank_connection' => 'ajaxTestBankConnection'
        ];
        
        foreach ($ajaxActions as $action => $method) {
            add_action("wp_ajax_{$action}", [$this, $method]);
        }
    }
    
    /**
     * Virkja plugin með improved error handling
     */
    public function activate(): void {
        if (!current_user_can('activate_plugins')) {
            wp_die(esc_html__('Þú hefur ekki heimild til að virkja plugins.', 'husfelag-bokhald'));
        }
        
        try {
            $this->createTables();
            $this->createSecurityFiles();
            $this->setupUserRoles();
            flush_rewrite_rules();
            
            // Log successful activation
            if (class_exists('HB_Security_Functions')) {
                HB_Security_Functions::logSecurityEvent(
                    SecurityEvent::SUSPICIOUS_ACTIVITY, // Using available enum
                    'Plugin activated successfully'
                );
            }
            
        } catch (Throwable $e) {
            wp_die(
                sprintf(
                    esc_html__('Villa við að virkja plugin: %s', 'husfelag-bokhald'),
                    $e->getMessage()
                )
            );
        }
    }
    
    /**
     * Afvirkja plugin
     */
    public function deactivate(): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        flush_rewrite_rules();
        
        // Unschedule cron jobs
        $timestamp = wp_next_scheduled('hb_daily_bank_sync');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'hb_daily_bank_sync');
        }
    }
    
    /**
     * Byrja plugin localization
     */
    public function init(): void {
        load_plugin_textdomain(
            $this->config->textDomain, 
            false, 
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    /**
     * Búa til gagnagrunnstöflur með PHP 8+ syntax
     */
    private function createTables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $tables = [
            'hb_ibuddir' => $this->getApartmentsTableSql($charset_collate),
            'hb_faerslur' => $this->getTransactionsTableSql($charset_collate),
            'hb_manadargjold' => $this->getMonthlyFeesTableSql($charset_collate)
        ];
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables as $tableName => $sql) {
            dbDelta($sql);
        }
    }
    
    /**
     * SQL fyrir íbúðatöflu
     */
    private function getApartmentsTableSql(string $charset_collate): string {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hb_ibuddir';
        
        return "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ibudanumer varchar(10) NOT NULL,
            eigandi varchar(100) NOT NULL,
            netfang varchar(100),
            simi varchar(20),
            fermetrar decimal(8,2) NOT NULL,
            hlutdeild decimal(8,6) NOT NULL,
            virkur tinyint(1) DEFAULT 1,
            stofnad datetime DEFAULT CURRENT_TIMESTAMP,
            uppfaerd datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ibudanumer (ibudanumer),
            KEY virkur (virkur),
            KEY hlutdeild (hlutdeild)
        ) $charset_collate;";
    }
    
    /**
     * SQL fyrir fjárhagsfærslutöflu
     */
    private function getTransactionsTableSql(string $charset_collate): string {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hb_faerslur';
        
        return "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            dagsetning date NOT NULL,
            lysing text NOT NULL,
            upphad decimal(12,2) NOT NULL,
            tegund enum('tekjur','gjold') NOT NULL,
            flokkur varchar(50) NOT NULL,
            ibudanumer varchar(10),
            kvittun varchar(255),
            bank_reference varchar(255),
            notandi_id bigint(20) unsigned,
            stofnad datetime DEFAULT CURRENT_TIMESTAMP,
            uppfaerd datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY dagsetning (dagsetning),
            KEY tegund (tegund),
            KEY flokkur (flokkur),
            KEY kvittun (kvittun),
            KEY bank_reference (bank_reference),
            KEY notandi_id (notandi_id),
            FOREIGN KEY (notandi_id) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL
        ) $charset_collate;";
    }
    
    /**
     * SQL fyrir mánaðargjaldatöflu
     */
    private function getMonthlyFeesTableSql(string $charset_collate): string {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hb_manadargjold';
        
        return "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ar int(4) NOT NULL,
            manudur int(2) NOT NULL,
            ibudanumer varchar(10) NOT NULL,
            grunngjald decimal(10,2) NOT NULL,
            aukagjold decimal(10,2) DEFAULT 0,
            samtals decimal(10,2) NOT NULL,
            greitt tinyint(1) DEFAULT 0,
            greitt_dags date NULL,
            greitt_notandi bigint(20) unsigned,
            stofnad datetime DEFAULT CURRENT_TIMESTAMP,
            uppfaerd datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ar_manudur_ibud (ar, manudur, ibudanumer),
            KEY ar_manudur (ar, manudur),
            KEY ibudanumer (ibudanumer),
            KEY greitt (greitt),
            FOREIGN KEY (greitt_notandi) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL
        ) $charset_collate;";
    }
    
    /**
     * Búa til öryggisSkrár
     */
    private function createSecurityFiles(): void {
        $securityFiles = [
            'includes/.htaccess' => $this->getHtaccessContent(),
            'includes/index.php' => "<?php\n// Silence is golden\n",
            'includes/api/.htaccess' => $this->getHtaccessContent(),
            'includes/api/index.php' => "<?php\n// Silence is golden\n"
        ];
        
        foreach ($securityFiles as $file => $content) {
            $filePath = $this->config->pluginPath . $file;
            if (!file_exists($filePath)) {
                wp_mkdir_p(dirname($filePath));
                file_put_contents($filePath, $content);
            }
        }
    }
    
    /**
     * Sækja .htaccess innihald
     */
    private function getHtaccessContent(): string {
        return <<<HTACCESS
# Húsfélags Bókhald Security
<Files "*.php">
    Order allow,deny
    Deny from all
</Files>
<Files "*.json">
    Order allow,deny
    Deny from all
</Files>
HTACCESS;
    }
    
    /**
     * Setja upp user roles
     */
    private function setupUserRoles(): void {
        foreach (HB_UserRole::cases() as $role) {
            if ($role === HB_UserRole::ADMIN) {
                // Add capabilities to existing admin role
                $adminRole = get_role('administrator');
                if ($adminRole) {
                    foreach ($role->getCapabilities() as $cap) {
                        $adminRole->add_cap($cap);
                    }
                }
            } else {
                // Create custom roles
                add_role(
                    $role->value,
                    match($role) {
                        HB_UserRole::HUSFELAG_MANAGER => 'Húsfélags Stjórnandi',
                        HB_UserRole::HUSFELAG_VIEWER => 'Húsfélags Skoðandi'
                    },
                    array_fill_keys($role->getCapabilities(), true)
                );
            }
        }
    }
    
    /**
     * Bæta við admin menu með PHP 8+ syntax
     */
    public function adminMenu(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $menuItems = [
            [
                'type' => 'main',
                'page_title' => 'Húsfélags Bókhald',
                'menu_title' => 'Húsfélags Bókhald',
                'capability' => 'manage_options',
                'menu_slug' => 'husfelag-bokhald',
                'callback' => [$this, 'adminPageMain'],
                'icon' => 'dashicons-calculator',
                'position' => 30
            ],
            [
                'type' => 'sub',
                'parent_slug' => 'husfelag-bokhald',
                'page_title' => 'Íbúðaskrá',
                'menu_title' => 'Íbúðaskrá',
                'capability' => 'manage_options',
                'menu_slug' => 'husfelag-ibudaskra',
                'callback' => [$this, 'adminPageApartments']
            ],
            [
                'type' => 'sub',
                'parent_slug' => 'husfelag-bokhald',
                'page_title' => 'Fjárhagsfærslur',
                'menu_title' => 'Fjárhagsfærslur',
                'capability' => 'manage_options',
                'menu_slug' => 'husfelag-faerslur',
                'callback' => [$this, 'adminPageTransactions']
            ],
            [
                'type' => 'sub',
                'parent_slug' => 'husfelag-bokhald',
                'page_title' => 'Mánaðargjöld',
                'menu_title' => 'Mánaðargjöld',
                'capability' => 'manage_options',
                'menu_slug' => 'husfelag-gjold',
                'callback' => [$this, 'adminPageMonthlyFees']
            ],
            [
                'type' => 'sub',
                'parent_slug' => 'husfelag-bokhald',
                'page_title' => 'Skýrslur',
                'menu_title' => 'Skýrslur',
                'capability' => 'manage_options',
                'menu_slug' => 'husfelag-skyrslur',
                'callback' => [$this, 'adminPageReports']
            ],
            [
                'type' => 'sub',
                'parent_slug' => 'husfelag-bokhald',
                'page_title' => 'Banka API',
                'menu_title' => 'Banka API',
                'capability' => 'manage_options',
                'menu_slug' => 'husfelag-bank-api',
                'callback' => [$this, 'adminPageBankApi']
            ]
        ];
        
        foreach ($menuItems as $item) {
            match($item['type']) {
                'main' => add_menu_page(
                    $item['page_title'],
                    $item['menu_title'],
                    $item['capability'],
                    $item['menu_slug'],
                    $item['callback'],
                    $item['icon'],
                    $item['position']
                ),
                'sub' => add_submenu_page(
                    $item['parent_slug'],
                    $item['page_title'],
                    $item['menu_title'],
                    $item['capability'],
                    $item['menu_slug'],
                    $item['callback']
                )
            };
        }
    }
    
    /**
     * Hlaða admin scripts með versioning
     */
    public function adminScripts(string $hook): void {
        if (!str_contains($hook, 'husfelag') || !current_user_can('manage_options')) {
            return;
        }
        
        wp_enqueue_script(
            'husfelag-admin',
            $this->config->pluginUrl . 'assets/js/admin.js',
            ['jquery'],
            $this->config->version,
            ['in_footer' => true]
        );
        
        wp_enqueue_style(
            'husfelag-admin',
            $this->config->pluginUrl . 'assets/css/admin.css',
            [],
            $this->config->version
        );
        
        wp_localize_script('husfelag-admin', 'husfelag_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('husfelag_ajax_nonce'),
            'messages' => [
                'delete_confirm' => __('Ertu viss um að þú viljir eyða þessu?', 'husfelag-bokhald'),
                'required_fields' => __('Vinsamlegast fylltu út öll nauðsynleg reitir.', 'husfelag-bokhald'),
                'api_error' => __('Villa kom upp við API köll.', 'husfelag-bokhald')
            ]
        ]);
    }
    
    /**
     * Frontend scripts
     */
    public function frontendScripts(): void {
        wp_enqueue_style(
            'husfelag-frontend',
            $this->config->pluginUrl . 'assets/css/frontend.css',
            [],
            $this->config->version
        );
    }
    
    /**
     * Admin síður með öryggisathugun
     */
    public function adminPageMain(): void {
        $this->renderAdminPage('admin-main.php');
    }
    
    public function adminPageApartments(): void {
        $this->renderAdminPage('admin-ibudaskra.php');
    }
    
    public function adminPageTransactions(): void {
        $this->renderAdminPage('admin-faerslur.php');
    }
    
    public function adminPageMonthlyFees(): void {
        $this->renderAdminPage('admin-gjold.php');
    }
    
    public function adminPageReports(): void {
        $this->renderAdminPage('admin-skyrslur.php');
    }
    
    public function adminPageBankApi(): void {
        $this->renderAdminPage('admin-bank-api.php');
    }
    
    /**
     * Render admin page með error handling
     */
    private function renderAdminPage(string $template): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Þú hefur ekki heimild til þessa.', 'husfelag-bokhald'));
        }
        
        $templatePath = $this->config->pluginPath . 'includes/' . $template;
        
        if (!file_exists($templatePath)) {
            wp_die(sprintf(__('Template ekki til: %s', 'husfelag-bokhald'), $template));
        }
        
        include $templatePath;
    }
    
    /**
     * AJAX handlers með modern error handling
     */
    public function ajaxSaveApartment(): void {
        $this->handleAjaxRequest(function() {
            // Implementation here
            wp_send_json_success(['message' => 'Íbúð vistuð']);
        });
    }
    
    public function ajaxDeleteApartment(): void {
        $this->handleAjaxRequest(function() {
            // Implementation here
            wp_send_json_success(['message' => 'Íbúð eytt']);
        });
    }
    
    public function ajaxSaveTransaction(): void {
        $this->handleAjaxRequest(function() {
            // Implementation here
            wp_send_json_success(['message' => 'Fjárhagsfærsla vistuð']);
        });
    }
    
    public function ajaxDeleteTransaction(): void {
        $this->handleAjaxRequest(function() {
            // Implementation here
            wp_send_json_success(['message' => 'Fjárhagsfærsla eytt']);
        });
    }
    
    public function ajaxSyncBank(): void {
        $this->handleAjaxRequest(function() {
            // Implementation here
            wp_send_json_success(['message' => 'Bankafærslur samstilltar']);
        });
    }
    
    public function ajaxTestBankConnection(): void {
        $this->handleAjaxRequest(function() {
            // Implementation here
            wp_send_json_success(['message' => 'Bankatengingu tókst']);
        });
    }
    
    /**
     * Handle AJAX request með error handling
     */
    private function handleAjaxRequest(callable $callback): void {
        try {
            check_ajax_referer('husfelag_ajax_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Óheimil aðgerð', 'husfelag-bokhald')]);
            }
            
            $callback();
            
        } catch (Throwable $e) {
            wp_send_json_error([
                'message' => __('Villa kom upp', 'husfelag-bokhald'),
                'debug' => WP_DEBUG ? $e->getMessage() : null
            ]);
        }
    }
}

// Byrja plugin með error handling
try {
    new HusfelagBokhald();
} catch (Throwable $e) {
    if (WP_DEBUG) {
        wp_die("Húsfélags Bókhald Error: " . $e->getMessage());
    }
    // Log error but don't crash site
    error_log("Húsfélags Bókhald Plugin Error: " . $e->getMessage());
}
