<?php
/**
 * Plugin Name: My Plugin
 * Description: Plugin tutorial
 * Author: GiaBaoo
 * Author: URI
 * Version: 1.0.0
 * Text Domain: simple-contact-form
 * 
 */

if( !defined('ABSPATH'))
{
    echo 'What are you trying to do?';
    exit;
}

class  SimpleContactForm{


    public function __construct()
    {
        //create custom post type
        add_action('init', array($this, 'create_custom_post_type')); 

        //add assets (js,css,etc)
        add_action('wp_enqueue_scripts',array($this, 'load_assets'));

        //add shortcode 
        add_shortcode('contact-form', array($this, 'load_shortcode'));

        //add Javascript
        add_action('wp_footer', array($this, 'load_scripts'));

        //add Javasp 
        add_action('rest_api_init', array($this, 'register_rest_api'));


    }//end __construct
    
    public function create_custom_post_type()
    {
        //echo "<script>alert('IT LOADED')</script>";
        $agrs = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                    'name' =>  'Contact Form',
                    'singular_name' => 'Contact Form Entry'
            ),
            'menu_icon' => 'dashicons-media-text',

        );
        register_post_type('simple-contact-form', $agrs);
    }//end create_custom_post_type



    public function load_assets()
    {
        wp_enqueue_style(
            'simple-contact-form',
            plugin_dir_url( __FILE__ ) . 'css/simple-contact-form.css',
            array(),
            1,
            'all'   
        );
        wp_enqueue_script(
            'simple-contact-form',
            plugin_dir_url( __FILE__ ) . 'js/simple-contact-form.js',
            array('jquery'),
            1,
            true
        );
        /**<?php echo get_template_directory_uri(); ?>/ */
    }//end load_assets
    public function load_shortcode()
    {?>
        <div class = "simple-contact-form">
        <h1>send us an email</h1>
        <p>Please fill the below form</p>
        <form id = "simple-contact-form_form"action="">
            <div class = "form-group mb-2">
            <input name="name"type = "text" placeholder= "Name" class = "form-control">
            </div>
            <div class = "form-group mb-2">
            <input name="email" type = "email" placeholder= "Email" class = "form-control">
            </div>
            <div class = "form-group mb-2">
            <input name="phone" type = "tel" placeholder= "Phone" class = "form-control">
            </div>
            <div class = "form-group mb-2">
            <textarea name="massage" placeholder= "Type your message" class = "form-control"></textarea>
            </div>
            <div class = "form-group mb-2">
            <button class ="btn btn-success btn-block">Send massage</button>
            </div>
            
        </form>
        </div>
    <?php }//end load_shortcode


        

    public function load_scripts()
    {?>

        <script>
            var nonce = '<?php echo wp_create_nonce('wp_rest');  ?>';
            (function($){
                $('#simple-contact-form_form').submit(function(event){
                    event.preventDefault();


                    var form = $(this ).serialize();

                    console.log(form);
                    $.ajax({
                        method:'post',
                        url:'<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');?>',
                        headers: { 'X-WP-Nonce': nonce},
                        data: form
                    })
             });
            })(jQuery)
             
        </script>
    <?php }
    //end load_scripts


    public function register_rest_api(){


        register_rest_route( 'simple-contact-form/v1',
        'send-email',
        array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form')
        ));

    }
    //end register_rest_api

    
    public function handle_contact_form($data){
        $headers = $data -> get_headers();
        $params = $data -> get_params();
        $nonce = $headers['x_wp_nonce'][0];

        if(!wp_verify_nonce($nonce,'wp_rest')){
           return new WP_REST_Response('message not sent',442);
        }
        $post_id = wp_insert_post([
            'post_type' => 'simple_contact_update_form',
            'post_title' => 'Contact enquiry',
            'post_status' => 'publish',
        ]);
        if($post_id){
            return new WP_REST_Response('Thank you for your email address',200);
        }
        
        // echo 'Hello, i am working';
    }

}
new SimpleContactForm;