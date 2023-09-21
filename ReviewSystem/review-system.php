<?php

if (!defined('ABSPATH')) {
    exit;
}

use GFAPI;

class ReviewSystem {

    private $feedback_form_id;
    private $feedback_form_title;
    private $location_review_links;

    public function __construct() {
        
        $this->__init();

        $location_page = false;
        foreach ( $this->location_review_links as $location ) {
            $slug = $location['slug'];
            $pattern = "/$slug/";
            if ( preg_match( $pattern, strtok( $_SERVER['REQUEST_URI'], '?' ) ) ) {
                $location_page = true;
                add_action( 'wp_footer', array( $this, 'add_modal' ) );
                break;
            }
        }

        if ( $location_page || strstr( $_SERVER['REQUEST_URI'], '/review-us' ) ) {

            if ( isset( $this->feedback_form_id ) ) {
                echo "<script>let feedbackFormId = $this->feedback_form_id;</script>";
            }
            $location_links = json_encode( $this->get_location_review_links() );
            echo "<script>let locationReviewLinks = $location_links;</script>";
        }
    }

    private function __init() {
        $this->set_variables();
        $this->add_hooks();
        $this->add_shortcodes();
    }

    private function set_variables() {
        $this->feedback_form_title = apply_filters('review_system_form_title', 'Review Feedback');
        $this->location_review_links = $this->get_review_locations();
        $this->feedback_form_id = $this->get_feedback_form_id();
    }

    private function add_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueues' ) );
        add_action( 'gform_after_submission', array( $this, 'post_submission_redirect' ), 10, 2 );
    }

    private function add_shortcodes() {
        add_shortcode( 'review_modal', array( $this, 'strategy_review_modal' ) );
        add_shortcode( 'review_content', array( $this, 'strategy_review_content' ) );
    }

    function enqueues() {
        $module_path = get_stylesheet_directory_uri() . '/includes/ReviewSystem';
        $lib_path = $module_path . '/lib';
        wp_enqueue_style( 'review-system-style.css', $lib_path . '/css/style.css' );

        wp_enqueue_script( 'review-system-micromodal.js', $lib_path . '/js/micromodal.js' );
        wp_enqueue_script( 'review-system-scripts.js', $lib_path . '/js/scripts.js', array('jquery') );
    }

    function add_modal() {
        echo do_shortcode('[review_modal]');
    }

    private function get_review_locations() {

        $raw_locations = get_posts(
            array(
                'post_type'     => 'page',
                'posts_status'  => 'publish',
                'numberposts'   => -1,
                'order'			=> 'ASC',
            )
        );

        $locations = array();
        if ( !empty( $raw_locations ) ) {
            foreach ( $raw_locations as $location ) {
                if ( get_field('google_review_link', $location->ID) || get_field('facebook_review_link', $location->ID) || get_field('yelp_review_link', $location->ID) ) {
                    $locations[$location->post_name]['slug'] = $location->post_name;
                    $locations[$location->post_name]['name'] = get_the_title($location->ID);
                }
            }
        }

        return $locations;
    }

    private function get_feedback_form_id() {

        // Check database option
        $id = get_option('review_system_form_id');
        if ( $id && GFAPI::get_form( $id ) ) {
            return $id;
        }

        // Check all forms for title
        $id = $this->get_form_id_by_title( $this->feedback_form_title );
        if ( $id && GFAPI::get_form( $id ) ) {
            update_option('review_system_form_id', $id);
            return $id;
        }

        // Create new form
        $id = $this->create_feedback_form();
        if ( $id && GFAPI::get_form( $id ) ) {
            update_option('review_system_form_id', $id);
            return $id;
        }

        return null;
    }

    private function get_form_id_by_title( $title ) {

        $forms = GFAPI::get_forms();
        foreach ( $forms as $form ) {
            if ( isset( $form['title'] ) && isset( $form['id'] ) && $title == $form['title'] ) {
                return $form['id'];
            }
        }

        return false;
    }

    private function create_feedback_form() {

        $form_data = array(
            'title'     => $this->feedback_form_title,
            'description'   => '',
            'button'    => array(
                'type'  => 'text',
                'text'  => 'Submit',
                'imageUrl'  => ''
            ),
            'fields'    => array(
                array(
                    'label' => 'Location',
                    'type'  => 'hidden',
                    'id'    => 1,
                    'isRequired'    => false,
                ),
                array(
                    'label' => 'Rating',
                    'type'  => 'hidden',
                    'id'    => 2,
                    'isRequired'    => false,
                ),
                array(
                    'label' => 'Your Name',
                    'type'  => 'text',
                    'id'    => 3,
                    'isRequired'    => true,
                ),
                array(
                    'label' => 'Email',
                    'type'  => 'email',
                    'id'    => 4,
                    'isRequired'    => true,
                ),
                array(
                    'label' => 'Feedback',
                    'type'  => 'textarea',
                    'id'    => 5,
                    'isRequired'    => true,
                ),
            ),
        );

        return GFAPI::add_form( $form_data );
    }

    private function get_review_links( $post_id ) {
        return array(
            'google'    => get_field( 'google_review_link', $post_id ),
            'facebook'  => get_field( 'facebook_review_link', $post_id ),
            'yelp'      => get_field( 'yelp_review_link', $post_id ),
        );
    }

    private function get_location_review_links() {
        $location_links = array();
        foreach ( $this->location_review_links as $location ) {
            if ( isset($location['slug']) ) {
                $location_links[$location['slug']] = $this->get_review_links( url_to_postid( home_url() . '/' . $location['slug'] ) );
            }
        }
        return $location_links;
    }

    private function review_vendors() {
        return array(
            'google'    => array(
                'name'  => 'Google',
                'id'    => 'review-google',
                'icon-class'    => 'fa-google',
            ),
            'facebook'  => array(
                'name'  => 'Facebook',
                'id'    => 'review-facebook',
                'icon-class'    => 'fa-facebook-f',
            ),
            'yelp'  => array(
                'name'  => 'Yelp!',
                'id'    => 'review-yelp',
                'icon-class'    => 'fa-yelp',
            ),
        );
    }

    // ===========================
    //         SHORTCODES
    // ===========================

    function strategy_review_modal() {
        ob_start();
        ?>
    
        <div id="review-modal" aria-hidden="true">
            <div class="modal-overlay" tabindex="-1" data-micromodal-close>
                <div id="review-modal--display" role="dialog" aria-modal="true" aria-labelledby="review-modal--title">
                    <header>
                        <h2 id="review-modal__title">
                            Leave a Review
                        </h2>
                        <button aria-label="Close modal" data-micromodal-close>✖</button>
                    </header>
                    <div id="review-modal__content">
                        <?php echo do_shortcode('[review_content]'); ?>
                    </div>
                </div>
            </div>
        </div>
    
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    function strategy_review_content() {
        ob_start();
        ?>
    
        <div id="review-content" class="review-content">
            <div id="review-content__1" class="review-content__page" data-value="1">
                <?php get_template_part('/includes/ReviewSystem/templates/default_content_1', null, array( 'locations' => $this->location_review_links ) ) ?>
            </div>
            <div id="review-content__2" class="review-content__page" data-value="2">
                <?php get_template_part('/includes/ReviewSystem/templates/default_content_2'); ?>
            </div>
            <div id="review-content__3" class="review-content__page" data-value="3">
                <?php get_template_part('/includes/ReviewSystem/templates/default_content_3', null, array( 'vendors' => $this->review_vendors() ) ); ?>
            </div>
            <div id="review-content__4" class="review-content__page" data-value="4">
                <?php get_template_part('/includes/ReviewSystem/templates/default_content_4', null, array( 'form_id' => $this->feedback_form_id ) ); ?>
            </div>
            <div id="review-content__5" class="review-content__page" data-value="5">
                <?php get_template_part('/includes/ReviewSystem/templates/default_content_5'); ?>
            </div>
            <div class="review-content__pagination">
                <a id="review-prev-btn" class="review-content__pagination--prev">Prev</a>
                <a id="review-next-btn" class="review-content__pagination--next">Next</a>
            </div>
        </div>
    
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    
}

$review_system = new ReviewSystem();