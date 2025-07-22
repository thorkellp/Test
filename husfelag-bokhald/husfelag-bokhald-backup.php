<?php
/**
 * Plugin Name: Húsfélags Bókhald
 * Plugin URI: https://example.com/husfelag-bokhald
 * Description: Bókhaldsforrit fyrir lítil húsfélög - stjórnun tekna, gjalda og íbúðaskrár
 * Version: 1.0.0
 * Author: Húsfélags Bókhald Team
 * License: GPL v2 or later
 * Text Domain: husfelag-bokhald
 * Domain Path: /languages
 */

// Koma í veg fyrir beinan aðgang
if (!defined('ABSPATH')) {
    exit;
}

// Skilgreina fastar
define('HB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HB_VERSION', '1.0.0');

/**
 * Aðal klasi fyrir Húsfélags Bókhald
 */
class HusfelagBokhald {
    
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
    }
    
    /**
     * Virkja plugin
     */
    public function activate() {
        $this->create_tables();
        flush_rewrite_rules();
    }
    
    /**
     * Afvirkja plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Byrja plugin
     */
    public function init() {
        load_plugin_textdomain('husfelag-bokhald', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Búa til gagnagrunnstöflur
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tafla fyrir íbúðir
        $table_ibuddir = $wpdb->prefix . 'hb_ibuddir';
        $sql_ibuddir = "CREATE TABLE $table_ibuddir (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ibudanumer varchar(10) NOT NULL,
            eigandi varchar(100) NOT NULL,
            netfang varchar(100),
            simi varchar(20),
            fermetrar decimal(6,2) NOT NULL,
            hlutdeild decimal(5,4) NOT NULL,
            virkur tinyint(1) DEFAULT 1,
            stofnad datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ibudanumer (ibudanumer)
        ) $charset_collate;";
        
        // Tafla fyrir fjárhagsfærslur
        $table_faerslur = $wpdb->prefix . 'hb_faerslur';
        $sql_faerslur = "CREATE TABLE $table_faerslur (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            dagsetning date NOT NULL,
            lysing text NOT NULL,
            upphad decimal(10,2) NOT NULL,
            tegund enum('tekjur','gjold') NOT NULL,
            flokkur varchar(50) NOT NULL,
            ibudanumer varchar(10),
            kvittun varchar(255),
            stofnad datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY dagsetning (dagsetning),
            KEY tegund (tegund),
            KEY flokkur (flokkur)
        ) $charset_collate;";
        
        // Tafla fyrir mánaðargjöld
        $table_gjold = $wpdb->prefix . 'hb_manadargjold';
        $sql_gjold = "CREATE TABLE $table_gjold (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ar int(4) NOT NULL,
            manudur int(2) NOT NULL,
            ibudanumer varchar(10) NOT NULL,
            grunngjald decimal(8,2) NOT NULL,
            aukagjold decimal(8,2) DEFAULT 0,
            samtals decimal(8,2) NOT NULL,
            greitt tinyint(1) DEFAULT 0,
            greitt_dags date NULL,
            stofnad datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ar_manudur (ar, manudur),
            KEY ibudanumer (ibudanumer)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_ibuddir);
        dbDelta($sql_faerslur);
        dbDelta($sql_gjold);
    }
    
    /**
     * Bæta við admin menu
     */
    public function admin_menu() {
        add_menu_page(
            'Húsfélags Bókhald',
            'Húsfélags Bókhald',
            'manage_options',
            'husfelag-bokhald',
            array($this, 'admin_page_main'),
            'dashicons-calculator',
            30
        );
        
        add_submenu_page(
            'husfelag-bokhald',
            'Íbúðaskrá',
            'Íbúðaskrá',
            'manage_options',
            'husfelag-ibudaskra',
            array($this, 'admin_page_ibudaskra')
        );
        
        add_submenu_page(
            'husfelag-bokhald',
            'Fjárhagsfærslur',
            'Fjárhagsfærslur',
            'manage_options',
            'husfelag-faerslur',
            array($this, 'admin_page_faerslur')
        );
        
        add_submenu_page(
            'husfelag-bokhald',
            'Mánaðargjöld',
            'Mánaðargjöld',
            'manage_options',
            'husfelag-gjold',
            array($this, 'admin_page_gjold')
        );
        
        add_submenu_page(
            'husfelag-bokhald',
            'Skýrslur',
            'Skýrslur',
            'manage_options',
            'husfelag-skyrslur',
            array($this, 'admin_page_skyrslur')
        );
    }
    
    /**
     * Hlaða admin scripts
     */
    public function admin_scripts($hook) {
        if (strpos($hook, 'husfelag') !== false) {
            wp_enqueue_script('husfelag-admin', HB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), HB_VERSION, true);
            wp_enqueue_style('husfelag-admin', HB_PLUGIN_URL . 'assets/css/admin.css', array(), HB_VERSION);
            
            wp_localize_script('husfelag-admin', 'husfelag_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('husfelag_nonce')
            ));
        }
    }
    
    /**
     * Frontend scripts
     */
    public function frontend_scripts() {
        wp_enqueue_style('husfelag-frontend', HB_PLUGIN_URL . 'assets/css/frontend.css', array(), HB_VERSION);
    }
    
    /**
     * Aðal admin síða
     */
    public function admin_page_main() {
        include HB_PLUGIN_PATH . 'includes/admin-main.php';
    }
    
    /**
     * Íbúðaskrá síða
     */
    public function admin_page_ibudaskra() {
        include HB_PLUGIN_PATH . 'includes/admin-ibudaskra.php';
    }
    
    /**
     * Fjárhagsfærslur síða
     */
    public function admin_page_faerslur() {
        include HB_PLUGIN_PATH . 'includes/admin-faerslur.php';
    }
    
    /**
     * Mánaðargjöld síða
     */
    public function admin_page_gjold() {
        include HB_PLUGIN_PATH . 'includes/admin-gjold.php';
    }
    
    /**
     * Skýrslur síða
     */
    public function admin_page_skyrslur() {
        include HB_PLUGIN_PATH . 'includes/admin-skyrslur.php';
    }
}

// Byrja plugin
new HusfelagBokhald();
