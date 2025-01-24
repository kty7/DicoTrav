<?php 
namespace PDFPro\Helper;

class Functions{

    protected static $meta = null;

    public static function i($array, $key1, $key2 = '', $default = false){
        if(isset($array[$key1][$key2])){
            return $array[$key1][$key2];
        }else if (isset($array[$key1])){
            return $array[$key1];
        }
        return $default;
    }

    public static function isset($array, $key1, $default = false){
        if (isset($array[$key1])){
            return $array[$key1];
        }
        return $default;
    }

    public static function meta($id, $key, $default = null, $true = false){
        $meta = metadata_exists( 'post', $id, '_fpdf' ) ? get_post_meta($id, '_fpdf', true) : '';
        if(isset($meta[$key]) && $meta != ''){
            if($true == true){
                if($meta[$key] == '1'){
                    return true;
                }else if($meta[$key] == '0'){
                    return false;
                }
            }else {
                return $meta[$key];
            }
        }else {
            return $default;
        }
    }

    /**
       * scrambel data ( password and video file if it is protected)
       */
    public static function scramble($do = 'encode', $data = ''){
        $originalKey = 'abcdefghijklmnopqrstuvwxyz1234567890';
		$key = 'z1ntg4ihmwj5cr09byx8spl7ak6vo2q3eduf';
		$resultData = '';
		if($do == 'encode'){
			if($data != ''){
				$length = strlen($data);
				for($i = 0; $i < $length; $i++){
					$position = strpos($originalKey, $data[$i]);
					if($position !== false){
						$resultData .= $key[$position];
					}else {
						$resultData .= $data[$i];
					}
				}
			}
		}

		if($do == 'decode'){
			if($data != ''){
				$length = strlen($data);
				for($i = 0; $i < $length; $i++){
					$position = strpos($key, $data[$i]);
					if($position !== false){
						$resultData .= $originalKey[$position];
					}else {
						$resultData .= $data[$i];
					}
				}
			}
		}

		return $resultData;
    }

    /**
     * Detect Browser
     */
    public static function getBrowser() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = "N/A";
        $browsers = array(
        '/msie/i' => 'Internet explorer',
        '/firefox/i' => 'Firefox',
        '/safari/i' => 'Safari',
        '/chrome/i' => 'Chrome',
        '/edge/i' => 'Edge',
        '/Edg/i' => 'Edge',
        '/opera/i' => 'Opera',
        '/mobile/i' => 'Mobile browser'
        );
        
        foreach ($browsers as $regex => $value) {
            if (preg_match($regex, $user_agent)) { $browser = $value; }
        }
        
        return $browser;
    }

    public static function generate_pdf_poster_block($id){

        $post_meta = get_post_meta($id,"_fpdf", true);
    
        $height = self::isset($post_meta, 'height', ['height' => 1122, 'unit' => 'px']);
        $width = self::isset($post_meta, 'width', ['width' => 100, 'unit' => '%']);
        $popupBtnPadding = self::isset($post_meta, 'popup_btn_padding', [ "top"=> 10, "right"=> 20, "bottom"=> 10, "left"=> 10 ]);
    
        return [
          "blockName" => "pdfp/pdfposter",
          "attrs" => [
            'uniqueId' => wp_unique_id( 'pdfp' ),
            'file' => self::isset($post_meta, 'source', ''),
             'height' => $height['height'].$height['unit'],
             'width' => $width['width'].$width['unit'],
             'print' => self::isset($post_meta,  'print', false) === '1',
             'fullscreenButton' => self::isset($post_meta,  'view_fullscreen_btn', '1') === '1',
             'fullscreenButtonText' => self::isset($post_meta,  'fullscreen_btn_text', 'View Fullscreen'),
             'newWindow' => self::isset($post_meta,  'view_fullscreen_btn_target_blank', false) === '1',
             'showName' => self::isset($post_meta,  'show_filename', '1') === '1',
             'downloadButton' => self::isset($post_meta,  'show_download_btn', false) === '1',
             'downloadButtonText' => self::isset($post_meta,  'download_btn_text', 'Download File'),
             'protect' => self::isset($post_meta,  'protect', false) === '1',
             'onlyPDF' => self::isset($post_meta,  'only_pdf', false) === '1',
             'defaultBrowser' => self::isset($post_meta,  'default_browser', false) === '1',
             'thumbMenu' => self::isset($post_meta,  'thumbnail_toggle_menu', false) === '1',
             'initialPage' => self::isset($post_meta,  'jump_to', 0),
             'sidebarOpen' => self::isset($post_meta,  'sidebar_open', false) === '1',
             'lastVersion' => self::isset($post_meta,  'ppv_load_last_version', false) === '1',
             'hrScroll' => self::isset($post_meta,  'hr_scroll', 0) === '1',
             'zoomLevel' => self::isset($post_meta,  'zoomLevel', null),
             'alert' => self::isset($post_meta,  'disable_alert', true) !== '1',
             "popupOptions" => [
                "enabled" =>  self::isset($post_meta,  'popup', 0) === '1',
                "text" =>  self::isset($post_meta,  'popup_btn_text', 'Open PDF'),
                "btnStyle" =>  [
                    "background" =>   self::isset($post_meta,  'popup_btn_bg', '#1e73be'),
                    "color" =>   self::isset($post_meta,  'popup_btn_color', '#fff'),
                    "fontSize" =>   self::isset($post_meta,  'popup_btn_font_size', null).'rem',
                    "padding" =>  $popupBtnPadding
                ]
            ],
          ]
        ];
      }
}