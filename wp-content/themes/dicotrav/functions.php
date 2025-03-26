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

            $posts_by_letter[$first_letter][] = '<li><a class="links-dark" href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }
    }
    wp_reset_postdata();

    // **Création du menu d'index**
    $output = '<div class="alphabet-index">';
    $output .= '<a class="links-dark-underline" href="#special">#</a> '; // Bouton "#"

    foreach ($letters as $letter) {
        if (isset($posts_by_letter[$letter])) {
            $output .= '<a class="links-dark-underline" href="#' . $letter . '">' . $letter . '</a> ';
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
        // Récupère l'URL de la page "Mon Compte"
        $account_url = um_get_core_page('account'); 
        
        // Récupère l'URL de la photo de profil Ultimate Member
        $user_id = get_current_user_id();
        $profile_picture = get_avatar_url($user_id, array('size' => 100)); // Taille ajustable si besoin
        
        // Retourne le lien avec l'image de profil
        return '<a href="' . esc_url($account_url) . '" class="profile-logo-link">
                    <img src="' . esc_url($profile_picture) . '" alt="Photo de profil">
                </a>';
    } else {
        // Si l'utilisateur n'est pas connecté, afficher un lien vers la page de connexion
        $login_url = um_get_core_page('login');
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

function enregistrer_like_utilisateur($post_id, $args = null) {
    if ( !is_user_logged_in() ) {
        return;
    }
    
    $user_id = get_current_user_id();
    $likes = get_user_meta($user_id, '_liked_posts', true);
    if ( !is_array($likes) ) {
        $likes = array();
    }
    
    // On bascule le like : s'il est déjà liké, on le retire ; sinon, on l'ajoute.
    if ( in_array($post_id, $likes) ) {
        // Un-like : retirer le post
        $key = array_search($post_id, $likes);
        unset($likes[$key]);
        $likes = array_values($likes);
        update_user_meta($user_id, '_liked_posts', $likes);
        error_log("Post retiré des likes (unlike)");
    } else {
        // Like : ajouter le post
        $likes[] = $post_id;
        update_user_meta($user_id, '_liked_posts', $likes);
        error_log("Post ajouté aux likes");
    }
}
add_action('wp_ulike_after_process', 'enregistrer_like_utilisateur', 10, 2);

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
                    <p class="card-button-parent">
                    <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                    </p>
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
                    <p class="card-button-parent">
                    <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                    </p>
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
                            <p class="card-button-parent">
                            <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                            </p>
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
                    <p class="card-button-parent">
                    <a class="card-button" href="<?php the_permalink(); ?>">Lire</a>
                    </p>
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

/*	-----------------------------------------------------------------------------------------------
	barre de progression temps de lecture articles
--------------------------------------------------------------------------------------------------- */

function shortcode_progression_lecture() {
    global $post;
    
    // Récupérer le contenu de l'article et compter les mots
    $content = strip_tags($post->post_content);
    $word_count = str_word_count($content);
    
    // Calculer le temps de lecture en minutes (en supposant 200 mots par minute)
    //$reading_time = ceil($word_count / 200);

    // Code HTML de la barre de progression et affichage du temps de lecture
    $output = '<div id="reading-progress-container" style="position:fixed;top:0;left:0;width:100%;z-index:9999;">';
    $output .= '<div id="reading-progress-bar" style="width:0%;height:5px;background:#3498db;"></div>';
    $output .= '</div>';
    //$output .= '<div id="reading-time" style="position:fixed;top:10px;right:10px;background:#fff;padding:5px;border:1px solid #ccc;border-radius:3px;">Temps de lecture : ' . $reading_time . ' min</div>';
    
    // Code JavaScript pour mettre à jour la barre de progression en fonction du scroll
    $output .= '
    <script>
      document.addEventListener("scroll", function(){
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var docHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight);
        var winHeight = window.innerHeight;
        var scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
        document.getElementById("reading-progress-bar").style.width = scrollPercent + "%";
      });
    </script>';
    
    return $output;
}
add_shortcode('progression_lecture', 'shortcode_progression_lecture');

/*	-----------------------------------------------------------------------------------------------
	Download PDF
--------------------------------------------------------------------------------------------------- */

// Crée le shortcode [download_pdf]
function shortcode_download_pdf() {
    global $post;
    if ( function_exists('get_field') ) {
        $pdf_file = get_field('pdf_upload', $post->ID);
        if ( $pdf_file ) {
            $pdf_url = is_array($pdf_file) ? $pdf_file['url'] : $pdf_file;
            // Chemin de l'image PNG (doit être uploadée dans ton dossier /images/ de ton thème)
            $png_url = get_template_directory_uri() . '/images/download.png';
            
            $output  = '<a class="download-pdf-btn" href="' . esc_url($pdf_url) . '" download>';
            $output .= '<img src="' . esc_url($png_url) . '" alt="Télécharger le PDF" class="pdf-download-img" />';
            $output .= '</a>';
            
            return $output;
        }
    }
    return '';
}
add_shortcode('download_pdf', 'shortcode_download_pdf');

/*	-----------------------------------------------------------------------------------------------
	image article
--------------------------------------------------------------------------------------------------- */

function shortcode_featured_image_with_caption() {
    global $post;

    // Si l'article a une image mise en avant, récupère son URL et sa légende
    if ( has_post_thumbnail( $post->ID ) ) {
        $image_url = get_the_post_thumbnail_url( $post->ID, 'full' );
        // Depuis WP 4.4, on peut utiliser cette fonction pour obtenir la légende de l'image mise en avant
        $caption = get_the_post_thumbnail_caption( $post->ID );
    } else {
        // Sinon, on utilise une image par défaut
        $image_url = get_template_directory_uri() . '/images/default.jpg';
        $caption = '';
    }

    // On peut utiliser le titre de l'article comme alt text
    $alt_text = get_the_title( $post->ID );

    // Construction du code HTML avec une balise <figure> et <figcaption>
    $output  = '<figure class="featured-image">';
    $output .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt_text ) . '" />';
    if ( $caption ) {
        $output .= '<figcaption>' . esc_html( $caption ) . '</figcaption>';
    }
    $output .= '</figure>';

    return $output;
}
add_shortcode('featured_image', 'shortcode_featured_image_with_caption');

function shortcode_bibliographie() {
    global $post;
    
    if ( function_exists('get_field') ) {
        // Récupère le contenu du champ textarea "bibliographie"
        $biblio_text = get_field('bibliographie', $post->ID);
        
        if ( $biblio_text ) {
            // Découpe le contenu en lignes (chaque ligne correspond à une entrée)
            $lines = preg_split("/(\r\n|\n|\r)/", $biblio_text);
            $output  = '<div class="bibliographie">';
            $output .= '<h2>Bibliographie</h2>';
            $output .= '<ul>';
            foreach ( $lines as $line ) {
                // Ignore les lignes vides
                if ( trim($line) ) {
                    $output .= '<li>' . esc_html( trim($line) ) . '</li>';
                }
            }
            $output .= '</ul>';
            $output .= '</div>';
            return $output;
        }
    }
    
    return '';
}
add_shortcode('bibliographie', 'shortcode_bibliographie');

function shortcode_citation() {
    global $post;
    
    if ( function_exists('get_field') ) {
        $citation = get_field('citation', $post->ID);
        $doi_code = get_field('doi_code', $post->ID); // champ optionnel pour le DOI
        
        if ( $citation ) {
            $output  = '<div class="citation">';
            $output .= '<h2>Citation</h2>';
            $output .= '<p>' . wp_kses_post( $citation ) . '</p>';
            
            // Si un DOI est renseigné, on l'affiche comme lien
            if ( $doi_code ) {
                $output .= '<p class="Account-link"><a href="https://doi.org/' . esc_attr( $doi_code ) . '" target="_blank">DOI : ' . esc_html( $doi_code ) . '</a></p>';
            }
            
            $output .= '</div>';
            return $output;
        }
    }
    
    return '';
}
add_shortcode('citation', 'shortcode_citation');

/*	-----------------------------------------------------------------------------------------------
	LeafLet
--------------------------------------------------------------------------------------------------- */

// Charger les fichiers CSS et JS de Leaflet et notre CSS custom
if ( ! function_exists('enqueue_leaflet_assets') ) {
    function enqueue_leaflet_assets() {
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', array(), null, true);
        // Charge ton fichier CSS custom (vérifie que le chemin est correct)
        wp_enqueue_style('timeline-leaflet-style', get_template_directory_uri() . '/css/timeline-leaflet-style.css');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_leaflet_assets');

function timeline_leaflet_shortcode($atts) {
    ob_start();
    ?>
    <!-- La carte occupe toute la largeur et se positionne en dessous du header -->
    <div id="map"></div>

    <!-- Conteneur de la timeline sur toute la largeur -->
    <div id="timeline">
        <!-- Affichage de la valeur sélectionnée (placé au-dessus du slider) -->
        <span id="timeline-year">2025</span>
        <!-- Slider range -->
        <input type="range" id="timeline-range" min="-500" max="2025" value="2025" />
        <!-- Conteneur pour la graduation de la timeline (en arrière-plan, pointer-events désactivés) -->
        <div id="timeline-graduation"></div>
    </div>

    <!-- Modale qui s'affiche sur la droite, masquée par défaut -->
    <div id="modal">
        <!-- Bouton de fermeture (croix rouge) -->
        <button id="modal-close">&times;</button>
        <h3 id="modal-title">Articles</h3>
        <ul id="article-list"></ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialisation de la carte Leaflet
            var map = L.map('map').setView([51.505, -0.09], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Tableau des "marqueurs globaux" pour les pays avec des coordonnées globales
            var countries = [
                { country: 'France', lat: 46.603354, lng: 1.888333 },
                { country: 'Espagne', lat: 40.463667, lng: -3.74922 },
                { country: 'Italie', lat: 41.87194, lng: 12.56738 },
                { country: 'Royaume-Uni', lat: 55.378051, lng: -3.435973 },
                { country: 'Allemagne', lat: 51.165691, lng: 10.451526 },
                { country: 'Portugal', lat: 39.399872, lng: -8.224454 }
                // Ajoutez d'autres pays si besoin...
            ];

            // Stocker les marqueurs par pays pour pouvoir les mettre à jour
            var markerByCountry = {};

            // Créer les marqueurs avec une icône personnalisée (affichant uniquement le compteur)
            countries.forEach(function(item) {
                var icon = L.divIcon({
                    html: '<div class="marker-label"><span class="marker-count">0</span></div>',
                    className: 'custom-marker-icon',
                    iconSize: [40, 40]
                });
                var marker = L.marker([item.lat, item.lng], {icon: icon}).addTo(map);
                marker.bindPopup(item.country);
                marker.on('click', function() {
                    console.log("Marker cliqué pour " + item.country);
                    loadArticlesForCountry(item.country);
                });
                markerByCountry[item.country] = marker;
            });

            // Référence à l'élément slider et affichage
            var timelineRange = document.getElementById('timeline-range');
            var timelineYear = document.getElementById('timeline-year');
            var minYear = parseInt(timelineRange.min);
            var maxYear = parseInt(timelineRange.max);

            // Déclaration de la variable pour le debounce des compteurs
            var markerUpdateTimeout;

            // Met à jour la valeur affichée et lance le chargement des articles et la mise à jour des compteurs
            function updateYear(newVal) {
                newVal = Math.max(minYear, Math.min(maxYear, newVal));
                timelineRange.value = newVal;
                timelineYear.innerText = newVal;
                loadArticlesForPeriod(newVal);
                updateMarkersCount(newVal);
            }

            // Événement sur le slider
            timelineRange.addEventListener('input', function(event) {
                updateYear(event.target.value);
            });

            // Génération dynamique de la graduation avec affichage des centaines en dessous
            var graduationContainer = document.getElementById('timeline-graduation');
            for (var i = minYear; i <= maxYear; i += 10) {
                var mark = document.createElement('div');
                mark.classList.add('timeline-mark');
                var percent = ((i - minYear) / (maxYear - minYear)) * 100;
                mark.style.left = percent + '%';
                // Pour les dizaines et les moitiés, on ajoute simplement la classe
                if (i % 100 === 0) {
                    mark.classList.add('mark-hundred');
                    // Créer un span pour le label (les centaines)
                    var label = document.createElement('span');
                    label.classList.add('timeline-label');
                    label.innerText = i;
                    mark.appendChild(label);
                } else if (i % 50 === 0) {
                    mark.classList.add('mark-half');
                } else {
                    mark.classList.add('mark-ten');
                }
                graduationContainer.appendChild(mark);
            }

            // Fonction pour charger les articles pour la période
            // La plage utilisée sera [min, max[ (min inclus, max exclus)
            function loadArticlesForPeriod(year) {
                var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
                var xhr = new XMLHttpRequest();
                xhr.open('GET', ajax_url + '?action=get_articles_for_year&year=' + year, true);
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var responseText = xhr.responseText.trim();
                        var articles = [];
                        if (responseText.length > 0) {
                            try {
                                articles = JSON.parse(responseText);
                            } catch(e) {
                                console.error("Erreur lors de l'analyse JSON pour loadArticlesForPeriod:", e);
                                articles = [];
                            }
                        } else {
                            console.warn("Réponse vide reçue pour loadArticlesForPeriod.");
                        }
                        console.log('Articles pour la période (' + year + '): ', articles);
                    }
                };
                xhr.send();
            }

            // Fonction pour charger et afficher les articles pour un pays et la période sélectionnée
            function loadArticlesForCountry(country) {
                var currentYear = timelineYear.innerText;
                var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
                var xhr = new XMLHttpRequest();
                xhr.open('GET', ajax_url + '?action=get_articles_for_country&country=' + encodeURIComponent(country) + '&year=' + currentYear, true);
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var responseText = xhr.responseText.trim();
                        var articles = [];
                        if (responseText.length > 0) {
                            try {
                                articles = JSON.parse(responseText);
                            } catch(e) {
                                console.error("Erreur lors de l'analyse JSON pour loadArticlesForCountry:", e);
                                articles = [];
                            }
                        } else {
                            console.warn("Réponse vide reçue pour loadArticlesForCountry.");
                        }
                        var articleList = document.getElementById('article-list');
                        articleList.innerHTML = '';
                        document.getElementById('modal-title').innerText = 'Articles pour ' + country;
                        if (articles.length > 0) {
                            articles.forEach(function(article) {
                                var li = document.createElement('li');
                                li.innerHTML = '(' + article.date + ') | <a href="' + article.url + '" target="_blank">' + article.title + '</a>';
                                articleList.appendChild(li);
                            });
                        } else {
                            articleList.innerHTML = '<li>Aucun article trouvé pour ' + country + '.</li>';
                        }
                        // Ouvrir la modale
                        document.getElementById('modal').classList.add('open');
                    } else {
                        console.error("Erreur AJAX, statut: " + xhr.status);
                        document.getElementById('article-list').innerHTML = '<li>Erreur lors du chargement des articles.</li>';
                        document.getElementById('modal').classList.add('open');
                    }
                };
                xhr.onerror = function() {
                    console.error("Erreur lors de la requête AJAX pour loadArticlesForCountry.");
                    document.getElementById('article-list').innerHTML = '<li>Erreur lors du chargement des articles.</li>';
                    document.getElementById('modal').classList.add('open');
                };
                xhr.send();
            }

            // Fonction pour mettre à jour le compteur sur chaque marqueur avec debounce
            function updateMarkersCount(year) {
                clearTimeout(markerUpdateTimeout);
                markerUpdateTimeout = setTimeout(function() {
                    countries.forEach(function(item) {
                        var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', ajax_url + '?action=get_articles_count_for_country&country=' + encodeURIComponent(item.country) + '&year=' + year, true);
                        xhr.onload = function() {
                            if (xhr.status == 200) {
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    var count = parseInt(response.count, 10);
                                    if (count > 20) {
                                        count = "20+";
                                    }
                                    if (markerByCountry[item.country]) {
                                        var newIcon = L.divIcon({
                                            html: '<div class="marker-label"><span class="marker-count">' + count + '</span></div>',
                                            className: 'custom-marker-icon',
                                            iconSize: [40, 40]
                                        });
                                        markerByCountry[item.country].setIcon(newIcon);
                                    }
                                } catch(e) {
                                    console.error("Erreur lors de l'analyse JSON pour updateMarkersCount:", e);
                                }
                            }
                        };
                        xhr.send();
                    });
                }, 300);
            }

            // Fermer la modale
            document.getElementById('modal-close').addEventListener('click', function() {
                document.getElementById('modal').classList.remove('open');
            });

            // Initialisation
            updateYear(timelineRange.value);
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('timeline_leaflet', 'timeline_leaflet_shortcode');


// Fonction AJAX pour la timeline (articles par période)
// On modifie ici la requête pour retourner uniquement les articles dont la date est comprise entre [min, max[
function get_articles_for_year() {
    if (isset($_GET['year'])) {
        $year = intval($_GET['year']);
        error_log('Year selected: ' . $year);
        if ($year >= 0) {
            $min = floor($year / 100) * 100;
            $max = $min + 100;
        } else {
            $max = ceil($year / 100) * 100;
            $min = $max - 100;
        }
        error_log('Period: ' . $min . ' - ' . $max);
        // Conditions : date >= $min et date < $max
        $args = array(
            'post_type' => 'post',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'date',
                    'value'   => $min,
                    'compare' => '>=',
                    'type'    => 'NUMERIC'
                ),
                array(
                    'key'     => 'date',
                    'value'   => $max,
                    'compare' => '<',
                    'type'    => 'NUMERIC'
                )
            )
        );
        error_log('Query args (year): ' . print_r($args, true));
        $query = new WP_Query($args);
        $articles = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $articles[] = array(
                    'title' => get_the_title(),
                    'url'   => get_permalink(),
                    'date'  => get_post_meta(get_the_ID(), 'date', true)
                );
            }
            wp_reset_postdata();
        }
        error_log('Articles retrieved (year): ' . print_r($articles, true));
        echo json_encode($articles);
    }
    wp_die();
}
add_action('wp_ajax_get_articles_for_year', 'get_articles_for_year');
add_action('wp_ajax_nopriv_get_articles_for_year', 'get_articles_for_year');


// Fonction AJAX pour les articles par pays et période
function get_articles_for_country() {
    if (isset($_GET['country']) && isset($_GET['year'])) {
        $country = sanitize_text_field($_GET['country']);
        $year = intval($_GET['year']);
        error_log('Country selected: ' . $country);
        error_log('Year received for country filter: ' . $year);
        if ($year >= 0) {
            $min = floor($year / 100) * 100;
            $max = $min + 100;
        } else {
            $max = ceil($year / 100) * 100;
            $min = $max - 100;
        }
        error_log('Period for country filter: ' . $min . ' - ' . $max);
        $args = array(
            'post_type'  => 'post',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'country',
                    'value'   => $country,
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ),
                array(
                    'key'     => 'date',
                    'value'   => $min,
                    'compare' => '>=',
                    'type'    => 'NUMERIC'
                ),
                array(
                    'key'     => 'date',
                    'value'   => $max,
                    'compare' => '<',
                    'type'    => 'NUMERIC'
                )
            ),
            'meta_key'  => 'date',
            'orderby'   => 'meta_value',
            'order'     => 'ASC'
        );
        error_log('Country query args: ' . print_r($args, true));
        $query = new WP_Query($args);
        $articles = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $articles[] = array(
                    'title' => get_the_title(),
                    'url'   => get_permalink(),
                    'date'  => get_post_meta(get_the_ID(), 'date', true)
                );
            }
            wp_reset_postdata();
        }
        echo json_encode($articles);
    }
    wp_die();
}
add_action('wp_ajax_get_articles_for_country', 'get_articles_for_country');
add_action('wp_ajax_nopriv_get_articles_for_country', 'get_articles_for_country');


// Fonction AJAX pour obtenir le compte d'articles par pays et période
function get_articles_count_for_country() {
    if ( isset($_GET['country']) && isset($_GET['year']) ) {
        $country = sanitize_text_field($_GET['country']);
        $year = intval($_GET['year']);
        error_log('Count - Country selected: ' . $country);
        error_log('Count - Year received: ' . $year);
        if ($year >= 0) {
            $min = floor($year / 100) * 100;
            $max = $min + 100;
        } else {
            $max = ceil($year / 100) * 100;
            $min = $max - 100;
        }
        error_log('Count - Period: ' . $min . ' - ' . $max);
        $args = array(
            'post_type'  => 'post',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'country',
                    'value'   => $country,
                    'compare' => '=',
                    'type'    => 'CHAR',
                ),
                array(
                    'key'     => 'date',
                    'value'   => $min,
                    'compare' => '>=',
                    'type'    => 'NUMERIC'
                ),
                array(
                    'key'     => 'date',
                    'value'   => $max,
                    'compare' => '<',
                    'type'    => 'NUMERIC'
                )
            )
        );
        $query = new WP_Query($args);
        $count = $query->found_posts;
        echo json_encode(array('count' => $count));
    }
    wp_die();
}
add_action('wp_ajax_get_articles_count_for_country', 'get_articles_count_for_country');
add_action('wp_ajax_nopriv_get_articles_count_for_country', 'get_articles_count_for_country');
