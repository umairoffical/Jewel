<?php
global $homey_local;
$terms_conditions = homey_option('login_terms_condition');
$register_image = homey_option('register_image', false, 'url' );
$register_text = esc_html__(homey_option('register_text'), 'homey');
$phone_number = homey_option('enable_phone_number', 'yes');
$facebook_login = homey_option('facebook_login');
$google_login = homey_option('google_login');
$show_roles = homey_option('show_roles');
$enable_password = homey_option('enable_password');
$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_prefix_text = homey_option('forms_gdpr_prefix_text');
$forms_gdpr_text = homey_option('forms_gdpr_text');
$forms_gdpr_href_link = homey_option('forms_gdpr_href_link');
?>
<div class="modal fade custom-modal-login" id="modal-register" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">
        <?php if(!empty($register_image)) { ?>
        <div class="modal-body-left pull-left" style="background-image: url(<?php echo esc_url($register_image); ?>); background-size: cover; background-repeat: no-repeat; background-position: 50% 50%;">
            <div class="login-register-title">
                <?php echo esc_attr($register_text); ?>
            </div>
        </div>
        <?php } ?>

        <div class="modal-body-right pull-right">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo esc_html__('Register', 'homey'); ?></h4>
                </div>

                <?php if( get_option('users_can_register') ) { ?>
                <div class="modal-body">

                    <div class="homey_register_messages homey_login_messages message"></div>

                    <?php if($facebook_login == 'yes') { ?>
                    <button type="button" class="homey-facebook-login btn btn-facebook-lined btn-full-width"><i class="homey-icon homey-icon-social-media-facebook" aria-hidden="true"></i> <?php echo esc_html__('Login with Facebook', 'homey'); ?></button>
                    <?php } ?>
                    
                    <?php if($google_login == 'yes') { ?>
                        <button class="gsi-material-button homey-google-login btn btn-google-lined btn-full-width">
                            <div class="gsi-material-button-state"></div>
                            <div class="gsi-material-button-content-wrapper">
                                <div class="gsi-material-button-icon">
                                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                                        <path fill="none" d="M0 0h48v48H0z"></path>
                                    </svg>
                                </div>
                                <span class="gsi-material-button-contents"><?php echo esc_html__("Sign in with Google", 'homey'); ?></span>
                            </div>
                        </button>
                    <?php } ?>

                    <div class="modal-login-form">
                        <?php if($facebook_login == 'yes' || $google_login == 'yes') { ?>
                        <p class="text-center"><strong><?php echo esc_html__('Register with your email', 'homey'); ?></strong></p>
                        <?php } ?>
                        <form class="homey_modal_register_form_child">
                            <div class="form-group">
                                <input name="username" type="text" class="form-control email-input-1" placeholder="<?php esc_attr_e('Name','homey'); ?>" />
                            </div>

                            <?php if($phone_number == 'yes') { ?>
                                <div class="form-group">
                                    <input pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" name="reg_form_phone_number" id="reg_form_phone_number" type="tel" class="form-control email-input-1" placeholder="<?php esc_attr_e('Phone Number','homey'); ?>" />
                                    <input name="iti__selected_flag" id="iti__selected_flag" type="hidden" value="" />
                                </div>
                            <?php } ?>

                            <div class="form-group">
                                <input type="email" name="useremail" class="form-control email-input-1" placeholder="<?php echo esc_attr__('Email', 'homey'); ?>">
                            </div>

                            <?php if( $enable_password == 'yes' ) { ?>
                            <div class="form-group">
                                <input type="password" name="register_pass" class="form-control password-input-1" placeholder="<?php echo esc_attr__('Password', 'homey'); ?>">
                            </div>
                            <div class="form-group">
                                <input type="password" name="register_pass_retype" class="form-control password-input-2" placeholder="<?php echo esc_attr__('Repeat Password', 'homey'); ?>">
                            </div>
                            <?php } ?>

                            <?php if($show_roles) { ?>
                            <select name="role" class="selectpicker" title="<?php echo esc_attr__('Select', 'homey'); ?>">
                                <option value=""><?php echo esc_attr__('Select', 'homey'); ?></option>
                                <option value="homey_renter"><?php echo esc_attr__(homey_option('renter_role'), 'homey'); ?></option>
                                <option value="homey_host"><?php echo esc_attr__(homey_option('host_role'), 'homey'); ?></option>
                            </select>
                            <?php } ?>

                            <input type="hidden" name="role" value="homey_renter">

                            <?php if(homey_show_google_reCaptcha()){ ?>
                            <div class="bootstrap-select">
                                <?php get_template_part('template-parts/google', 'reCaptcha'); ?>
                            </div>
                            <?php } ?>

                            <div class="checkbox">
                                <label>
                                    <input required name="term_condition" type="checkbox"> <?php echo sprintf( wp_kses(__( 'I agree with your <a href="%s">Terms & Conditions</a>', 'homey' ), homey_allowed_html()), 'https://share.google/sm9V87V0Gkmo6XSu3' ); ?>
                                </label>
                            </div>

                            <?php if($enable_forms_gdpr != 0) { ?>
                            <div class="checkbox">
                                <label>
                                    <input name="privacy_policy" type="checkbox">
                                    <?php echo $forms_gdpr_prefix_text.' <a href="'.$forms_gdpr_href_link.'" title="'.$forms_gdpr_text.'">'.$forms_gdpr_text.'</a>'; ?>
                                </label>
                            </div>
                            <?php } ?>

                            <?php wp_nonce_field( 'homey_register_nonce', 'homey_register_security' ); ?>
                            <input type="hidden" name="action" value="homey_register_child_modal">
                            <button type="submit" class="homey-register-button btn btn-primary btn-full-width"><?php echo esc_html__('Register', 'homey'); ?></button>
                        </form>
                        <p class="text-center"><?php echo esc_html__('Do you already have an account?', 'homey'); ?> <a href="#" data-toggle="modal" data-target="#modal-login" data-dismiss="modal"><?php echo esc_html__('Log In', 'homey'); ?></a></p>
                    </div>
                </div><!-- /.modal-content -->
                <?php } else {
                    echo '<div class="modal-body">';
                    esc_html_e('User registration is disabled in this website.', 'homey');
                    echo '</div>';
                } ?>
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->