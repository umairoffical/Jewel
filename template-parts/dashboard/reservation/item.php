<?php 
global $homey_prefix, $homey_local;
$listing_author = homey_get_author('40', '40', 'img-circle media-object avatar');



$check_in = get_post_meta(get_the_ID(), 'reservation_checkin_date', true);
$check_out = get_post_meta(get_the_ID(), 'reservation_checkout_date', true);
$reservation_guests = get_post_meta(get_the_ID(), 'reservation_guests', true);
$listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
$listing_address    = ''; //get_post_meta( $listing_id, $homey_prefix.'listing_address', true );
$pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
$deposit = get_post_meta(get_the_ID(), 'reservation_upfront', true);
$total_amount = get_post_meta(get_the_ID(), 'reservation_total', true);

$start_date = homey_get_booking_start_date(get_the_ID());
$end_date = homey_get_booking_end_date(get_the_ID());
$total_guests = homey_get_total_guests_range(get_the_ID(), $listing_id);


$reservation_extra_expenses = get_post_meta(get_the_ID(), 'homey_reservation_extra_expenses', true);
$reservation_extra_expenses = isset($reservation_extra_expenses[0]) ? $reservation_extra_expenses[0]['expense_value'] : 0;

$reservation_discount = get_post_meta(get_the_ID(), 'homey_reservation_discount', true);
$reservation_discount = isset($reservation_discount[0]) ? $reservation_discount[0]['discount_value'] : 0;

$total_amount += (float) $reservation_extra_expenses - (float) $reservation_discount;

$reservation_status = get_post_meta(get_the_ID(), 'reservation_status', true);

$hide_labels = homey_option('show_hide_labels');

if(homey_is_renter()) {
    $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
} else {

    if(!homey_listing_guest(get_the_ID())) {
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
    } else {
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations2.php');
    }
}

$detail_link = add_query_arg( 'reservation_detail', get_the_ID(), $reservation_page_link );

$no_upfront = homey_option('reservation_payment');
$booking_hide_fields = homey_option('booking_hide_fields');

if($no_upfront == 'no_upfront') {
    $price = '';
} else {
    $price = $deposit;
}

if( empty($price) ) {
    $price = $total_amount;
}
$price = $total_amount;

$is_read = $status_label = '';

$pets_allow = '-';

if($pets != 1) {
    $pets_allow = $homey_local['text_no'];
} elseif(!empty($pets )) {
    $pets_allow = $homey_local['text_yes'];
}

if( !homey_is_renter() ) {
    if($reservation_status == 'under_review') {
        $is_read = 'msg-unread';
    }
}

if ( is_page_template( array('template/dashboard.php') ) ) {
    $is_read = '';
}

?>
<tr class="<?php echo esc_attr($is_read); ?>">
    <td data-label="<?php esc_html_e('Author', 'homey'); ?>">
        <?php if(!empty($listing_author['photo'])) { 
            echo '<a href="'.esc_url($listing_author['link']).'" target="_blank">'.$listing_author['photo'].'</a>';
        } 
        ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['id_label']); ?>">
        <?php echo '#'.get_the_ID(); ?>
        <?php $wc_order_id = get_wc_order_id(get_the_ID()); if($wc_order_id > 0) echo 'wc#'.$wc_order_id; ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['status_label']); ?>">
        <?php homey_reservation_label($reservation_status, get_the_ID()); ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['date_label']); ?>">
        <?php esc_attr( the_time( homey_convert_date(homey_option( 'homey_date_format' ) ) ));?><br>
        <?php esc_attr( the_time( homey_time_format() ));?>
        
    </td>
    <td data-label="<?php echo esc_attr($homey_local['address']); ?>">
        <a href="<?php echo get_permalink($listing_id); ?>"><strong><?php echo get_the_title($listing_id); ?></strong></a>

        <?php get_template_part('single-listing/item-address'); ?>

        <?php if(!empty($listing_address)) { ?>
            <address><?php echo esc_attr($listing_address); ?></address>
        <?php } ?>
    </td>
    <td data-label="<?php esc_html_e('Rental Start','homey-child'); ?>">
        <?php echo $start_date; ?>
        
    </td>
    <td data-label="<?php esc_html_e('Rental End','homey-child'); ?>">
        <?php echo $end_date; ?>
    </td>
    <?php if(@$booking_hide_fields['guests'] != 1 && 0 !=  homey_option('cgl_guests')) {?>
    <td data-label="<?php echo homey_option('glc_guests_label');?>">
        <?php echo esc_attr($total_guests); ?>
    </td>
    <?php } ?>

    <?php if($hide_labels['sn_pets_allowed'] != 1) { ?>
    <td data-label="<?php echo esc_attr($homey_local['pets_label']);?>">
        <?php echo esc_attr($pets_allow); ?>
    </td>
    <?php } ?>

    <td data-label="<?php echo esc_attr($homey_local['subtotal_label']); ?>">
        <strong><?php echo homey_formatted_price($price); ?></strong>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['actions_label']); ?>">
        <div class="custom-actions">
            <?php 
            if( homey_listing_guest(get_the_ID()) ) {
                if($reservation_status == 'available') {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-success">'.esc_html__($homey_local['res_paynow_label'], 'homey').'</a>';
                } else {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-secondary">'.esc_html__($homey_local['res_details_label'], 'homey').'</a>';
                }
            } else {
                if($reservation_status == 'under_review') {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-success">'.esc_html__($homey_local['res_confirm_label'], 'homey').'</a>';
                } else {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-secondary">'.esc_html__($homey_local['res_details_label'], 'homey').'</a>';
                }
            }
            ?>
        </div>
    </td>
</tr>