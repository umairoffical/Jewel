<?php
global $listing_id;

?>

<div class="multiple-date-hours-reperater">
    <div class="booking-table-labes" style="display: flex; gap: 30px; justify-content: space-between;">
        <b style="width: 50%;"><?php esc_html_e('Date','homey-child'); ?></b>
        <b style="width: 50%; margin-left: 15px;"><?php esc_html_e('Hours','homey-child'); ?></b>
    </div>
    <div class="multiple-date-hours-container">
        <?php 
            $id = uniqid();
        $args = [
            'id' => $id,
            'listing_id' => $listing_id
        ];
        get_template_part('single-listing/booking/single-hour-row', null, $args);
    ?>
    </div>
    <div class="add-more-date-hours" style="display: flex; gap: 10px; justify-content: space-between; margin-bottom: 20px;">
        <a class="add-more-date-hours-btn" style="cursor: pointer; text-decoration: underline;"><i class="fa fa-plus-square-o" aria-hidden="true"></i> <?php esc_html_e('Add Day','homey-child'); ?></a>
        <span class="total-hours-count"><?php esc_html_e('Total Hours: ','homey-child'); ?><span class="total-hours-count-number">0</span></span>
        <input type="hidden" class="total-hours-count-hidden" name="total_hours_count" value="0">
    </div>
</div>