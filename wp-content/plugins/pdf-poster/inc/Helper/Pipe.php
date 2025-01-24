<?php
namespace PDFPro\Helper;

class Pipe{
    public static function isPipe(){
        global $pdfp_bs;
        pdfp_fs()->can_use_premium_code();
    }

    public static function wasPipe(){
        $pdfp = \get_option('pdfp', false);
        
        if(!$pdfp){
            return false;
        }

        return true;
    }

}