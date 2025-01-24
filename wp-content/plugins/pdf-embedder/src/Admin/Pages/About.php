<?php

namespace PDFEmbedder\Admin\Pages;

use PDFEmbedder\Admin\Partners;

/**
 * About Page.
 *
 * @since 4.9.0
 */
class About extends Page {

	public const SLUG = 'about';

	/**
	 * Get the title of the page.
	 *
	 * @since 4.9.0
	 */
	public function get_title(): string {

		return __( 'About', 'pdf-embedder' );
	}

	/**
	 * Page content.
	 *
	 * @since 4.9.0
	 */
	public function content() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
		?>

		<h3 class="headline-title">
			<?php esc_html_e( 'Our Plugins', 'pdf-embedder' ); ?>
		</h3>

		<p>
			<?php esc_html_e( 'Get the most out of your site with these plugins.', 'pdf-embedder' ); ?>
		</p>

		<div class="pdfemb-partners-wrap">
			<?php ( new Partners() )->show(); ?>
		</div>

		<?php
	}
}
