<?php
/**
 * Plugin Name: Read Me Later
 * Plugin URI: https://github.com/iamsayed/read-me-later
 * Description: This plugin allow you to add blog posts in read me later lists using Ajax
 * Version: 1.0.0
 * Author: Marko Poikkimäki
 * Author URI: https://github.com/iamsayed/
 * License: GPL3
 */

require(plugin_dir_path( __FILE__ ).'widget.php');


class ReadMeLater {

    /*
    * Action hooks
    */
    public function run() {
        // Enqueue plugin styles and scripts
        add_action('plugins_loaded', array( $this, 'enqueue_rml_scripts' ) );
        add_action('plugins_loaded', array( $this, 'enqueue_rml_styles' ) );

        // Setup filter hook to show Read Me Later link
        add_filter( 'the_excerpt', array( $this, 'rml_button' ) );
        add_filter( 'the_content', array( $this, 'rml_button' ) );

        // Setup Ajax action hook
        add_action( 'wp_ajax_read_me_later', array( $this, 'read_me_later' ) );


    }

    /**
     * Register plugin styles and scripts
     */
    public function register_rml_scripts() {
        wp_register_script( 'rml-script', plugins_url( 'js/read-me-later.js', __FILE__ ), array('jquery'), null, true );
        wp_register_style( 'rml-style', plugins_url( 'css/read-me-later.css' ) );
    }
    /**
     * Enqueues plugin-specific scripts.
     */
    public function enqueue_rml_scripts() {
        wp_enqueue_script( 'rml-script' );

        $admin_url = admin_url('admin-ajax.php');
        $check_nonce = wp_create_nonce('rml-nonce');
        // echo var_dump($admin_url);
        //  echo var_dump($check_nonce);

        wp_localize_script('rml-script', 'readmelater_ajax', array( 'ajax_url' => $admin_url, 'check_nonce' => $check_nonce) );
    }

    /**
     * Enqueues plugin-specific styles.
     */
    public function enqueue_rml_styles() {
        wp_enqueue_style( 'rml-style' );
    }


    /**
     * Adds a read me later button at the bottom of each post excerpt that allows logged in users to save those posts in their read me later list.
     *
     * @param string $content
     * @returns string
     */
    public function rml_button( $content ) {
        // Show read me later link only when the user is logged in
       // echo var_dump(is_user_logged_in());
       // echo var_dump(get_post_type());

        if( is_user_logged_in() && get_post_type() == 'post') {
            $html = '<a href="#" class="rml_bttn" data-id="' . get_the_id() . '">Read Me Later</a>';
            $content .= $html;
        }

        return $content;
    }

    public function read_me_later() {

        check_ajax_referer( 'rml-nonce', 'security' );
        $rml_post_id = $_POST['post_id'];
        $echo = array();
        if(get_user_meta( wp_get_current_user()->ID, 'rml_post_ids', true ) !== null ) {
            $value = get_user_meta( wp_get_current_user()->ID, 'rml_post_ids', true );
        }

        if( $value ) {
            $echo = $value;
            array_push( $echo, $rml_post_id );
        }
        else {
            $echo = array( $rml_post_id );
        }

        update_user_meta( wp_get_current_user()->ID, 'rml_post_ids', $echo );
        $ids = get_user_meta( wp_get_current_user()->ID, 'rml_post_ids', true );


        // Query read me later posts
        $args = array(
            'post_type' => 'post',
            'orderby' => 'DESC',
            'posts_per_page' => -1,
            'numberposts' => -1,
            'post__in' => $ids
        );

        $rmlposts = get_posts( $args );
        if( $ids ) :
            global $post;
            foreach ( $rmlposts as $post ) :
                setup_postdata( $post );
                $img = wp_get_attachment_image_src( get_post_thumbnail_id() );
                ?>
                <div class="rml_posts">
                    <div class="rml_post_content">
                        <h5><a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a></h5>
                        <p><?php the_excerpt(); ?></p>
                    </div>
                    <img src="<?php echo $img[0]; ?>" alt="<?php echo get_the_title(); ?>" class="rml_img">
                </div>
                <?php
            endforeach;
            wp_reset_postdata();
        endif;

        // Always die in functions echoing Ajax content
        die();

    }

}
// end class


// init
$rml = new ReadMeLater();
$rml->register_rml_scripts();
$rml->run();
