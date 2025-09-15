<?php
global $current_user, $homey_local;
wp_get_current_user();
$userID  =  $current_user->ID;
$first_name  =  $current_user->first_name;
$last_name  =  $current_user->last_name;
$display_name = empty(trim($current_user->display_name))?$current_user->user_nicename: $current_user->display_name;

$enable_wallet = homey_option('enable_wallet');

$homey_author = homey_get_author_by_id('36', '36', 'img-circle', $userID);
$author_photo = $homey_author['photo'];

$dashboard_membership = homey_get_template_link_dash('template/dashboard-membership-host.php');
$dashboard = homey_get_template_link_dash('template/dashboard.php');
$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');

$dashboard_listings = homey_get_template_link_dash('template/dashboard-listings.php');
$dashboard_add_listing = homey_get_template_link_dash('template/dashboard-submission.php');

$dashboard_experiences = homey_get_template_link_dash('template/dashboard-experiences.php');
$dashboard_add_experience = homey_get_template_link_dash('template/dashboard-experience-submission.php');

$dashboard_favorites = homey_get_template_link_dash('template/dashboard-favorites.php');
$dashboard_search = homey_get_template_link_dash('template/dashboard-saved-searches.php');

$dashboard_reservations = homey_get_template_link_dash('template/dashboard-reservations.php');
$dashboard_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2.php');

$dashboard_exp_reservations = homey_get_template_link_dash('template/dashboard-reservations-experiences.php');
$dashboard_exp_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2-experiences.php');

$dashboard_messages = homey_get_template_link_dash('template/dashboard-messages.php');
$dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');

$dashboard_wallet = homey_get_template_link_dash('template/dashboard-wallet.php');
$earnings_page_link = add_query_arg( 'dpage', 'earnings', $dashboard_wallet );
$payout_request_link = add_query_arg( 'dpage', 'payout-request', $dashboard_wallet );
$payouts_page_link = add_query_arg( 'dpage', 'payouts', $dashboard_wallet );
$payouts_setup_page = add_query_arg( 'dpage', 'payment-method', $dashboard_wallet );
$dashboard_host_stripe = homey_get_template_link_dash('template/dashboard-host-stripe.php');

$all_users = add_query_arg( 'dpage', 'users', $dashboard );

$home_link = home_url('/');

$reservation_payment = homey_option('reservation_payment');

?>
<div class="account-loggedin no-cace-<?php echo strtotime("now"); ?>">
    <?php echo esc_html($display_name); ?>

    <span class="user-image">
        <?php echo homey_messages_notification( 'user-alert' ); ?>
        <?php echo ''.$author_photo; ?>
    </span>
    <div class="account-dropdown">
        <ul>
            <?php 
            if( !empty($dashboard) ) {
                echo '<li><a href="'.esc_url($dashboard).'"><!--<!--<i class="homey-icon homey-icon-house-2"></i>-->'.$homey_local['m_dashboard_label'].'</a></li>';
            }

            if( !empty($dashboard_profile) ) {
                echo '<li><a href="'.esc_url($dashboard_profile).'"><!--<i class="homey-icon homey-icon-multiple-man-woman-2-o"></i>-->'.$homey_local['m_profile_label'].'</a></li>';
            }

            if(!homey_is_renter()) {
                if(homey_is_host()){
                    if(homey_is_stripe_completed()){
                        if( !empty($dashboard_listings) ) {
                            echo '<li><a href="'.esc_url($dashboard_listings).'"><!--<i class="homey-icon homey-icon-layout-agenda-interface-essential"></i>-->'.$homey_local['m_listings_label'].'</a></li>';
                        }
                    }
                }else{
                    if( !empty($dashboard_listings) ) {
                        echo '<li><a href="'.esc_url($dashboard_listings).'"><!--<i class="homey-icon homey-icon-layout-agenda-interface-essential"></i>-->'.$homey_local['m_listings_label'].'</a></li>';
                    }
                }

                if( !empty($dashboard_experiences) ) {
                    echo '<li><a href="'.esc_url($dashboard_experiences).'"><!--<i class="homey-icon homey-icon-layout-agenda-interface-essential"></i>-->'.esc_html__('Experiences', 'homey').'</a></li>';
                }

                if(!homey_is_renter() && ! homey_is_admin() && in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
                    if( !empty($dashboard_membership) ) {
                        echo '<li>
                        <a href="'.esc_url($dashboard_membership).'"><!--<i class="homey-icon homey-icon-saving-bank-money-payments-finance"></i>-->'.esc_html__('Membership', 'homey').'</a>
                    </li>';
                    }
                }
            }

            if( !empty($dashboard_reservations) ) {
                if(homey_is_renter()) {
                   // echo '<li><a href="'.esc_url($dashboard_reservations).'"><!--<i class="homey-icon homey-icon-calendar-3"></i>-->'.$homey_local['m_reservation_label'].'</a></li>';
                } elseif(homey_is_admin()) {
                    //echo '<li><a href="'.esc_url($dashboard_reservations).'"><!--<i class="homey-icon homey-icon-calendar-3"></i>-->'.esc_html__('Bookings', 'homey').'</a></li>';
                } else {
                    $new_notification = homey_booking_notification(1);
                    $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';
                    //echo '<li><a href="'.esc_url($dashboard_reservations).'"><!--<i class="homey-icon homey-icon-calendar-3"></i>-->'.esc_html__('My Bookings', 'homey').' '.$new_notification.'</a></li>';
                }
            }

            if( !empty($dashboard_exp_reservations) ) {
                $new_notification = homey_booking_notification(1);
                $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';
                //echo '<li><a href="'.esc_url($dashboard_exp_reservations).'"><!--<i class="homey-icon homey-icon-calendar-3"></i>-->'.esc_html__('Experiences Reservations', 'homey').' '.$new_notification.'</a></li>';
            }

            if($enable_wallet != 0) {
                if($reservation_payment == 'percent' || $reservation_payment == 'full') {
                    if(homey_is_host()) {
                        if(homey_is_stripe_completed()){
                            if( !empty($dashboard_wallet) ) {
                                echo '<li><a href="'.esc_url($dashboard_wallet).'"><!--<i class="homey-icon homey-icon-saving-bank-money-payments-finance"></i>-->'.esc_html__('Wallet', 'homey').'</a></li>';
                            }
                        }
                    }

                    if(homey_is_renter()) {
                        if( !empty($dashboard_wallet) ) {
                            echo '<li><a href="'.esc_url($dashboard_wallet).'"><!--<i class="homey-icon homey-icon-saving-bank-money-payments-finance"></i>-->'.esc_html__('Wallet', 'homey').'</a></li>';
                        }
                    }

                    if(homey_is_admin()) {
                        if( !empty($dashboard_wallet) ) {
                            echo '<li><a href="'.esc_url($payouts_page_link).'"><!--<i class="homey-icon homey-icon-saving-bank-money-payments-finance"></i>-->'.esc_html__('Payouts', 'homey').'</a></li>';
                        }
                    }
                }
            }

            if(homey_is_host()){
                if(homey_is_stripe_completed()){
                    if( !empty($dashboard_favorites) ) {
                        echo '<li><a href="'.esc_url($dashboard_favorites).'"><!--<i class="homey-icon homey-icon-love-it"></i>-->'.$homey_local['m_favorites_label'].' </a></li>';
                    }
                }

            }else{
                if( !empty($dashboard_favorites) ) {
                    echo '<li><a href="'.esc_url($dashboard_favorites).'"><!--<i class="homey-icon homey-icon-love-it"></i>-->'.$homey_local['m_favorites_label'].' </a></li>';
                }
            }

            if(homey_is_host()){
                if(homey_is_stripe_completed()){
                    if( !empty($dashboard_invoices) ) {
                        echo '<li><a href="'.esc_url($dashboard_invoices).'"><!--<i class="homey-icon homey-icon-common-file-text-files-folders"></i>-->'.$homey_local['m_invoices_label'].' </a></li>';
                    }
                }
            }else{
                if( !empty($dashboard_invoices) ) {
                    echo '<li><a href="'.esc_url($dashboard_invoices).'"><!--<i class="homey-icon homey-icon-common-file-text-files-folders"></i>-->'.$homey_local['m_invoices_label'].' </a></li>';
                }
            }

            if(homey_is_host()){
                if( !empty($dashboard_wallet) ) {
                    if( !empty($dashboard_host_stripe) ) {
                        echo '<li class=""><a href="'.esc_url($dashboard_host_stripe).'">'.esc_html__('Stripe', 'homey').'</a></li>';
                    }
                }
            }

            if(homey_is_admin()) {
                if( !empty($all_users) ) {
                    echo '<li class=""><a href="'.esc_url($all_users).'"><!--<i class="homey-icon homey-icon-multiple-man-woman-2"></i>-->'.esc_html__('Users', 'homey').'</a></li>';
                }
            }


            if(homey_is_host()){
                if(homey_is_stripe_completed()){
                    if( !empty($dashboard_messages) ) {
                        echo '<li><a href="'.esc_url($dashboard_messages).'"><!--<i class="homey-icon homey-icon-messages-bubble-text-1-messages-chat-smileys"></i>-->'.$homey_local['m_messages_label'].' </a></li>';
                    }
                }
            }else{
                if( !empty($dashboard_messages) ) {
                    echo '<li><a href="'.esc_url($dashboard_messages).'"><!--<i class="homey-icon homey-icon-messages-bubble-text-1-messages-chat-smileys"></i>-->'.$homey_local['m_messages_label'].' </a></li>';
                }
            }

            echo '<li><a href="' . wp_logout_url(home_url('/')) . '"><!--<i class="homey-icon homey-icon-logout-interface-essential"></i>-->'.$homey_local['m_logout_label'].' </a></li>';
            ?>
        </ul>
    </div>
</div>
