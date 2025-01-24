<?php
namespace PDFPro\Rest;

use PDFPro\Helper\Functions as Utils;

class GetMeta{
    public $route = '';

    function __construct(){
        $this->route = '/single(?:/(?P<id>\d+))?';
        add_action('rest_api_init', [$this, 'single_doc']);
    }

    public function single_doc(){
        register_rest_route( 'pdfposter/v1',
        $this->route,
            [
                'methods' => 'GET',
                'callback' => [$this, 'single_doc_callback'],
                'permission_callback' => '__return_true',
            ] 
        );
    }

    function single_doc_callback(\WP_REST_Request $request){
        $response = [];
        $params = $request->get_params();
        $id = $params['id'] ?? null;

        if(!$id){
            return new \WP_REST_Response([]);
        }

        $post_type = get_post_type($id);
        $post = get_post($id);
        
        if($post_type !== 'pdfposter'){
          return new \WP_REST_Response([]);
        }
    
        $isGutenberg = get_post_meta($id, 'isGutenberg', true);
    
        if($isGutenberg){
         $content = $post->post_content ?? false;
         if($content){
            $blocks = parse_blocks($content);
            $data = wp_parse_args( $blocks[0]['attrs'], Utils::generate_pdf_poster_block(null)['attrs'] );
          }
        }else {
          $block = Utils::generate_pdf_poster_block($id);
          $data = $block['attrs'];
        }
       
        return new \WP_REST_Response($data);
    }
 
}
