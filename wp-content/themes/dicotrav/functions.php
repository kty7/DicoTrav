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
                        <?php 
                        $pays = get_field('country');
                        $periode = get_field('date');
                        echo esc_html($pays) . ', ' . esc_html($periode); 
                        ?>
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
        $cat_name = $current_category->name;
        echo '<h1 style="padding:8px 0 0 0 !important; padding-top:var(--wp--preset--spacing--40); margin-top:0; margin-bottom:0;" class="has-text-align-left alignwide wp-block-post-title has-extra-large-font-size">' . esc_html($cat_name) . '</h1>';
    } else {
        $cat_name = 'Catégorie';
        echo '<h1 style="padding:8px 0 0 0 !important; padding-top:var(--wp--preset--spacing--40); margin-top:0; margin-bottom:0;" class="has-text-align-left alignwide wp-block-post-title has-extra-large-font-size">Catégorie</h1>';
    }

    // Définir le texte descriptif en fonction de la catégorie
    $description = '';
    switch ( $cat_name ) {
        case 'Concepts':
            $description = "Cette section regroupe les notions fondamentales qui ont structuré la pensée et les discours sur le travail au fil du temps. On y trouve des définitions de termes tels que « exploitation », « salariat », « division du travail » ou encore « artisanat », qui permettent de saisir les grandes évolutions idéologiques et économiques du travail.";
            break;
        case 'Corps':
            $description = "Le travail engage le corps humain, tant dans ses efforts que dans ses souffrances. Cette catégorie examine la place du corps dans l’histoire du travail : ses gestes, ses postures, ses conditions physiques, mais aussi les impacts du travail sur la santé et les représentations corporelles associées à certaines professions.";
            break;
        case 'Figures':
            $description = "Certains individus ou groupes ont marqué l’histoire du travail par leur action, leur pensée ou leur engagement. Cette section met en lumière des penseurs du travail, des entrepreneurs, des syndicalistes, des ouvriers emblématiques ou encore des figures politiques ayant influencé l’organisation du travail.";
            break;
        case 'Institutions':
            $description = "Le travail ne se développe pas sans cadres normatifs et organisationnels. Ici, sont recensées les institutions qui encadrent, réglementent et transforment le travail : guildes, corporations, syndicats, patronats, ministères du travail, organisations internationales, etc.";
            break;
        case 'Lieux':
            $description = "Le travail se déploie dans des espaces variés, façonnés par les exigences économiques et sociales. Ateliers, usines, bureaux, chantiers, mines, domiciles… Cette section décrit les lieux du travail, leurs évolutions et leurs implications dans l’organisation de l’activité laborieuse.";
            break;
        case 'Pratiques':
            $description = "Les métiers et les techniques de production évoluent au gré des innovations et des transformations sociales. Cette catégorie détaille les manières de travailler, les outils utilisés, les savoir-faire mobilisés, ainsi que les formes d’organisation du travail à travers l’histoire.";
            break;
        case 'Relations':
            $description = "Le travail est indissociable des liens qu’il tisse entre les individus et les groupes sociaux. Ici sont explorées les relations de pouvoir, de solidarité, de subordination ou de coopération qui structurent le monde du travail : hiérarchies, conflits sociaux, négociations, réseaux professionnels, etc.";
            break;
        case 'Temps':
            $description = "Cette catégorie interroge les rythmes du travail, son organisation dans le temps et son évolution historique. On y trouve des notions comme la durée du travail, les congés, la retraite, mais aussi les grandes périodes de mutation du travail, des révolutions industrielles aux transformations numériques contemporaines.";
            break;
        default:
            $description = ""; // Pas de description si la catégorie ne correspond pas
    }

    // Affichage du texte descriptif sous le H1 (uniquement s'il existe)
    if ( !empty( $description ) ) {
        echo '<p class="category-description" style="margin-top: 16px;">' . esc_html($description) . '</p>';
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
                        <?php
                        $pays = get_field('country');
                        $periode = get_field('date');
                        echo esc_html($pays) . ', ' . esc_html($periode); 
                        ?>
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
                                <?php
                                $pays = get_field('country');
                                $periode = get_field('date');
                                echo esc_html($pays) . ', ' . esc_html($periode); 
                                ?>
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
 * Shortcode pour la recherche timeline (plage de dates via ACF)
 */
function timeline_search_shortcode() {
    ob_start();

    // Charger le CSS des cartes
    wp_enqueue_style('search-results-cards', get_template_directory_uri() . '/css/search-results-cards.css');
    
    // Charger le style de la timeline
    wp_enqueue_style('timeline-style', get_template_directory_uri() . '/css/timeline-style.css');

    // Charger jQuery UI Slider et son CSS (exemple via CDN)
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    ?>
    <div id="timeline-search" style="padding: 32px;">
        <label for="slider-range">
            (<span id="timeline-label-min"></span>) - (<span id="timeline-label-max"></span>)
        </label>
        <div id="slider-range" style="margin-top:20px; position: relative;"></div>
        <!-- Conteneur pour la graduation -->
        <div id="slider-graduation" style="position: relative; height: 30px; margin-top: 10px;"></div>
    </div>
    <div id="timeline-results">
        <!-- Les articles correspondant à la période sélectionnée seront chargés ici -->
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Définir les bornes de la timeline
        var periodMin = -500;
        var periodMax = 2025;

        // Initialisation des labels
        $('#timeline-label-min').text(periodMin);
        $('#timeline-label-max').text(periodMax);

        // Initialisation du slider à double poignée
        $("#slider-range").slider({
            range: true,
            min: periodMin,
            max: periodMax,
            values: [ periodMin, periodMax ],
            step: 5,
            slide: function( event, ui ) {
                $('#timeline-label-min').text(ui.values[0]);
                $('#timeline-label-max').text(ui.values[1]);
            },
            change: function( event, ui ) {
                loadTimelinePosts(ui.values[0], ui.values[1]);
            }
        });

        // Génération de la graduation similaire à la seconde timeline
        function generateGraduation() {
            var graduationContainer = $('#slider-graduation');
            graduationContainer.empty();
            // On choisit un pas de 10 pour les marques
            for(var i = periodMin; i <= periodMax; i += 10) {
                var percent = ((i - periodMin) / (periodMax - periodMin)) * 100;
                var mark = $('<div></div>').addClass('slider-mark').css({
                    position: 'absolute',
                    left: percent + '%',
                    bottom: '0',
                    width: '1px',
                    background: '#000',
                    height: '5px'
                });
                if(i % 100 === 0) {
                    mark.addClass('mark-hundred').css('height', '15px');
                    // Créer un label pour les centaines
                    var label = $('<span></span>').addClass('timeline-label').text(i).css({
                        position: 'absolute',
                        top: '-20px',
                        left: '-10px',
                        fontSize: '12px'
                    });
                    mark.append(label);
                } else if(i % 50 === 0) {
                    mark.addClass('mark-half').css('height', '10px');
                }
                graduationContainer.append(mark);
            }
        }
        generateGraduation();

        // Fonction qui effectue la requête Ajax pour charger les articles
        function loadTimelinePosts(minVal, maxVal) {
            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                type: "POST",
                data: {
                    action: "timeline_search_action",
                    period_min: minVal,
                    period_max: maxVal
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
        
        // Chargement initial avec l'intervalle complet
        loadTimelinePosts(periodMin, periodMax);
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('timeline_search', 'timeline_search_shortcode');

function timeline_search_ajax_handler() {
    $period_min = isset($_POST['period_min']) ? intval($_POST['period_min']) : -500;
    $period_max = isset($_POST['period_max']) ? intval($_POST['period_max']) : 2025;

    // Requête sur le champ ACF "date"
    $args = array(
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key'     => 'date',
                'value'   => array( $period_min, $period_max ),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC'
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
                    <p class="card-date"><?php 
                    $pays = get_field('country');
                    $periode = get_field('date');
                    echo esc_html($pays) . ', ' . esc_html($periode); 
                    ?>
                    </p>
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
    $reading_time = ceil($word_count / 200);

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

function display_country_period_shortcode() {
    $pays = get_field('country');
    $periode = get_field('date');
    return '<p>' . esc_html($pays) . ', ' . esc_html($periode) . '</p>';
}
add_shortcode('country_period', 'display_country_period_shortcode');

function um_set_display_name_after_registration( $user_id ) {
    // Récupérer les données de l'utilisateur
    $user = get_userdata( $user_id );
    
    // Récupérez ici les champs souhaités. Par exemple, si vous stockez le prénom et le nom dans les meta "first_name" et "last_name" :
    $first_name = get_user_meta( $user_id, 'first_name', true );
    $last_name  = get_user_meta( $user_id, 'last_name', true );

    // Vous pouvez aussi utiliser d'autres champs du formulaire Ultimate Member selon votre configuration.
    if ( !empty( $first_name ) && !empty( $last_name ) ) {
        $display_name = $first_name . ' ' . $last_name;
    } else {
        // En cas d'absence de ces données, on peut utiliser le login
        $display_name = $user->user_login;
    }

    // Mise à jour du nom affiché
    wp_update_user( array(
        'ID'           => $user_id,
        'display_name' => $display_name,
    ));
}
add_action( 'um_registration_complete', 'um_set_display_name_after_registration', 10, 1 );

function alphabetical_authors_shortcode() {
    // Récupérer tous les utilisateurs avec le rôle 'author', triés par nom affiché
    $args = array(
        'role'    => 'author',
        'orderby' => 'display_name',
        'order'   => 'ASC'
    );
    $users = get_users($args);

    $authors_by_letter = array();
    $letters = range('A', 'Z');
    $special_characters = '#';

    // Initialiser le tableau avec toutes les lettres et la catégorie spéciale
    foreach ($letters as $letter) {
        $authors_by_letter[$letter] = array();
    }
    $authors_by_letter[$special_characters] = array();

    // Parcourir les utilisateurs et les regrouper par la première lettre de leur nom affiché
    foreach ($users as $user) {
        $first_letter = strtoupper(mb_substr($user->display_name, 0, 1));

        // Si la première lettre n'est pas une lettre de A à Z, la placer dans la catégorie spéciale "#"
        if (!preg_match('/[A-Z]/', $first_letter)) {
            $first_letter = $special_characters;
        }

        $authors_by_letter[$first_letter][] = '<li><a class="links-dark" href="' . esc_url( get_author_posts_url($user->ID) ) . '">' . esc_html($user->display_name) . '</a></li>';
    }

    // Création du menu d'index
    $output = '<div class="alphabet-index">';
    $output .= '<a class="links-dark-underline" href="#special">#</a> '; // Bouton "#"
    foreach ($letters as $letter) {
        if (!empty($authors_by_letter[$letter])) {
            $output .= '<a class="links-dark-underline" href="#' . $letter . '">' . $letter . '</a> ';
        } else {
            $output .= '<span class="inactive">' . $letter . '</span> ';
        }
    }
    $output .= '</div>';

    // Création du contenu du dictionnaire d'auteurs
    $output .= '<div class="alphabetical-index">';
    foreach ($authors_by_letter as $letter => $authors) {
        if (!empty($authors)) {
            $section_id = ($letter == $special_characters) ? 'special' : $letter;
            $output .= '<h2 id="' . $section_id . '">' . $letter . '</h2><ul>' . implode('', $authors) . '</ul>';
        }
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('alphabetical_authors', 'alphabetical_authors_shortcode');

function add_reading_time_to_title($title, $id = null) {
    // S'exécute uniquement sur les articles (post) en page unique et dans la boucle principale
    if ( is_singular('post') && in_the_loop() && is_main_query() ) {
        global $post;
        // Vérifie que l'ID correspond bien à celui du post actuel
        if ($id == $post->ID) {
            // Récupération du contenu de l'article et comptage des mots
            $content = strip_tags($post->post_content);
            $word_count = str_word_count($content);
            if ($word_count > 0) {
                // Calcul du temps de lecture en secondes
                $total_seconds = ceil($word_count / 400 * 60);
                $minutes = floor($total_seconds / 60);
                $seconds = $total_seconds % 60;

                // Construction du texte du temps de lecture
                if ($minutes > 0 && $seconds > 0) {
                    $reading_time_str = $minutes . ' min ' . $seconds . ' secs';
                } elseif ($minutes > 0) {
                    $reading_time_str = $minutes . ' min';
                } else {
                    $reading_time_str = $seconds . ' secs';
                }

                // Ajout du temps de lecture au titre sous forme d'annotation
                $title .= '<p class="reading-time"> (' . $reading_time_str . ') </p>';
            }
        }
    }
    return $title;
}
add_filter('the_title', 'add_reading_time_to_title', 10, 2);

function custom_language_switcher_redirect_script() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function(){
        // Sélectionne tous les liens du language switcher
        var lsLinks = document.querySelectorAll("#trp-floater-ls-language-list a");
        lsLinks.forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                // Récupère la langue sélectionnée (en minuscule)
                var selectedLang = this.innerText.trim().toLowerCase();
                var origin = window.location.origin;
                var pathname = window.location.pathname; // ex: "/DicoTrav/it/dictionnaire" ou "/DicoTrav/dictionnaire" ou "/DicoTrav/en/dictionnaire"
                
                // Découpe le chemin en segments
                var segments = pathname.split('/');
                // segments[0] est vide, segments[1] doit contenir "DicoTrav"
                var baseFolder = "DicoTrav";
                if (segments[1].toLowerCase() !== baseFolder.toLowerCase()) {
                    // Si on n'est pas dans le dossier attendu, on ne change rien
                    window.location.href = origin + pathname + window.location.search + window.location.hash;
                    return;
                }
                
                // Liste des codes de langue potentiels
                var langCodes = ["fr", "it", "en"];
                
                // La partie langue potentielle se trouve dans segments[2]
                // Pour IT : si segments[2] n'est pas "it", on l'insère ou le remplace par "it"
                if (selectedLang === "it") {
                    if (segments.length > 2 && langCodes.includes(segments[2].toLowerCase())) {
                        if (segments[2].toLowerCase() !== "it") {
                            segments[2] = "it";
                        }
                    } else {
                        // Si aucun segment langue n'est présent, insère "it" à l'index 2
                        segments.splice(2, 0, "it");
                    }
                }
                // Pour FR : si segments[2] existe et est un code langue, le retirer pour revenir à la version par défaut
                else if (selectedLang === "fr") {
                    if (segments.length > 2 && langCodes.includes(segments[2].toLowerCase())) {
                        segments.splice(2, 1);
                    }
                }
                
                // Reconstruire le chemin
                var newPath = segments.join('/');
                if (newPath.charAt(0) !== "/") {
                    newPath = "/" + newPath;
                }
                // Rediriger en conservant la query string et le fragment
                window.location.href = origin + newPath + window.location.search + window.location.hash;
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'custom_language_switcher_redirect_script');

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
    // Enqueue jQuery UI Slider et son CSS
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    // Enqueue également tes assets Leaflet et ton CSS custom pour la timeline
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', array(), null, true);
    wp_enqueue_style('timeline-leaflet-style', get_template_directory_uri() . '/css/timeline-leaflet-style.css');

    ob_start();
    ?>
    <!-- La carte occupe toute la largeur et se positionne en dessous du header -->
    <div id="map"></div>

    <!-- Conteneur de la timeline avec sélection de période (début et fin) -->
    <div id="timeline" style="padding: 32px;">
        <div id="label-panel">
            <label for="timeline-input-min">De : </label>
            <input type="text" id="timeline-input-min" placeholder="Année début" style="width:80px; margin-left:10px;" />
            <label class="margin-label" for="timeline-input-max">à : </label>
            <input type="text" id="timeline-input-max" placeholder="Année fin" style="width:80px; margin-left:10px;" />
        </div>
        <div id="slider-range" style="margin-top:20px; position: relative;"></div>
        <!-- Conteneur pour la graduation (en arrière-plan, pointer-events désactivés) -->
        <div id="slider-graduation" style="position: relative; height: 30px; margin-top: 10px;"></div>
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
            marker.on('click', function() {
                console.log("Marker cliqué pour " + item.country);
                loadArticlesForCountry(item.country);
            });
            markerByCountry[item.country] = marker;
        });

        // Référence aux éléments des champs de texte et du slider
        var timelineInputMin = document.getElementById('timeline-input-min');
        var timelineInputMax = document.getElementById('timeline-input-max');
        var sliderRange = document.getElementById('slider-range');
        var periodMin = -500;
        var periodMax = 2025;

        // Initialiser les valeurs des inputs
        timelineInputMin.value = periodMin;
        timelineInputMax.value = periodMax;

        // Déclaration de la variable pour le debounce des compteurs
        var markerUpdateTimeout;

        // Initialisation du slider à double poignée via jQuery UI Slider
        jQuery("#slider-range").slider({
            range: true,
            min: periodMin,
            max: periodMax,
            values: [ periodMin, periodMax ],
            step: 5,
            slide: function( event, ui ) {
                timelineInputMin.value = ui.values[0];
                timelineInputMax.value = ui.values[1];
            },
            change: function( event, ui ) {
                loadArticlesForPeriod(ui.values[0], ui.values[1]);
                updateMarkersCount(ui.values[0], ui.values[1]);
            }
        });

        // Gestion des événements sur les zones de texte : au clic sur Entrée, mettre à jour le slider
        timelineInputMin.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var minVal = parseInt(this.value, 10);
                var maxVal = parseInt(timelineInputMax.value, 10);
                jQuery("#slider-range").slider("values", 0, minVal);
            }
        });
        timelineInputMax.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var minVal = parseInt(timelineInputMin.value, 10);
                var maxVal = parseInt(this.value, 10);
                jQuery("#slider-range").slider("values", 1, maxVal);
            }
        });

        // Génération dynamique de la graduation avec affichage des centaines en dessous
        var graduationContainer = document.getElementById('slider-graduation');
        for(var i = periodMin; i <= periodMax; i += 10) {
            var percent = ((i - periodMin) / (periodMax - periodMin)) * 100;
            var mark = document.createElement('div');
            mark.classList.add('slider-mark');
            mark.style.left = percent + '%';
            mark.style.position = 'absolute';
            mark.style.bottom = '0';
            mark.style.width = '1px';
            mark.style.background = '#000';
            mark.style.height = '5px';
            if(i % 100 === 0) {
                mark.classList.add('mark-hundred');
                mark.style.height = '15px';
                // Créer un label pour les centaines
                var label = document.createElement('span');
                label.classList.add('timeline-label');
                label.innerText = i;
                label.style.position = 'absolute';
                label.style.top = '-20px';
                label.style.left = '-10px';
                label.style.fontSize = '12px';
                mark.appendChild(label);
            } else if(i % 50 === 0) {
                mark.classList.add('mark-half');
                mark.style.height = '10px';
            } else {
                mark.classList.add('mark-ten');
            }
            graduationContainer.appendChild(mark);
        }

        // Fonction pour charger les articles pour la période sélectionnée (plage [min, max[)
        function loadArticlesForPeriod(minVal, maxVal) {
            var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
            var xhr = new XMLHttpRequest();
            xhr.open('GET', ajax_url + '?action=get_articles_for_year&year=' + minVal + '&max=' + maxVal, true);
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
                    console.log('Articles pour la période (' + minVal + ' - ' + maxVal + '): ', articles);
                }
            };
            xhr.send();
        }

        // Fonction pour charger et afficher les articles pour un pays et la période sélectionnée
        function loadArticlesForCountry(country) {
            var minVal = timelineInputMin.value;
            var maxVal = timelineInputMax.value;
            var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
            var xhr = new XMLHttpRequest();
            xhr.open('GET', ajax_url + '?action=get_articles_for_country&country=' + encodeURIComponent(country) + '&year=' + minVal + '&max=' + maxVal, true);
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
        function updateMarkersCount(minVal, maxVal) {
            clearTimeout(markerUpdateTimeout);
            markerUpdateTimeout = setTimeout(function() {
                countries.forEach(function(item) {
                    var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', ajax_url + '?action=get_articles_count_for_country&country=' + encodeURIComponent(item.country) + '&year=' + minVal + '&max=' + maxVal, true);
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
        loadArticlesForPeriod(periodMin, periodMax);
        updateMarkersCount(periodMin, periodMax);
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('timeline_leaflet', 'timeline_leaflet_shortcode');


// Fonction AJAX pour récupérer les articles sur une période (plage [min, max])
function get_articles_for_year() {
    if ( isset($_GET['year']) && isset($_GET['max']) ) {
        $min = intval($_GET['year']);
        $max = intval($_GET['max']);
        // Conditions : date >= $min et date < $max (ou utiliser BETWEEN)
        $args = array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key'     => 'date',
                    'value'   => array($min, $max),
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC'
                )
            )
        );
        $query = new WP_Query($args);
        $articles = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
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
add_action('wp_ajax_get_articles_for_year', 'get_articles_for_year');
add_action('wp_ajax_nopriv_get_articles_for_year', 'get_articles_for_year');


// Fonction AJAX pour récupérer les articles pour un pays dans une période donnée
function get_articles_for_country() {
    if ( isset($_GET['country']) && isset($_GET['year']) && isset($_GET['max']) ) {
        $country = sanitize_text_field($_GET['country']);
        $min = intval($_GET['year']);
        $max = intval($_GET['max']);
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
                    'value'   => array($min, $max),
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC'
                )
            ),
            'meta_key'  => 'date',
            'orderby'   => 'meta_value',
            'order'     => 'ASC'
        );
        $query = new WP_Query($args);
        $articles = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
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


// Fonction AJAX pour obtenir le compte d'articles pour un pays dans une période donnée
function get_articles_count_for_country() {
    if ( isset($_GET['country']) && isset($_GET['year']) && isset($_GET['max']) ) {
        $country = sanitize_text_field($_GET['country']);
        $min = intval($_GET['year']);
        $max = intval($_GET['max']);
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
                    'value'   => array($min, $max),
                    'compare' => 'BETWEEN',
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
