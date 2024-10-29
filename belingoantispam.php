<?php

/**
 * Plugin Name: Belingo.AntiSpam
 * Description: Simple spam protection for WordPress
 * Author URI:  https://belingo.ru
 * Author:      Belingo llc
 * Version:     1.2.4
 * Text Domain: belingoantispam
 * Domain Path: /languages
 */

define("BELINGO_ANTISPAM_VERSION", '1.2.4');

function belingoAntiSpam_scripts_styles() {
    wp_enqueue_script('belingoantispam-cookie', plugins_url('/js/jquery.cookie.js', __FILE__), array(), BELINGO_ANTISPAM_VERSION, true);
    wp_enqueue_script('belingoantispam', plugins_url('js/belingoantispam.js', __FILE__), array(), BELINGO_ANTISPAM_VERSION, true);
}
add_action('wp_enqueue_scripts', 'belingoAntiSpam_scripts_styles');

add_action('wpcf7_before_send_mail', 'belingoAntiSpam_wpcf7_before_send_mail', 10, 3);
function belingoAntiSpam_wpcf7_before_send_mail( $contact_form, &$abort, $that ) {

    if(!isset($_COOKIE['BAS_test_mousemoved'])) {
        $abort = true;
        $mail = $contact_form->prop( 'mail' );
        $tags = [];
        foreach((array)$contact_form->scan_form_tags() as $tag) {
            if($tag->type == 'text' || $tag->type == 'text*') {
                $tags[] = $tag->name;
            }
        }
        $post_title = '';
        foreach($that->get_posted_data() as $key => $value) {
            if(in_array($key, $tags)) {
                $post_title .= $value.' ';
            }
        }
        $id = wp_insert_post(array(
          'post_title'=>sanitize_text_field($post_title),
          'post_type'=>'bas_blocked_cf7',
          'post_content'=>wpcf7_mail_replace_tags( $mail['body'] )
        ));
    }

}

add_filter( 'wp_pre_insert_user_data', 'belingoAntiSpam_pre_insert_user_data_filter', 10, 4 );

function belingoAntiSpam_pre_insert_user_data_filter( $data, $update, $user_id, $userdata ){

    if(!isset($_COOKIE['BAS_test_mousemoved']) && !is_admin()) {
        $data = false;
    }
    return $data;
}

add_filter( 'pre_comment_approved', 'belingoAntiSpam_pre_comment_approved_filter', 10, 2 );

function belingoAntiSpam_pre_comment_approved_filter( $approved, $commentdata ){

    $approved = 0;

    return $approved;
}

add_action('wp_insert_comment', 'belingoAntiSpam_wp_insert_comment', 10, 2);

function belingoAntiSpam_wp_insert_comment($id, $comment) {

    if(isset($_COOKIE['BAS_test_mousemoved'])) {
        if(!get_option('comment_moderation')) {
            wp_set_comment_status($id, 'approve');
        }
    }else{
        wp_set_comment_status($id, 'spam');
    }

}

add_action( 'init', 'belingoAntiSpam_register_post_types' );

function belingoAntiSpam_register_post_types(){

    register_post_type( 'bas_blocked_cf7', [
        'label'  => __('Blocked applications CF7'),
        'description'            => '',
        'public'                 => false,
        'show_ui'             => true,
        'show_in_menu'           => true,
        'hierarchical'        => false,
        'supports'            => [ 'title', 'editor' ],
        'taxonomies'          => [],
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => false,
    ] );

}

?>