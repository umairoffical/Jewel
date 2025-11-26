<?php
global $homey_local;
$nav_register = homey_option('nav_register');
$login_image = homey_option('login_image', false, 'url' );
$login_text = esc_html__(homey_option('login_text'), 'homey');
$facebook_login = homey_option('facebook_login');
$google_login = homey_option('google_login');
$login_as_normal_form = homey_option('login_as_normal_form');
?>
<div class="modal fade custom-modal-login" id="modal-login" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">
        
        <?php if(!empty($login_image)) { ?>
        <div class="modal-body-left pull-left" style="background-image: url(<?php echo esc_url($login_image); ?>); background-size: cover; background-repeat: no-repeat; background-position: 50% 50%;">
            <div class="login-register-title">
                <?php echo esc_attr($login_text); ?>
            </div>
        </div>
        <?php } ?>

        <div class="modal-body-right pull-right">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo esc_html__('Login', 'homey'); ?></h4>
                </div>
                <div class="modal-body">

                    <div class="homey_login_messages message" <?php if($login_as_normal_form == "yes" && isset($_GET['no-login-err'])){ echo 'style="color: #f58d9d;"'; }?>><?php if($login_as_normal_form == "yes" && isset($_GET['no-login-err'])){ echo esc_html__('Check your credentials or contact to the admin.', 'homey');} ?></div>
                    
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
                        <p class="text-center"><strong><?php echo esc_html__('Log in', 'homey'); ?></strong></p>
                        <?php } ?>

                        <form <?php if($login_as_normal_form == 'yes'){ echo ' name="homey_login_no_ajax" method="post" '; } ?> id="login_form_<?php echo rand(111111, 999999); ?>">
                            <div class="form-group">
                                <input type="text" name="username" class="form-control email-input-1" placeholder="<?php echo esc_attr__('Username or Email', 'homey'); ?>">
                            </div>
                            <div class="form-group">
                                <input type="password" id="password" name="password" class="form-control password-input-2" placeholder="<?php echo esc_attr__('Password', 'homey'); ?>">
                                <span toggle="#password" class="toggle-password"></span>
                            </div>
                            
                            <?php if(homey_show_google_reCaptcha()){ ?>
                            <div class="bootstrap-select">
                                <?php get_template_part('template-parts/google', 'reCaptcha'); ?>
                            </div>
                            <?php } ?>

                            <div class="checkbox pull-left">
                                <label>
                                    <input name="remember" type="checkbox"> <?php echo esc_html__('Remember me', 'homey'); ?>
                                </label>
                            </div>
                            <div class="forgot-password-text pull-right">
                                <a href="#" data-toggle="modal" data-target="#modal-login-forgot-password" data-dismiss="modal"><?php echo esc_html__('Forgot password?', 'homey'); ?></a>
                            </div>

                            <?php wp_nonce_field( 'homey_login_nonce', 'homey_login_security' ); ?>
                            <?php if($login_as_normal_form == 'yes'){?>
                                <input type="hidden" name="action" id="homey_login_no_ajax" value="homey_login_no_ajax">
                                <button type="submit"
                                        class="homey_login_button_no_ajax btn btn-primary btn-full-width"><?php echo esc_html__('Log In', 'homey'); ?></button>
                            <?php } else{ ?>
                            <input type="hidden" name="action" id="homey_login_action" value="homey_login_child">
                            <button type="submit" class="homey_login_button btn btn-primary btn-full-width"><?php echo esc_html__('Log In', 'homey'); ?></button>

                            <?php } ?>
                            <input type="hidden" name="random_action_str" id="random_action_str" value="<?php echo rand(111111, 999999); ?>">
                        </form>
                        <?php if($nav_register) { ?>
                            <p class="text-center"><?php echo esc_html__("Don't have an account?", 'homey'); ?> <a href="#" data-toggle="modal" data-target="#modal-register" data-dismiss="modal"><?php echo esc_html__('Register', 'homey'); ?></a></p>
                        <?php } ?>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->