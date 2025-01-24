<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use PDFPro\Helper\Functions as Utils;

$id = wp_unique_id('pdfp-');

// echo '<pre>';
// print_r( $attributes );
// echo '</pre>';

extract($attributes );

if($protect){
    $attributes['file'] = Utils::scramble('encode', $attributes['file']);
}


$className = $className ?? '';
$blockClassName = 'wp-block-pdfp-pdf-poster ' . $className . ' align' . $align;

?>

<div 
    class='<?php echo esc_attr( $blockClassName ); ?>'
    id='<?php echo esc_attr( $id ); ?>'
    data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'
    style="text-align: <?php echo esc_attr($alignment) ?>"
>
</div>