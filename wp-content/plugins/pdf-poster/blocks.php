<?php
namespace PDFPro\Block;
if(!defined('ABSPATH')) {
    return;
}

use PDFPro\Helper\Pipe;
use PDFPro\Helper\Functions as Utils;
use PDFPro\Services\PDFTemplate;


class RegisterBlock{
    protected static $_instance = null;

    function __construct(){
        add_action('init', [$this, 'enqueue_script']);
        add_action('wp_ajax_block_validator', [$this, 'block_validator']);
    }

     /**
     * Create Instance
     */
    public static function instance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function enqueue_script(){
        // wp_register_script(	'pdfp-editor', PDFPRO_PLUGIN_DIR.'build/editor.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'jquery'  ), PDFPRO_VER, true );

        wp_register_style( 'pdfp-editor', PDFPRO_PLUGIN_DIR. 'build/editor.css' , array(), PDFPRO_VER );

        register_block_type(PDFPRO_PATH.'build/blocks/pdf-poster');

        register_block_type(PDFPRO_PATH.'build/blocks/selector');

        global $pdfp_bs;

        $option = get_option('fpdf_option', []);

        wp_localize_script('pdfp-pdfposter-editor-script', 'pdfp', [
            'siteUrl' => home_url(),
            'pipe' => pdfp_fs()->can_use_premium_code(),
            'placeholder' => PDFPRO_PLUGIN_DIR.'img/placeholder.pdf',
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_ajax'),
            'adobeClientKey' => Utils::scramble('encode', Utils::isset($option, 'adobe_client_key', '')),
            'dir' => PDFPRO_PLUGIN_DIR,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'gAppId' => Utils::isset($option, 'google_project_number'),
            'gClientId' => Utils::isset($option, 'google_client_id'),
            'gDeveloperKey' => Utils::isset($option, 'google_apikey'),
            'isPipe' => pdfp_fs()->can_use_premium_code()
        ]);

        load_plugin_textdomain( 'pdfp', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n' );
        wp_set_script_translations( 'pdfp-editor', 'pdfp', plugin_dir_path( __FILE__ ) . '/i18n' );
    }


    function block_validator(){
        // if()
    }


}

RegisterBlock::instance();

