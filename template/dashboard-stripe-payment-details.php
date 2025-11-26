<?php
/**
 * Template Name: Dashboard Stripe Payment Details
 */
if ( !is_user_logged_in() && !homey_is_admin() ) {
    wp_redirect(  home_url('/') );
}

get_header(); 

// GEt Payments from the custom table homey_renter_stripe_payment
// custom table name is homey_renter_stripe_payment
$payments = get_payments_from_custom_table();
function get_payments_from_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
    $payments = $wpdb->get_results("SELECT * FROM $table_name");
    return $payments;
}
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div style="background-color: #fff; border-radius: 10px; padding: 15px; box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                            <h2><?php esc_html_e('Stripe Payments', 'homey'); ?></h2>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Payment ID', 'homey'); ?></th>
                                        <th><?php esc_html_e('Renter ID', 'homey'); ?></th>
                                        <th><?php esc_html_e('Host ID', 'homey'); ?></th>
                                        <th><?php esc_html_e('Reservation ID', 'homey'); ?></th>
                                        <th><?php esc_html_e('Reservation Status', 'homey'); ?></th>
                                        <th><?php esc_html_e('Payment Status', 'homey'); ?></th>
                                        <th><?php esc_html_e('Paid Time', 'homey'); ?></th>
                                        <th><?php esc_html_e('Amount', 'homey'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($payments as $payment) { ?>
                                        <tr>
                                            <td><?php echo $payment->id; ?></td>
                                            <td><?php echo $payment->renter_id; ?></td>
                                            <td><?php echo $payment->host_id; ?></td>
                                            <td><?php echo $payment->reservation_id; ?></td>
                                            <td><?php echo ucfirst($payment->reservation_status); ?></td>
                                            <td><?php echo ucfirst($payment->payment_status); ?></td>
                                            <td><?php echo $payment->paid_time; ?></td>
                                            <td><?php echo homey_formatted_price($payment->amount, true); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area --> 
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->


<?php get_footer();?>
