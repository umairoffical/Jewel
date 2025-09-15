<?php 
global $homey_local, $homey_booking_type;

if($homey_booking_type == 'per_day_date') {
    $ex_per_night = esc_html__('Per Day', 'homey');
    $ex_per_night_per_guest = esc_html__('Per Day Per Guest', 'homey');
} elseif($homey_booking_type == 'per_hour') {
    $ex_per_night = esc_html__('Per Hour', 'homey');
    $ex_per_night_per_guest = esc_html__('Per Hour Per Guest', 'homey');
} else if($homey_booking_type == 'per_week') {
    $ex_per_night = esc_html__('Per Week', 'homey');
    $ex_per_night_per_guest = esc_html__('Per Week Per Guest', 'homey');
} else if($homey_booking_type == 'per_month') {
    $ex_per_night = esc_html__('Per Month', 'homey');
    $ex_per_night_per_guest = esc_html__('Per Month Per Guest', 'homey');
} else {
    $ex_per_night = $homey_local['ex_per_night'];
    $ex_per_night_per_guest = $homey_local['ex_per_night_per_guest'];
}
?>
<div class="homey-extra-prices">
<hr class="row-separator">
<h3 class="sub-title mb-0"><?php echo esc_html__('Add Ons', 'homey'); ?></h3>
<p><?php esc_html_e(' Do you have any add-ons available, such as lights, cameras, extra tables, or other equipment? List them here! If a renter selects any add-ons, they will be charged a daily rental fee.', 'homey-child'); ?></p>

<div id="more_extra_services_main" class="custom-extra-prices">
    <div class="more_extra_services_wrap">
        <div class="row">
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="name"><?php esc_html_e('Enter Add on','homey-child'); ?></label>
                    <input type="text" name="extra_price[0][name]" class="form-control" placeholder="<?php esc_html_e('Enter Add On','homey-child'); ?>">
                </div>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="price"> <?php esc_html_e('Price Per Day (USD)','homey-child');; ?> </label>
                    <input type="text" name="extra_price[0][price]" class="form-control" placeholder="<?php esc_html_e('Enter Price Per Day','homey-child'); ?>">
                </div>
            </div>
            <!-- <div class="col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="type"> <?php //echo esc_attr($homey_local['ex_type']); ?> </label>
                        
                    <select name="extra_price[0][type]" class="selectpicker" data-live-search="false" data-live-search-style="begins">
                        <option value="single_fee"><?php //echo esc_attr($homey_local['ex_single_fee']); ?></option>
                        <option value="per_night"><?php //echo esc_attr($ex_per_night); ?></option>
                        <option value="per_guest"><?php //echo esc_attr($homey_local['ex_per_guest']); ?></option>
                        <option value="per_night_per_guest"><?php //echo esc_attr($ex_per_night_per_guest); ?></option>
                    </select>
                </div>
            </div> -->
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <button type="button" data-remove="0" class="remove-extra-services-child btn btn-primary btn-slim"><?php esc_html_e('Delete', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-xs-12 text-right">
        <button type="button" id="add_more_extra_services_child" data-increment="0" class="btn btn-primary btn-slim"><i class="homey-icon homey-icon-add"></i> <?php echo esc_html__('Add More','homey'); ?></button>
    </div>
</div>
</div>