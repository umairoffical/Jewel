<?php
global $homey_local, $hide_fields, $homey_booking_type;
$checkin_after_before = homey_option('checkin_after_before');
$checkin_after_before_array = explode( ',', $checkin_after_before );

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');
$start_and_end_hours = '';
for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
    $start_and_end_hours .= '<option value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
}

$checkinout_hours = '';
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html(homey_option('ad_terms_rules')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <?php if($hide_fields['cancel_policy'] != 1) { ?>
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label for="cancel"><?php echo esc_attr(homey_option('ad_cancel_policy')).homey_req('cancellation_policy'); ?></label>
                            <select name="cancellation_policy" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr(homey_option('ad_cancel_policy')); ?>">
                                <option value=""><?php echo esc_html__("Select Cancellation Policy", "homey"); ?></option>
                                <?php

                                $args = array(
                                    'post_type' => 'homey_cancel_policy',
                                    'posts_per_page' => 100
                                );

                                $policies_data = '';

                                $policies_qry = new WP_Query($args);
                                if ($policies_qry->have_posts()):
                                    while ($policies_qry->have_posts()): $policies_qry->the_post();
                                        echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                                    endwhile;
                                endif;
                                ?>
                            </select>
                            <?php  wp_reset_postdata(); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- <div class="row">
                <div class="col-sm-12 col-sm-12">
                    <div class="form-group">
                        <label for="overtime_policy"><?php //esc_html_e('Overtime Policy','homey-child'); ?></label>
                        <?php
                        // // default settings - Kv_front_editor.php
                        // $content = '';
                        // $editor_id = 'overtime_policy';
                        // $settings =   array(
                        //     'id' => 'rules', // id rules
                        //     'wpautop' => true, // use wpautop?
                        //     'media_buttons' => false, // show insert/upload button(s)
                        //     'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                        //     'textarea_rows' => '10', // rows="..."
                        //     'tabindex' => '',
                        //     'editor_css' => '', //  extra styles for both visual and HTML editors buttons,
                        //     'editor_class' => '', // add extra class(es) to the editor textarea
                        //     'teeny' => false, // output the minimal editor config used in Press This
                        //     'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                        //     'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                        //     'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                        // );
                        // wp_editor( $content, $editor_id, $settings ); ?>
                    </div>
                </div>
            </div> -->

            <!-- <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="start_hour"><?php //echo esc_html__('Start Business Hour', 'homey').homey_req('start_hour'); ?></label>
                        <select name="start_hour" class="selectpicker" <?php //homey_required('start_hour'); ?> id="start_hour" data-live-search="false" title="<?php //echo esc_attr(homey_option('ad_text_select')); ?>">
                                <option value=""><?php //echo esc_attr(homey_option('ad_text_select')); ?></option>
                                <?php //echo ''.$start_and_end_hours; ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="end_hour"><?php //echo esc_html__('End Business Hour', 'homey').homey_req('end_hour'); ?></label>
                        <select name="end_hour" class="selectpicker" <?php //homey_required('end_hour'); ?> id="end_hour" data-live-search="false" title="<?php //echo esc_attr(homey_option('ad_text_select')); ?>">
                            <option value=""><?php //echo esc_attr(homey_option('ad_text_select')); ?></option>
                            <?php //echo ''.$start_and_end_hours; ?>
                        </select>
                    </div>
                </div>
            </div> -->
            
            <?php if($homey_booking_type == 'per_hour') { ?>
                <hr class="row-separator">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <h3 class="sub-title"><?php echo esc_html__('Business Hours', 'homey'); ?></h3>
                    </div>
                    
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="start_hour"><?php echo esc_html__('Start Hour', 'homey').homey_req('start_hour'); ?></label>
                            <select name="start_hour" class="selectpicker" <?php homey_required('start_hour'); ?> id="start_hour" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                                    <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                                    <?php echo ''.$start_and_end_hours; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="end_hour"><?php echo esc_html__('End Hour', 'homey').homey_req('end_hour'); ?></label>
                            <select name="end_hour" class="selectpicker" <?php homey_required('end_hour'); ?> id="end_hour" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                                <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                                <?php echo ''.$start_and_end_hours; ?>
                            </select>
                        </div>
                    </div>
                    
                </div>

            <?php } elseif( $homey_booking_type == 'per_day_date' || $homey_booking_type == 'per_day' ) { ?>
                <div class="row">
                    <?php if($hide_fields['checkin_after'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="checkin_after"><?php echo esc_attr(homey_option('ad_check_in_after')).homey_req('checkin_after'); ?></label>
                            <select name="checkin_after" class="selectpicker" <?php homey_required('checkin_after'); ?> id="checkin_after" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                                    <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                                    <?php 
                                        foreach ($checkin_after_before_array as $hour) {
                                            echo '<option value="'.trim($hour).'">'.trim($hour).'</option>';
                                        }
                                    ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if($hide_fields['checkout_before'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="checkout_before"><?php echo esc_attr(homey_option('ad_check_out_before')).homey_req('checkout_before'); ?></label>
                            <select name="checkout_before" class="selectpicker" <?php homey_required('checkout_before'); ?> id="checkout_before" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                                <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                                <?php 
                                foreach ($checkin_after_before_array as $hour2) {
                                    echo '<option value="'.trim($hour2).'">'.trim($hour2).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                </div>

            <?php } ?>
            
            <div class="row mb-20">
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="min_book_hours"><?php echo esc_attr(homey_option('ad_min_hours_booking')).homey_req('min_book_days'); ?></label>
                        <input type="number" name="min_book_hours" <?php homey_required('min_book_days'); ?> value="" class="form-control" id="min_book_hours" placeholder="<?php echo esc_attr(homey_option('ad_min_hours_booking_plac')); ?>">
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label><?php esc_html_e('How many parking spots are available?','homey-child'); ?></label>
                        <input type="number" name="parking_spots" id="parking_spots" class="form-control" placeholder="<?php esc_html_e('Enter Number of Parking Spots','homey-child'); ?>">
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12">
                    <div class="listing-form-row">
                        <div class="house-features-list">
                            <label class="label-title" style="margin-bottom: 5px;"><?php esc_html_e('Parking Facilities','homey-child'); ?></label>

                            <?php
                            $amenities = get_terms( 'parking', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                            if (!empty($amenities)) {
                                $count = 1;
                                foreach ($amenities as $amenity) {
                                    echo '<label class="control control--checkbox">';
                                        echo '<input type="checkbox" name="listing_parking[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '">';
                                        echo '<span class="contro-text">'.esc_attr( $amenity->name ).'</span>';
                                        echo '<span class="control__indicator"></span>';
                                    echo '</label>';
                                    $count++;
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-sm-12">
                    <div class="form-group">
                        <label for="additional_rules"><?php esc_html_e('Location Rules','homey-child'); ?></label>
                        <?php
                        // default settings - Kv_front_editor.php
                        $content = '';
                        $editor_id = 'additional_rules';
                        $settings =   array(
                            'id' => 'rules', // id rules
                            'wpautop' => true,
                            'media_buttons' => false,
                            'textarea_name' => $editor_id,
                            'textarea_rows' => '10',
                            'tabindex' => '',
                            'editor_css' => '',
                            'editor_class' => '',
                            'teeny' => false,
                            'dfw' => false,
                            'tinymce' => true,
                            'quicktags' => false // Set quicktags to false to remove code view
                        );
                        wp_editor( $content, $editor_id, $settings ); ?>
                    </div>
                </div>
            </div>

            <div class="listing-form-row mb-10">
                <div class="house-features-list">
                    <label class="label-title mb-0" style="font-size: 16px; font-weight: 600;"><?php esc_html_e('Accessibility','homey-child'); ?></label>
                    <p class="mb-15" style="margin-top: -5px;"><?php esc_html_e('Select accessibility features available at this property','homey-child');?></p>
                    <?php
                    $facilities = get_terms( 'listing_accessibility', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                    if (!empty($facilities)) {
                        $count = 1;
                        foreach ($facilities as $facility) {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_accessibility[]" id="facility-' . esc_attr( $facility->slug ). '" value="' . esc_attr( $facility->term_id ). '">';
                                echo '<span class="contro-text">'.esc_attr( substr($facility->name, 0, 30) . (strlen($facility->name) > 30 ? '...' : '') ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                            $count++;
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- <div class="row">
                <div class="col-xs-12">
                    <label class="label-title" style="margin-bottom: 5px;"><?php //esc_html_e('Additoinal Rules!','homey-child');?></label>
                    <small>(<?php //esc_html_e('Add Mulitple additional rules for your listing.','homey-child');?>)</small>
                </div>
                <div class="col-sm-12 mt-10">
                    <div class="row">
                        <div class="col-sm-9 col-xs-12">
                            <input type="text" name="listing_rules_add_rule" id="listing_rules_add_rule" class="form-control" placeholder="<?php //esc_html_e('Enter the Rules!','homey-child'); ?>">
                        </div>
                        <div class="col-sm-3 col-xs-12">
                            <a href="#" class="btn btn-primary btn-single-rule" style="width:100%;"><i class="homey-icon homey-icon-add"></i> <?php //esc_html_e('Add Rule','homey-child'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-xs-12 listing-rules-row mt-10"></div>
            </div> -->
        </div>
    </div>
</div>
