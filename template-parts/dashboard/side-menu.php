<?php
global $current_user, $post, $homey_local;
$current_user = wp_get_current_user();
$enable_wallet = homey_option('enable_wallet');
$reservation_payment = homey_option('reservation_payment');
$offsite_payment = homey_option('off-site-payment');

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$earnings_page_link = add_query_arg( 'dpage', 'earnings', $wallet_page_link );
$payout_request_link = add_query_arg( 'dpage', 'payout-request', $wallet_page_link );
$payouts_page_link = add_query_arg( 'dpage', 'payouts', $wallet_page_link );
//$payouts_setup_page = add_query_arg( 'dpage', 'payment-method', $wallet_page_link );
$security_deposits_page = add_query_arg( 'dpage', 'security-deposits', $wallet_page_link );

$dashboard = homey_get_template_link_dash('template/dashboard.php');
$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$payment_method_setup = add_query_arg( 'dpage', 'payment-method', $dashboard_profile );

$dashboard_listings = homey_get_template_link_dash('template/dashboard-listings.php');

$dashboard_experiences = homey_get_template_link_dash('template/dashboard-experiences.php');
$dashboard_add_experience = homey_get_template_link_dash('template/dashboard-experience-submission.php');

$dashboard_membership = homey_get_template_link_dash('template/dashboard-membership-host.php');
$dashboard_add_listing = homey_get_template_link_dash('template/dashboard-submission.php');
$dashboard_favorites = homey_get_template_link_dash('template/dashboard-favorites.php');
$dashboard_search = homey_get_template_link_dash('template/dashboard-saved-searches.php');

$dashboard_reservations = homey_get_template_link_dash('template/dashboard-reservations.php');
$dashboard_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2.php');

$dashboard_exp_reservations = homey_get_template_link_dash('template/dashboard-reservations-experiences.php');
$dashboard_exp_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2-experiences.php');

$dashboard_messages = homey_get_template_link_dash('template/dashboard-messages.php');
$dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');
$dashboard_wallet = homey_get_template_link_dash('template/dashboard-wallet.php');
$home_link = home_url('/');

$all_users = add_query_arg( 'dpage', 'users', $dashboard );
$verification_page = add_query_arg( 'dpage', 'verification', $dashboard_profile );
$password_page = add_query_arg( 'dpage', 'password-reset', $dashboard_profile );

$dashboard_host_stripe = homey_get_template_link_dash('template/dashboard-host-stripe.php');

$ac_wallet = $ac_dash = $ac_host_stripe = $ac_profile = $ac_fav = $ac_experiences = $ac_experience_submission = $ac_listings = $ac_membership = $ac_invoices = $ac_msgs = $ac_submission = $ac_reserv = $ac_reserv_host = $ac_exp_reserv = $ac_exp_reserv_host = '';

//separate two classes check for experiences and for listings
if ( is_page_template( 'template/dashboard-submission.php' )
    || is_page_template( 'template/dashboard-reservations2.php' )
    || is_page_template( 'template/dashboard-listings.php' )
    || is_page_template( 'template/dashboard-reservations.php' )
){
    $ac_listings = 'board-panel-item-active';
}

if ( is_page_template( 'template/dashboard-reservations-experiences.php' )
    || is_page_template( 'template/dashboard-reservations2-experiences.php' )
    || is_page_template( 'template/dashboard-experience-submission.php' )
){
    $ac_experiences = 'board-panel-item-active';
}
//separate two classes check for experiences and for listings

if( is_page_template( 'template/dashboard.php' ) && !isset($_GET['dpage'])) {
    $ac_dash = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-profile.php' ) ) {
    $ac_profile = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-experiences.php' ) ) {
    $ac_experiences = 'board-panel-item-active';
}elseif ( is_page_template( 'template/dashboard-experience-submission.php' ) ) {
    $ac_experience_submission = 'board-panel-item-active';
}elseif ( is_page_template( 'template/dashboard-listings.php' ) ) {
    $ac_listings = 'board-panel-item-active';
}elseif ( is_page_template( 'template/dashboard-membership-host.php' ) ) {
    $ac_membership = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-submission.php' ) ) {
    $ac_submission = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-favorites.php' ) ) {
    $ac_fav = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-invoices.php' ) ) {
    $ac_invoices = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-messages.php' ) ) {
    $ac_msgs = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-reservations.php' ) ) {
    $ac_reserv = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-reservations2.php' ) ) {
    $ac_reserv_host = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-reservations-experiences.php' ) ) {
    $ac_exp_reserv = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-reservations2-experiences.php' ) ) {
    $ac_exp_reserv_host = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-wallet.php' ) ) {
    $ac_wallet = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-membership-host.php' ) ) {
    $ac_wallet = 'board-panel-item-active';
} elseif ( is_page_template( 'template/dashboard-host-stripe.php' ) ) {
    $ac_host_stripe = 'board-panel-item-active';
} 

?>
<div class="user-dashboard-left white-bg"> 
    <div class="navi">
        <ul class="board-panel-menu">
            <?php 
            if( !empty($dashboard) ) {
                echo '<li class="'.esc_attr($ac_dash).'">
                        <a href="'.esc_url($dashboard).'">
                            '.$homey_local['m_dashboard_label'].'
                        </a>
                    </li>';
            }

            if( !empty($dashboard_profile) ) {
                echo '<li class="has-child '.esc_attr($ac_profile).'">
                    <a href="'.esc_url($dashboard_profile).'">
                        '.$homey_local['m_profile_label'].' <i class="homey-icon homey-icon-arrow-down-1"></i>
                    </a>';

                    echo '<ul>';
                        if(!homey_is_admin()) {
                            echo '<li><a href="'.esc_url($verification_page).'">'.esc_html__('Verification', 'homey').'</a></li>';
                        }
                        echo '<li><a href="'.esc_url($password_page).'">'.esc_html__('Password', 'homey').'</a></li>';

                        // if($offsite_payment != 0 || $enable_wallet != 0) {
                        //     echo '<li><a href="'.esc_url($payment_method_setup).'">'.esc_html__('Payment Method', 'homey').'</a></li>';
                        // }
                    echo '</ul>';
                    
                echo '</li>';
                
            }

            if(homey_is_host()){
                if(homey_is_stripe_completed()){
                    if(!empty($dashboard_listings)) {
                        $main_listing_link = $dashboard_listings;
                        $main_listing_heading = $homey_local['m_listings_label'];
        
                        if (!empty($dashboard_reservations) && homey_is_renter()) {
                            $main_listing_link = $dashboard_reservations;
                            $main_listing_heading = $homey_local['m_reservation_label'];
                        }
        
                        echo '<li class="' . esc_attr($ac_listings) . '">
                                <a href="' . esc_url($main_listing_link) . '">' . $main_listing_heading . '<i class="homey-icon homey-icon-arrow-down-1"></i></a>
                        <ul>';
                        if (!homey_is_renter() && !empty($dashboard_add_listing)) {
                            echo '<li class="' . esc_attr($ac_submission) . '">
                                <a href="' . esc_url($dashboard_add_listing) . '">' . $homey_local['m_add_listing_label'] . '</a>
                            </li>';
                        }
        
                        if (!empty($dashboard_reservations)) {
                            if (homey_is_renter()) {
                                echo '<li data-diber="1" class="' . esc_attr($ac_reserv) . '">
                                <a href="' . esc_url($dashboard_reservations) . '">' . $homey_local['m_reservation_label'] . '</a>
                            </li>';
                            } elseif (homey_is_admin()) {
                                echo '<li class="' . esc_attr($ac_reserv) . '">
                                <a href="' . esc_url($dashboard_reservations) . '">' . esc_html__('My Bookings', 'homey') . '</a>
                            </li>';
                            } else {
                                $new_notification = homey_booking_notification(1);
                                $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';
        
                                echo '<li class="' . esc_attr($ac_reserv) . '">
                                <a href="' . esc_url($dashboard_reservations) . '">' . esc_html__('My Bookings', 'homey') . ' ' . $new_notification . '</a>
                            </li>';
                            }
                        }
        
                        if (!empty($dashboard_host_reservations) && !homey_is_renter()) {
                            echo '<li class="' . $ac_reserv_host . '">
                                <a href="' . esc_url($dashboard_host_reservations) . '">' . esc_html__('Reservations', 'homey') . '</a>
                            </li>';
                        }
        
                        echo '</ul> 
                        </li>';
                    }
                }
            }else{
                if(!empty($dashboard_listings)) {
                    $main_listing_link = $dashboard_listings;
                    $main_listing_heading = $homey_local['m_listings_label'];
    
                    if (!empty($dashboard_reservations) && homey_is_renter()) {
                        $main_listing_link = $dashboard_reservations;
                        $main_listing_heading = $homey_local['m_reservation_label'];
                    }
    
                    echo '<li class="' . esc_attr($ac_listings) . '">
                            <a href="' . esc_url($main_listing_link) . '">' . $main_listing_heading . '<i class="homey-icon homey-icon-arrow-down-1"></i></a>
                    <ul>';
                    if (!homey_is_renter() && !empty($dashboard_add_listing)) {
                        echo '<li class="' . esc_attr($ac_submission) . '">
                            <a href="' . esc_url($dashboard_add_listing) . '">' . $homey_local['m_add_listing_label'] . '</a>
                        </li>';
                    }
    
                    if (!empty($dashboard_reservations)) {
                        if (homey_is_renter()) {
                            echo '<li data-diber="1" class="' . esc_attr($ac_reserv) . '">
                            <a href="' . esc_url($dashboard_reservations) . '">' . $homey_local['m_reservation_label'] . '</a>
                        </li>';
                        } elseif (homey_is_admin()) {
                            echo '<li class="' . esc_attr($ac_reserv) . '">
                            <a href="' . esc_url($dashboard_reservations) . '">' . esc_html__('My Bookings', 'homey') . '</a>
                        </li>';
                        } else {
                            $new_notification = homey_booking_notification(1);
                            $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';
    
                            echo '<li class="' . esc_attr($ac_reserv) . '">
                            <a href="' . esc_url($dashboard_reservations) . '">' . esc_html__('My Bookings', 'homey') . ' ' . $new_notification . '</a>
                        </li>';
                        }
                    }
    
                    if (!empty($dashboard_host_reservations) && !homey_is_renter()) {
                        echo '<li class="' . $ac_reserv_host . '">
                            <a href="' . esc_url($dashboard_host_reservations) . '">' . esc_html__('Reservations', 'homey') . '</a>
                        </li>';
                    }
    
                    echo '</ul> 
                    </li>';
                }
            }

            if( !empty($dashboard_experiences) ) {
                $main_experience_link = $dashboard_experiences;
                $main_experiences_heading = $homey_local['m_experiences_label'];
                // checking to put proper text on the main link for renter
                if (!empty($dashboard_exp_reservations) && homey_is_renter()) {
                    $main_experience_link = $dashboard_exp_reservations;
                }

                echo '<li class="' . esc_attr($ac_experiences) . '">
                        <a href="' . esc_url($main_experience_link) . '">' . $main_experiences_heading . '<i class="homey-icon homey-icon-arrow-down-1"></i></a>
                <ul>';
                // experiences menu items

                if( !empty($dashboard_add_experience)  && !homey_is_renter() ) {
                    echo '<li class="'.esc_attr($ac_experience_submission).'"><a href="'.esc_url($dashboard_add_experience).'">'.$homey_local['m_add_experience_label'].' </a></li>';
                }

                if( !empty($dashboard_exp_reservations) ) {
                    $new_notification = homey_booking_notification(1);
                    $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';
                    echo '<li class="'.$ac_exp_reserv.'"><a href="'.esc_url($dashboard_exp_reservations).'">'.esc_html__('Reservations', 'homey').' '.$new_notification.'</a></li>';
                }

                if( !empty($dashboard_exp_host_reservations) && !homey_is_renter()) {
                    echo '<li class="'.$ac_exp_reserv_host.'"><a href="'.esc_url($dashboard_exp_host_reservations).'">'.esc_html__('My Bookings', 'homey').'</a></li>';
                }

                // end experiences menu items
                echo '</ul>';
            }

            if(!homey_is_renter() && ! homey_is_admin() && in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
                if( !empty($dashboard_membership) ) {
                    echo '<li class="'.esc_attr($ac_membership).'">
                        <a href="'.esc_url($dashboard_membership).'">'.esc_html__('Membership', 'homey').'</a>
                    </li>';
                }
            }

            if(homey_is_host()){

                if(homey_is_stripe_completed()){
                    if( !empty($dashboard_wallet) ) {
                        echo '<li class="'.esc_attr($ac_wallet).' has-child">
                            <a href="'.esc_url($dashboard_wallet).'">'.esc_html__('Wallet', 'homey').' <i class="homey-icon homey-icon-arrow-down-1"></i></a>
                            <ul>
                                <li><a href="'.esc_url($earnings_page_link).'">'.esc_html__('Earnings', 'homey').'</a></li>
                            </ul>
                        </li>';
                    }
                }
                
            }else{
                if($enable_wallet != 0) {
                    if($reservation_payment == 'percent' || $reservation_payment == 'full') {
                        if(homey_is_host()) {
                            if( !empty($dashboard_wallet) ) {
                                echo '<li class="'.esc_attr($ac_wallet).' has-child">
                                    <a href="'.esc_url($dashboard_wallet).'">'.esc_html__('Wallet', 'homey').' <i class="homey-icon homey-icon-arrow-down-1"></i></a>
                                    <ul>
                                        <li><a href="'.esc_url($earnings_page_link).'">'.esc_html__('Earnings', 'homey').'</a></li>
                                    </ul>
                                </li>';
                            }
                        }
                    }
                }
            }

            if(homey_is_host()){
                if( !empty($dashboard_host_stripe) ) {
                    echo '<li class="'.esc_attr($ac_host_stripe).'">
                        <a href="'.esc_url($dashboard_host_stripe).'">'.esc_html__('Stripe', 'homey').'</a>
                    </li>';
                }
            }

            if(homey_is_host()) {
                if(homey_is_stripe_completed()){
                    if( !empty($dashboard_messages) ) {
                        echo '<li class="'.esc_attr($ac_msgs).'">
                            <a href="'.esc_url($dashboard_messages).'">'.$homey_local['m_messages_label'].'
                            '.homey_messages_notification().'
                            </a>
                        </li>';
                    }
                }
                
            }else{
                if( !empty($dashboard_messages) ) {
                    echo '<li class="'.esc_attr($ac_msgs).'">
                        <a href="'.esc_url($dashboard_messages).'">'.$homey_local['m_messages_label'].'
                        '.homey_messages_notification().'
                        </a>
                    </li>';
                }
            }

            if( !empty($dashboard_invoices) && !homey_is_host()) {
                echo '<li class="'.esc_attr($ac_invoices).'">
                    <a href="'.esc_url($dashboard_invoices).'">'.esc_html__('Transactions','homey-child').'</a>
                </li>';
            }

            if(homey_is_admin()) {
                if( !empty($all_users) ) {
                    echo '<li class="">
                        <a href="'.esc_url($all_users).'">'.esc_html__('Users', 'homey').'</a>
                    </li>';
                }
            }

            if(homey_is_host()){
                if(homey_is_stripe_completed()){
                    if( !empty($dashboard_favorites) ) {
                        echo '<li class="'.esc_attr($ac_fav).'">
                            <a href="'.esc_url($dashboard_favorites).'">'.$homey_local['m_favorites_label'].'</a>
                        </li>';
                    }
                }

            }else{
                if( !empty($dashboard_favorites) ) {
                    echo '<li class="'.esc_attr($ac_fav).'">
                        <a href="'.esc_url($dashboard_favorites).'">'.$homey_local['m_favorites_label'].'</a>
                    </li>';
                }
            }

            echo '<li>
                <a href="' . wp_logout_url(home_url('/')) . '">'.$homey_local['m_logout_label'].'</a>
            </li>';
            ?>
            
        </ul>
    </div>
</div>
