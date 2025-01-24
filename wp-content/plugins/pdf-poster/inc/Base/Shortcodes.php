<?php

namespace PDFPro\Base;

use PDFPro\Helper\Functions as Utils;

class Shortcodes{

  public function register(){
    add_shortcode('pdf', [$this, 'pdf'], 10, 2);
    add_shortcode('raw_pdf', [$this, 'raw_pdf']);
    add_shortcode('pdf_embed', [$this, 'pdf_embed']);
  }

  public function pdf($atts, $content){
    extract(shortcode_atts(array(
      'id' => null,
    ), $atts));
    $post_type = get_post_type($id);
    $pluginUpdated = 1630223686;
    $publishDate = get_the_date('U', $id);
    $isGutenberg = get_post_meta($id, 'isGutenberg', true);
    $post = get_post($id);
    
    ob_start();
    
    if($post_type !== 'pdfposter'){
      return false;
    }

    if($pluginUpdated < $publishDate && $post->post_content != '' || $isGutenberg){
        $content = $post->post_content ?? ' ';
        $blocks = parse_blocks($content);
        return render_block($blocks[0]);
    }else {
      $block = Utils::generate_pdf_poster_block($id);      
      return render_block( $block );
    }
    return ob_get_clean(); 
  }

  // Raw PDF ShortCode
  public function raw_pdf($atts){
    extract(shortcode_atts(array(
      'id' => null,
    ), $atts));

    $post_type = get_post_type($id);
    $post = get_post($id);
    
    if($post_type !== 'pdfposter'){
      return false;
    }

    $isGutenberg = get_post_meta($id, 'isGutenberg', true);

    if($isGutenberg){
     $content = $post->post_content ?? false;
     if($content){
        $blocks = parse_blocks($content);
        $blocks[0]['attrs']['onlyPDF'] = true;
        return render_block($blocks[0]);
      }
    }else {
      $block = Utils::generate_pdf_poster_block($id);
      $block['attrs']['onlyPDF'] = true;
      return render_block( $block );
    }
  }

 


  public function pdf_embed($atts){
    $attrs = shortcode_atts($this->pdf_embed_attrs() , $atts);

    $block = $this->pdf_embed_to_block($attrs);
    return render_block( $block );
  }


  public function pdf_embed_attrs(){
    return [
        'url' => null,
        'width' => '100%',
        'height' => '842px',
        'print' => 'false',
        'title' => null,
        'download_btn' => 'false',
        'fullscreen_btn_text' => 'View Fullscreen'
    ];
  }

  public function pdf_embed_to_block($attrs){
    extract($attrs);
    return [
      "blockName" => "pdfp/pdfposter",
      "attrs" => [
        'uniqueId' => wp_unique_id( 'pdfp' ),
        'file' => esc_url($url),
        'title' => esc_html($title),
        'height' => esc_html($height),
        'width' => esc_html($width),
        'print' => $print === 'true',
        'downloadButton' => $download_btn === 'true',
        'fullscreenButtonText' => esc_html($fullscreen_btn_text),
        'fullscreenButton' => true
      ]
      
    ];
  }



}
