<?php
namespace PDFPro\Helper;

class Plugin{

    public static $version = PDFPRO_VER;
    public static $latestVersion = null;

    public static function dir(){
        return plugin_dir_url(__FILE__);
    }

    public static function path(){
        return plugin_dir_path(__FILE__);
    }

    public static function version(){
        return self::$version;
    }

   
}