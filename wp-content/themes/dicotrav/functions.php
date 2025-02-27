<?php

/*	-----------------------------------------------------------------------------------------------
	THEME SUPPORTS
--------------------------------------------------------------------------------------------------- */

function davis_blocks_setup() {
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'davis_blocks_setup' );


/*	-----------------------------------------------------------------------------------------------
	ENQUEUE STYLESHEETS
--------------------------------------------------------------------------------------------------- */

function davis_blocks_styles() {
	wp_enqueue_style( 'davis-blocks-styles', get_template_directory_uri() . '/style.css', array(), wp_get_theme( 'davis-blocks' )->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'davis_blocks_styles' );


/*	-----------------------------------------------------------------------------------------------
	REGISTER BLOCK STYLES
--------------------------------------------------------------------------------------------------- */

function davis_blocks_register_block_styles() {
	register_block_style( 'core/separator', array(
		'name'  	=> 'davis-separator',
		'label' 	=> esc_html__( 'Diamonds', 'davis-blocks' ),
	) );
}
add_action( 'init', 'davis_blocks_register_block_styles' );

/*	-----------------------------------------------------------------------------------------------
	Index alphabetique pour le dictionnaire
--------------------------------------------------------------------------------------------------- */

function alphabetical_index_shortcode() {
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);
    $posts_by_letter = array();
    $letters = range('A', 'Z');
    $special_characters = '#';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $first_letter = strtoupper(mb_substr(get_the_title(), 0, 1));

            // Si ce n'est pas une lettre, on le met dans la section "#"
            if (!preg_match('/[A-Z]/', $first_letter)) {
                $first_letter = $special_characters;
            }

            if (!isset($posts_by_letter[$first_letter])) {
                $posts_by_letter[$first_letter] = array();
            }

            $posts_by_letter[$first_letter][] = '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }
    }
    wp_reset_postdata();

    // **Création du menu d'index**
    $output = '<div class="alphabet-index">';
    $output .= '<a href="#special">#</a> '; // Bouton "#"

    foreach ($letters as $letter) {
        if (isset($posts_by_letter[$letter])) {
            $output .= '<a href="#' . $letter . '">' . $letter . '</a> ';
        } else {
            $output .= '<span class="inactive">' . $letter . '</span> ';
        }
    }
    $output .= '</div>';

    // **Création du contenu des articles**
    $output .= '<div class="alphabetical-index">';
    foreach ($posts_by_letter as $letter => $posts) {
        $output .= '<h2 id="' . ($letter == '#' ? 'special' : $letter) . '">' . $letter . '</h2><ul>' . implode('', $posts) . '</ul>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('alphabetical_index', 'alphabetical_index_shortcode');

function afficher_lien_connexion_compte() {
    if (is_user_logged_in()) {
        // Si l'utilisateur est connecté, afficher un lien vers la page "Mon Compte" Ultimate Member
        $account_url = um_get_core_page('account'); // Récupère l'URL de la page "Mon Compte"
        return '<a href="' . esc_url($account_url) . '">Mon Compte</a>';
    } else {
        // Si l'utilisateur n'est pas connecté, afficher un lien vers la page de connexion UM
        $login_url = um_get_core_page('login'); // Récupère l'URL de la page de connexion
        return '<a href="' . esc_url($login_url) . '">Se connecter</a>';
    }
}
add_shortcode('lien_connexion_compte', 'afficher_lien_connexion_compte');

function afficher_lien_admin_auteur_um() {
    if (current_user_can('administrator')) {
        // Lien pour administrateurs
        return '<a href="' . esc_url(admin_url()) . '">Tableau de bord Admin</a>';
    } elseif (current_user_can('edit_posts')) {
        // Lien pour les auteurs
        return '<a href="' . esc_url(admin_url('post-new.php')) . '">Écrire un article</a>';
    }
    return ''; // Rien pour les autres utilisateurs
}
add_shortcode('lien_admin_auteur', 'afficher_lien_admin_auteur_um');

function enregistrer_like_utilisateur($post_id) {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $likes = get_user_meta($user_id, '_liked_posts', true);

        if (!is_array($likes)) {
            $likes = [];
        }

        if (!in_array($post_id, $likes)) {
            $likes[] = $post_id;
            update_user_meta($user_id, '_liked_posts', $likes);
        }
    }
}
add_action('wp_ulike_after_process', 'enregistrer_like_utilisateur');

function afficher_likes_utilisateur() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $likes = get_user_meta($user_id, '_liked_posts', true);

        if (!empty($likes)) {
            $output = '<ul>';
            foreach ($likes as $post_id) {
                $output .= '<li><a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></li>';
            }
            $output .= '</ul>';
            return $output;
        } else {
            return "<p>Vous n'avez encore aimé aucun article.</p>";
        }
    } else {
        return "<p>Veuillez vous connecter pour voir vos coups de cœur.</p>";
    }
}
add_shortcode('mes_coups_de_coeur', 'afficher_likes_utilisateur');

/*	-----------------------------------------------------------------------------------------------
	Carousel
--------------------------------------------------------------------------------------------------- */

// Enqueue d'un script "vide" pour pouvoir y ajouter notre code inline
function enqueue_inline_carousel_script() {
    // Enqueue jQuery (souvent déjà inclus, sinon il sera chargé ici)
    wp_enqueue_script('jquery');
    
    // Enregistrer un script factice pour l'inline script
    wp_register_script('custom-carousel-inline', '', array('jquery'), null, true);
    wp_enqueue_script('custom-carousel-inline');
    
    // Code JavaScript du carousel
    $custom_js = "
    jQuery(document).ready(function($) {
        var \$carousel = $('.carousel-container');
        var \$items = \$carousel.find('.carousel-item');
        var currentIndex = 0;
        var itemCount = \$items.length;
        
        // Masquer tous les items sauf le premier
        \$items.hide().eq(currentIndex).show();
        
        // Fonction pour passer à l'élément suivant
        function showNextItem() {
            \$items.eq(currentIndex).fadeOut(600);
            currentIndex = (currentIndex + 1) % itemCount;
            \$items.eq(currentIndex).fadeIn(600);
        }
        
        // Changement automatique toutes les 5 secondes
        setInterval(showNextItem, 5000);
    });
    ";
    
    // Ajout du script inline
    wp_add_inline_script('custom-carousel-inline', $custom_js);
}
add_action('wp_enqueue_scripts', 'enqueue_inline_carousel_script');

// Fonction qui construit le carousel et la mise en page, accessible via le shortcode [custom_carousel]
function display_custom_carousel() {
    ob_start();

    // Récupérer les 3 derniers articles
    $args = array(
       'posts_per_page' => 3,
       'post_status'    => 'publish'
    );
    $latest_posts = new WP_Query($args);
    ?>

    <div class="main-container">
      <!-- Colonne de gauche : Carousel -->
      <div class="left-column">
         <?php if ( $latest_posts->have_posts() ) : ?>
         <div class="carousel-container">
            <?php while ( $latest_posts->have_posts() ) : $latest_posts->the_post();
                // Si l'article n'a pas d'image à la une, on affiche une image par défaut
                $featured_image = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : get_template_directory_uri() . '/images/default.jpg';
            ?>
             <div class="carousel-item">
               <a href="<?php the_permalink(); ?>">
                  <div class="carousel-image" style="background-image: url('<?php echo esc_url($featured_image); ?>');">
                    <div class="overlay">
                       <span>Dernière publication</span>
                    </div>
                  </div>
               </a>
             </div>
            <?php endwhile; wp_reset_postdata(); ?>
         </div>
         <?php endif; ?>
      </div>

      <!-- Colonne de droite : 2 images (en haut et en bas) avec liens et overlay -->
      <div class="right-column">
         <a href="http://localhost/DicoTrav/index.php/atlas/" class="right-link top-link">
            <div class="top-image" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/right-top.jpg');">
              <div class="overlay">
                 <span>Altas</span>
              </div>
            </div>
         </a>
         <a href="http://localhost/DicoTrav/index.php/frise/" class="right-link bottom-link">
            <div class="bottom-image" style="background-image: url('<?php echo get_template_directory_uri(); ?>/images/right-bottom.jpg');">
              <div class="overlay">
                 <span>Frise Chronologique</span>
              </div>
            </div>
         </a>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_carousel', 'display_custom_carousel');

/*	-----------------------------------------------------------------------------------------------
	Cartes
--------------------------------------------------------------------------------------------------- */

function display_search_results_cards_shortcode() {
    ob_start();

    // Vérifier si on est dans une recherche
    if (is_search() && have_posts()) {
        echo '<div class="article-cards-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px;">';

        while (have_posts()) : the_post();

            // Filtrer pour n'afficher que les articles (exclut pages, produits WooCommerce, etc.)
            if (get_post_type() !== 'post') {
                continue;
            }

            // Récupère l'image mise en avant ou une image par défaut
            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : get_template_directory_uri() . '/images/default.jpg';
            ?>
            <div class="article-card" style="border:1px solid #ddd; box-shadow:0 2px 5px rgba(0,0,0,0.1); overflow:hidden; background:#fff; border-radius:10px;">
                <!-- Image de l'article -->
                <div class="card-image" style="width:100%; height:200px; background-image: url('<?php echo esc_url($image_url); ?>'); background-size:cover; background-position:center; border-top-left-radius:10px; border-top-right-radius:10px;"></div>
                
                <!-- Contenu de la carte -->
                <div class="card-content" style="padding:1em; background-color:#f5f5f5;">
                    <h2 class="card-title" style="margin:0 0 10px;">
                        <a href="<?php the_permalink(); ?>" style="color:#333; text-decoration:none;">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    <p class="card-date" style="color:#777; font-size:0.9em; margin-bottom:10px;">
                        <?php echo get_the_date(); ?>
                    </p>
                    <p class="card-excerpt" style="margin-bottom:15px;">
                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                    </p>
                    <a class="card-button" href="<?php the_permalink(); ?>" style="display:inline-block; background:#0073aa; color:#fff; padding:8px 15px; text-decoration:none; border-radius:3px; float:right;">
                        Lire
                    </a>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>';
    } else {
        echo '<p>Aucun article trouvé pour cette recherche.</p>';
    }

    return ob_get_clean();
}

// Ajout du shortcode [search_results_cards]
add_shortcode('search_results_cards', 'display_search_results_cards_shortcode');

