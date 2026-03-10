<?php
if(!function_exists('homey_child_register_module')) {
    function homey_child_register_module($atts, $content = null) {
    
        extract(shortcode_atts(array(
            'register_title' => ''
        ), $atts));

        $terms_conditions = homey_option('login_terms_condition');
        $enable_password = homey_option('enable_password');
        $enable_forms_gdpr = homey_option('enable_forms_gdpr');

        $forms_gdpr_prefix_text = homey_option('forms_gdpr_prefix_text');
        $forms_gdpr_text = homey_option('forms_gdpr_text');
        $forms_gdpr_href_link = homey_option('forms_gdpr_href_link');

        ob_start();?>
        <div class="register_module_wrap">
            <?php if(is_user_logged_in()){ ?>
                <p><?php echo esc_html__('You are logged in, explore the website', 'homey-core');?></p>
            <?php }else{ ?>


                <div class="homey_register_messages message"></div>

                <h2><?php echo esc_html__(ucfirst($register_title), 'homey'); ?></h2>
                <div class="modal-login-form">

                    <form>
                        <div class="form-group">
                            <input name="username" type="text" class="form-control email-input-1" placeholder="<?php esc_html_e('Username','homey'); ?>" />
                        </div>
                        <div class="form-group">
                            <input type="useremail" name="useremail" class="form-control email-input-1" placeholder="<?php echo esc_html__('Email', 'homey'); ?>">
                        </div>
                        <div class="form-group">
                            <input type="phone" name="phone" class="form-control email-input-1 email-widget-field" placeholder="<?php echo esc_html__('Phone Number', 'homey'); ?>" style="border-radius: 0px;">
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="role" value="homey_host">
                        </div>

                        <?php if( $enable_password == 'yes' ) { ?>
                            <div class="form-group">
                                <input type="password" name="register_pass" class="form-control password-input-1" placeholder="<?php echo esc_html__('Password', 'homey'); ?>">
                            </div>
                            <div class="form-group">
                                <input type="password" name="register_pass_retype" class="form-control password-input-2" placeholder="<?php echo esc_html__('Repeat Password', 'homey'); ?>">
                            </div>
                        <?php } ?>

                        <?php get_template_part('template-parts/google', 'reCaptcha'); ?>

                        <div class="checkbox pull-left term_condition_check">
                            <label>
                                <input required name="term_condition" type="checkbox"> <?php echo sprintf( wp_kses(__( 'I agree with your <a href="%s">Terms & Conditions</a>', 'homey' ), homey_allowed_html()), 'https://intercom.help/locationjewel/en/articles/9376751-terms-of-service' ); ?>
                            </label>
                        </div>
                        <?php if($enable_forms_gdpr != 0) { ?>
                            <div class="checkbox pull-left privacy_policy_check">
                                <label>
                                    &nbsp;<input name="privacy_policy" type="checkbox">
                                    <?php echo $forms_gdpr_prefix_text.'<a href="'.$forms_gdpr_href_link.'" title="'.$forms_gdpr_text.'">'.$forms_gdpr_text.'</a>'; ?>
                                </label>
                            </div>
                        <?php } ?>
                        <?php wp_nonce_field( 'homey_register_nonce', 'homey_register_security' ); ?>
                        <input type="hidden" name="action" value="homey_register_child">
                        <input type="hidden" name="role" value="homey_host">
                        <button type="submit" class="homey-register-button btn btn-primary btn-full-width"><?php echo esc_html__('Register', 'homey'); ?></button>
                    </form>
                </div>
            <?php } ?>
        </div><!-- /.modal-content -->

        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }
}


/*-----------------------------------------------------------------------------------*/
// Register
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_register_child', 'homey_register_child' );
add_action( 'wp_ajax_homey_register_child', 'homey_register_child' );

if( !function_exists('homey_register_child') ) {
    function homey_register_child() {
        //$local = homey_get_localization();

        check_ajax_referer('homey_register_nonce', 'homey_register_security');

        $allowed_html = array();
        homey_google_recaptcha_callback();

        $usermane          = trim( sanitize_text_field( wp_kses( $_POST['username'], $allowed_html ) ));
        $email             = trim( sanitize_text_field( wp_kses( $_POST['useremail'], $allowed_html ) ));
        $term_condition    = wp_kses( $_POST['term_condition'], $allowed_html );
        $enable_password = homey_option('enable_password');
        $phone = trim( sanitize_text_field( wp_kses( $_POST['phone'], $allowed_html ) ));

        $response = isset($_POST["g-recaptcha-response"])?$_POST["g-recaptcha-response"]:'';

        $user_role = get_option( 'default_role' );

        if( $user_role == 'administrator' ) {
            $user_role = 'subscriber';
        }

        if( isset( $_POST['role'] ) && $_POST['role'] != '' ){
            $user_role = isset( $_POST['role'] ) ? sanitize_text_field( wp_kses( $_POST['role'], $allowed_html ) ) : $user_role;
        } else {
            $user_role = $user_role;
        }

        if( get_option('users_can_register') != 1 ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Access denied.', 'homey-login-register') ) );
            wp_die();
        }

        $term_condition = ( $term_condition == 'on') ? true : false;

        if( !$term_condition ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('You need to agree with terms & conditions.', 'homey-login-register') ) );
            wp_die();
        }

        if( empty( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The username field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( strlen( $usermane ) < 3 ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Minimum 3 characters required', 'homey-login-register') ) );
            wp_die();
        }
        if (preg_match("/^[0-9A-Za-z_]+$/", $usermane) == 0) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username (do not use special characters or spaces)!', 'homey-login-register') ) );
            wp_die();
        }
        if( username_exists( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This username is already registered.', 'homey-login-register') ) );
            wp_die();
        }
        if( empty( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The email field is empty.', 'homey-login-register') ) );
            wp_die();
        }

        if( email_exists( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This email address is already registered.', 'homey-login-register') ) );
            wp_die();
        }

        if( !is_email( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid email address.', 'homey-login-register') ) );
            wp_die();
        }

        if( empty( $phone ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The phone number field is empty.', 'homey-login-register') ) );
            wp_die();
        }

        if( $enable_password == 'yes' ){
            $user_pass         = trim( sanitize_text_field(wp_kses( $_POST['register_pass'] ,$allowed_html) ) );
            $user_pass_retype  = trim( sanitize_text_field(wp_kses( $_POST['register_pass_retype'] ,$allowed_html) ) );

            if ($user_pass == '' || $user_pass_retype == '' ) {
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('One of the password field is empty!', 'homey-login-register') ) );
                wp_die();
            }

            if ($user_pass !== $user_pass_retype ){
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('Passwords do not match', 'homey-login-register') ) );
                wp_die();
            }
        }

        $enable_forms_gdpr = homey_option('enable_forms_gdpr');

        if( $enable_forms_gdpr != 0 ) {
            $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
            if ( empty($privacy_policy) ) {
                echo json_encode(array(
                    'success' => false,
                    'msg' => homey_option('forms_gdpr_validation')
                ));
                wp_die();
            }
        }

        if($enable_password == 'yes' ) {
            $user_password = $user_pass;
        } else {
            $user_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        }
        $user_id = wp_create_user( $usermane, $user_password, $email );

        if ( is_wp_error($user_id) ) {
            echo json_encode( array( 'success' => false, 'msg' => $user_id ) );
            wp_die();
        } else {

            wp_update_user( array( 'ID' => $user_id, 'role' => $user_role ) );

            update_user_meta( $user_id, 'phone', $phone );

            if( $enable_password =='yes' ) {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('Your account was created and you can login now!', 'homey-login-register') ) );
            } else {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('Registration complete. Please check your email!', 'homey-login-register') ) );
            }
            homey_wp_new_user_notification( $user_id, $user_password );
        }
        wp_die();

    }
}
?>