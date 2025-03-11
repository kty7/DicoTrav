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
	Cartes quand on "recherche"
--------------------------------------------------------------------------------------------------- */

// Fonction pour afficher les résultats de recherche sous forme de cartes
function display_search_results_cards_shortcode() {
    ob_start();
    
    // Charger le CSS
    wp_enqueue_style('search-results-cards', get_template_directory_uri() . '/css/search-results-cards.css');
    
    if (is_search() && have_posts()) {
        echo '<div class="article-cards-grid">';
        
        while (have_posts()) : the_post();
            
            if (get_post_type() !== 'post') {
                continue;
            }
            
            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : get_template_directory_uri() . '/images/default.jpg';
            ?>
            <div class="article-card">
                <div class="card-image" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
                <div class="card-content">
                    <h2 class="card-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    <p class="card-date">
                        <?php echo get_the_date(); ?>
                    </p>
                    <p class="card-excerpt">
                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                    </p>
                    <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>'; // Fermeture de la grille
        
        echo '<div class="pagination">';
        echo paginate_links(array(
            'total' => $GLOBALS['wp_query']->max_num_pages,
            'current' => max(1, get_query_var('paged')),
            'format' => '?paged=%#%',
            'prev_text' => '← Précédent',
            'next_text' => 'Suivant →',
        ));
        echo '</div>';
    } else {
        echo '<p class="info-msg">Désolé, mais rien n’a été trouvé. Veuillez réessayer avec d’autres mots-clés.</p>';
    }
    
    return ob_get_clean();
}

add_shortcode('search_results_cards', 'display_search_results_cards_shortcode');

/*	-----------------------------------------------------------------------------------------------
	Cartes pour les catégories
--------------------------------------------------------------------------------------------------- */

// Fonction pour afficher les articles d'une catégorie sous forme de cartes
function display_category_cards_shortcode($atts) {
    // Ici, on définit "main" comme wrapper par défaut
    $atts = shortcode_atts(array(
         'wrapper' => 'main',  // Par défaut, la balise générée sera <main>
    ), $atts, 'category_cards');
    
    // Choisir la balise wrapper en fonction de l'attribut (main ou div)
    $wrapper = ($atts['wrapper'] === 'main') ? 'main' : 'div';

    ob_start();

    // Charger le CSS des cartes (vérifie que le chemin est correct)
    wp_enqueue_style('search-results-cards', get_template_directory_uri() . '/css/search-results-cards.css');

    // Récupérer la catégorie actuelle depuis l'URL (sur une archive de catégorie)
    $current_category = get_queried_object();
    $cat_slug = '';
    if ( $current_category && isset($current_category->slug) ) {
        $cat_slug = $current_category->slug;
    }

    // Ouverture du conteneur avec la balise choisie (<main> par défaut)
    echo '<' . $wrapper . ' class="wp-block-group has-global-padding is-layout-constrained wp-container-core-group-is-layout-8 wp-block-group-is-layout-constrained" id="wp--skip-link--target">';
    echo '<div style="margin-top:0px !important" class="wp-block-group alignwide has-global-padding is-layout-constrained wp-container-core-group-is-layout-7 wp-block-group-is-layout-constrained">';

    // Afficher le H1 avec le nom de la catégorie
    if ( $current_category && isset($current_category->name) ) {
        echo '<h1 style="padding:32px 0 0 0 !important; padding-top:var(--wp--preset--spacing--40); margin-top:0; margin-bottom:0;" class="has-text-align-left alignwide wp-block-post-title has-extra-large-font-size">' . esc_html($current_category->name) . '</h1>';
    } else {
        echo '<h1 style="padding:32px 0 0 0 !important; padding-top:var(--wp--preset--spacing--40); margin-top:0; margin-bottom:0;" class="has-text-align-left alignwide wp-block-post-title has-extra-large-font-size">Catégorie</h1>';
    }

    // Préparer la requête personnalisée
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $args = array(
        'post_type'     => 'post',
        'category_name' => $cat_slug,
        'paged'         => $paged,
    );
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        echo '<div class="article-cards-grid" style="padding: 32px;">';
        while ( $query->have_posts() ) : $query->the_post();

            // On s'assure d'afficher uniquement les articles de type "post"
            if ( get_post_type() !== 'post' ) {
                continue;
            }
            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : get_template_directory_uri() . '/images/default.jpg';
            ?>
            <div class="article-card">
                <div class="card-image" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
                <div class="card-content">
                    <h2 class="card-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    <p class="card-date">
                        <?php echo get_the_date(); ?>
                    </p>
                    <p class="card-excerpt">
                        <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                    </p>
                    <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>'; // Fermeture de la grille

        // Pagination
        $big = 999999999; // Un nombre improbable pour la pagination
        echo '<div class="pagination">';
        echo paginate_links( array(
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '?paged=%#%',
            'current'   => max( 1, $paged ),
            'total'     => $query->max_num_pages,
            'prev_text' => '← Précédent',
            'next_text' => 'Suivant →',
        ) );
        echo '</div>';
    } else {
        echo '<p class="info-msg">Désolé, aucun article n’a été trouvé dans cette catégorie.</p>';
    }
    
    // Fermeture des conteneurs
    echo '</div>';
    echo '</' . $wrapper . '>';

    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('category_cards', 'display_category_cards_shortcode');

/*	-----------------------------------------------------------------------------------------------
	Cartes pour les coups de coeurs
--------------------------------------------------------------------------------------------------- */

function afficher_likes_utilisateur() {
    ob_start();

    // Charger le CSS pour les cartes
    wp_enqueue_style('search-results-cards', get_template_directory_uri() . '/css/search-results-cards.css');

    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $likes = get_user_meta( $user_id, '_liked_posts', true );

        if ( ! empty( $likes ) ) {
            // Prépare la requête sur les articles aimés
            $args = array(
                'post_type' => 'post',
                'post__in'  => $likes,
                'orderby'   => 'post__in', // Pour conserver l'ordre des likes
            );
            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                echo '<div class="article-cards-grid" style="padding: 32px; margin-block-start: 1.5rem;">';
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : get_template_directory_uri() . '/images/default.jpg';
                    ?>
                    <div class="article-card">
                        <div class="card-image" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
                        <div class="card-content">
                            <h2 class="card-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            <p class="card-date">
                                <?php echo get_the_date(); ?>
                            </p>
                            <p class="card-excerpt">
                                <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                            </p>
                            <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                        </div>
                    </div>
                    <?php
                }
                echo '</div>'; // Fermeture de la grille
            } else {
                echo '<p>Vous n\'avez encore aimé aucun article.</p>';
            }
            wp_reset_postdata();
        } else {
            echo '<p>Vous n\'avez encore aimé aucun article.</p>';
        }
    } else {
        echo '<p>Veuillez vous connecter pour voir vos coups de cœur.</p>';
    }

    return ob_get_clean();
}
add_shortcode('mes_coups_de_coeur', 'afficher_likes_utilisateur');

/**
 * Shortcode pour la recherche timeline
 */
function timeline_search_shortcode() {
    ob_start();

    // Charger le CSS des cartes
    wp_enqueue_style('search-results-cards', get_template_directory_uri() . '/css/search-results-cards.css');
    
    // Charger le style de la timeline
    wp_enqueue_style('timeline-style', get_template_directory_uri() . '/css/timeline-style.css');

    // S'assurer que jQuery est chargé
    wp_enqueue_script('jquery');

    // Définition des périodes (le tableau doit correspondre aux slug de tes catégories)
    $periods = array(
        '-500', '-400', '-300', '-200', '-100', 
        '1', '100', '200', '300', '400', '500', 
        '600', '700', '800', '900', '1000', '1100', 
        '1200', '1300', '1400', '1500', '1600', 
        '1700', '1800', '1900', '2000-2025'
    );
    // Calculer l'index maximum pour le slider
    $max_index = count($periods) - 1;
    ?>
    <div id="timeline-search" style="padding: 32px;">
        <label for="timeline-range">
            Choisissez une période : <span id="timeline-label"><?php echo esc_html($periods[0]); ?></span>
        </label>
        <br>
        <input type="range" id="timeline-range" min="0" max="<?php echo $max_index; ?>" value="0" step="1">
    </div>
    <div id="timeline-results">
        <!-- Les articles correspondant à la période sélectionnée seront chargés ici -->
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Tableau des périodes passé depuis PHP
        var periods = <?php echo json_encode($periods); ?>;
        
        // Fonction qui effectue la requête Ajax pour charger les articles
        function loadTimelinePosts(index) {
            var period = periods[index];
            $('#timeline-label').text(period);
            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                type: "POST",
                data: {
                    action: "timeline_search_action",
                    period: period
                },
                beforeSend: function() {
                    $('#timeline-results').removeClass('loaded').html('<p>Chargement...</p>');
                },
                success: function(response) {
                    $('#timeline-results').html(response).addClass('loaded');
                },
                error: function() {
                    $('#timeline-results').html('<p>Une erreur est survenue.</p>').addClass('loaded');
                }
            });
        }
        
        // Chargement initial avec la première période du tableau
        loadTimelinePosts($('#timeline-range').val());
        
        // Au changement de valeur du slider
        $('#timeline-range').on('input change', function() {
            var index = $(this).val();
            loadTimelinePosts(index);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('timeline_search', 'timeline_search_shortcode');

function timeline_search_ajax_handler() {
    // Récupérer la période depuis la requête Ajax
    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '';

    // Si la valeur est "0" (ou toute autre valeur purement numérique) :
    // on vérifie le terme par nom afin d'obtenir le slug réel.
    $term = get_term_by('name', $period, 'category');
    if ($term) {
        $period_slug = $term->slug;
    } else {
        // Sinon, on utilise la valeur reçue
        $period_slug = $period;
    }

    // Préparer la requête en utilisant tax_query pour filtrer par slug de catégorie
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'post',
        'paged'     => $paged,
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $period_slug,
                'operator' => 'IN'
            )
        )
    );
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        echo '<div class="article-cards-grid" style="padding: 0px;">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : get_template_directory_uri() . '/images/default.jpg';
            ?>
            <div class="article-card">
                <div class="card-image" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
                <div class="card-content">
                    <h2 class="card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <p class="card-date"><?php echo get_the_date(); ?></p>
                    <p class="card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?></p>
                    <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p class="info-msg">Désolé, aucun article n’a été trouvé pour cette période.</p>';
    }
    wp_reset_postdata();
    wp_die();
}
add_action('wp_ajax_timeline_search_action', 'timeline_search_ajax_handler');
add_action('wp_ajax_nopriv_timeline_search_action', 'timeline_search_ajax_handler');