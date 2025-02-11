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

