<?php
/*
Plugin Name: Код реферала в Google Tag Manager
Plugin URI:  https://github.com/ivannikitin-com/in-employee-reports
Description: Плагин реализует обработку и передачу в GTM реферальных ссылок и продаж по реферальным программам
Version:     1.0
Author:      IvanNikitin.com
Author URI:  https://ivannikitin.com/
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: in-ref-gtm
Domain Path: /lang
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* Глобальные константы плагина */
define( 'INRG', 		'in-ref-gtm' );					// Text Domain
define( 'INRG_FOLDER',	plugin_dir_path( __FILE__ ) );	// Plugin folder
define( 'INRG_URL',		plugin_dir_url( __FILE__ ) );	// Plugin URL

/* Классы плагина */
require( INRG_FOLDER . 'classes/inrg_plugin.php' );
require( INRG_FOLDER . 'classes/inrg_base_settings.php' );
require( INRG_FOLDER . 'classes/inrg_settings.php' );
require( INRG_FOLDER . 'classes/ingr_referral.php' );
require( INRG_FOLDER . 'classes/inrg-user.php' );

/* активация плагина */
register_activation_hook( __FILE__, 'inrg_activation' );
function inrg_activation()
{
	// Добавление роли
	INRG_User::addRole();
}


/* Инициализация плагина */
add_action( 'plugins_loaded', 'inrg_loaded' );
function inrg_loaded()
{
	// Локализация плагина
	load_plugin_textdomain( INRG, false, basename( dirname( __FILE__ ) ) . '/lang' );	
	
	// Загрузка плагина
	new INRG_Plugin( INRG_FOLDER, INRG_URL );
}
