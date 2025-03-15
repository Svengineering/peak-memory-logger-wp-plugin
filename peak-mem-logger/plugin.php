<?php

 /**
 * Plugin Name: Peak Memory Logger
 * Plugin URI: 
 * Description: logs peak memory usage for every request
 * Version: 1.0
 * Requires at least: 6.7
 * Requires PHP: 8.0
 * Author: Sven Volkmann
 * Author URI: viaclara.io
 */

namespace PkMemLogger;

defined( 'ABSPATH' ) || exit;

const PLUGIN_NAME = 'Peak Memory Logger';
const LOG_FILE_DEF = 'php_peak_mem.log';

//starting the plugin
add_action('plugins_loaded', 'PkMemLogger\start');



function start() {
    
    add_action('shutdown', 'PkMemLogger\shutdown_action');
    add_action('admin_init', 'PkMemLogger\settings_api_init');
    add_action('admin_menu', 'PkMemLogger\admin_menu_cb');
    
    //build file path for log file
    $file_or_path = get_option('pkmemlogger_log_file');
    
    //default file name
    if(!$file_or_path) {
    	$file_or_path = LOG_FILE_DEF;
    }
    
    //full file path?
    $maybe_dir = dirname($file_or_path);
    if( is_dir($maybe_dir) && is_writeable($maybe_dir) ) {
    
    	define('PKMEMLOGGER_LOG_FILE', $file_or_path);
    	
    } else {
    //only file name -> save in uploads
    
        $upload_dir_info = wp_upload_dir();
	    $upload_dir = $upload_dir_info['basedir']; //without trailing slash	
	    $log_file = $upload_dir . '/' . $file_or_path;
	    define('PKMEMLOGGER_LOG_FILE', $log_file);
	
    }
    
}


function settings_api_init() {

    add_settings_section( 'section-id', 'Peak Memory Logger: Konfiguration', '', 'peak-memory-logger' );

    add_settings_field(
       'pkmemlogger_log_file-field', 
       'Logdatei: Dateiname oder Dateipfad',  
       'PkMemLogger\settings_field_log_file', 
       'peak-memory-logger', 
       'section-id');
   
    register_setting('option-group', 'pkmemlogger_log_file');		

}


function settings_field_log_file() {
    $val = get_option('pkmemlogger_log_file'); ?>
    <div>
        <input style="min-width:30rem" type='text' id='pkmemlogger_log_file' name='pkmemlogger_log_file' value='<?php esc_attr_e($val); ?>' placeholder='<?php esc_attr_e(LOG_FILE_DEF)?>' />
        <p>
        Hinweis: Wird nur ein Dateinname angegeben, wird die Log-Datei im Uploads-Verzeichnis gespeichert. <br>
        Ansonsten muss ein vollständiger nicht-schreibgeschützter Dateipfad angegeben werden.
        </p>
    </div>

    <?php
}


function admin_menu_cb() {

    add_options_page( 
        PLUGIN_NAME . ' - Konfiguration', //html page title
        PLUGIN_NAME,  //menu title
        'manage_options', 
        'peak-memory-logger', //page slug or admin url as a link i.e. 'edit-tags.php?taxonomy=proficiency_level'
        'PkMemLogger\admin_page'
    );

}

function admin_page() {
    ?>
    <div class="wrap">

        <form action="options.php" method="post">
            <p class="submit">
                <?php
                settings_fields('option-group');

                do_settings_sections('peak-memory-logger');

                submit_button("Speichern", 'secondary'); 
                ?>
            </p>
        </form>
    </div><!-- wrap -->
    <?php 
}


function shutdown_action() {
    $request = $_SERVER['REQUEST_URI'];

    //append POST data when admin ajax request has no get variables...
    if($request == '/wp-admin/admin-ajax.php') {
        $request .= " " . json_encode($_POST);
    }

    $msg = "[mem peak usage] " . (int)(memory_get_peak_usage()/(1024*1024)) . "MB (" . (int)(memory_get_peak_usage(true)/(1024*1024)) . "MB) " . $request;
    
    file_put_contents(PKMEMLOGGER_LOG_FILE, current_time('c') . " " . $msg. "\n", FILE_APPEND);
}


