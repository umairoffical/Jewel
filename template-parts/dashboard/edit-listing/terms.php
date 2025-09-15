<?php
global $homey_prefix, $hide_fields, $homey_local, $listing_data, $listing_meta_data, $homey_booking_type;
$min_book_days = homey_get_field_meta('min_book_days'); 
$min_book_hours = homey_get_field_meta('min_book_hours'); 
$max_book_days = homey_get_field_meta('max_book_days'); 
$min_book_weeks = homey_get_field_meta('min_book_weeks'); 
$max_book_weeks = homey_get_field_meta('max_book_weeks'); 
$min_book_months = homey_get_field_meta('min_book_months'); 
$max_book_months = homey_get_field_meta('max_book_months'); 
$checkin_after = homey_get_field_meta('checkin_after'); 
$checkout_before = homey_get_field_meta('checkout_before'); 
$get_start_hour = homey_get_field_meta('start_hour'); 
$get_end_hour = homey_get_field_meta('end_hour'); 
$smoke = homey_get_field_meta('smoke'); 
$pets = homey_get_field_meta('pets'); 
$party = homey_get_field_meta('party'); 
$children = homey_get_field_meta('children');

$additional_rules = isset($listing_meta_data['homey_additional_rules'][0]) ? $listing_meta_data['homey_additional_rules'][0] : ''; 
$cancellation_policy = isset($listing_meta_data['homey_cancellation_policy'][0]) ? $listing_meta_data['homey_cancellation_policy'][0] : ''; 

$overtime_policy = get_post_meta($listing_data->ID, 'homey_overtime_policy', true);


$checkin_after_before = homey_option('checkin_after_before');
$checkin_after_before_array = explode( ',', $checkin_after_before );

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');

// NEW FIELDS
$slots = get_post_meta($listing_data->ID, 'homey_parking_spots', true);
$homey_rules = get_post_meta($listing_data->ID, 'homey_rules', true);

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'rules') {
    $class = 'in active';
}
?>
<div id="rules-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
            <h3 class="title"><?php echo esc_attr(homey_option('ad_terms_rules')); ?></h3>
    </div>
    <div class="block-body">

        <?php if($hide_fields['cancel_policy'] != 1) { ?>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <!--<div class="form-group">
                        <label for="cancel"><?php echo esc_attr(homey_option('ad_cancel_policy')).homey_req('cancellation_policy'); ?></label>
                        <textarea name="cancellation_policy" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_cancel_policy_plac'), 'homey'); ?>" <?php homey_required('cancellation_policy'); ?>><?php echo $cancellation_policy; ?></textarea>
                    </div>-->

                    <div class="form-group">
                        <label for="cancel"><?php echo esc_attr(homey_option('ad_cancel_policy')).homey_req('cancellation_policy'); ?></label>
                        <select name="cancellation_policy" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr(homey_option('ad_cancel_policy')); ?>">
                            <option <?php if($cancellation_policy < 1){ echo 'selected'; } ?> value=""><?php echo esc_html__("Select Cancellation Policy", "homey"); ?></option>
                            <?php

                $args = array(
                    'post_type' => 'homey_cancel_policy',
                    'posts_per_page' => 100
                );

                $policies_data = '';

                $policies_qry = new WP_Query($args);
                if ($policies_qry->have_posts()):
                    while ($policies_qry->have_posts()): $policies_qry->the_post();
                        $is_selected = $cancellation_policy == get_the_ID() ? "selected='selected'" : '';
                        echo '<option '.$is_selected.' value="'.get_the_ID().'">'.get_the_title().'</option>';
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
                    // default settings - Kv_front_editor.php
                    // $content = $overtime_policy;
                    // $editor_id = 'overtime_policy';
                    // $settings =   array(
                    //     'id' => 'rules', // use wpautop?
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
                            <?php 
                            // for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
                            //     echo '<option '.selected(date('H:i',$halfhour), $get_start_hour, false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
                            // }
                            ?>
                    </select>
                    <span class="help-block"><?php //echo esc_html__('List when your property is available for renting.', 'homey-child'); ?></span>
                </div>
            </div>
            
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="end_hour"><?php //echo esc_html__('End Business Hour', 'homey').homey_req('end_hour'); ?></label>
                    <select name="end_hour" class="selectpicker" <?php //homey_required('end_hour'); ?> id="end_hour" data-live-search="false" title="<?php //echo esc_attr(homey_option('ad_text_select')); ?>">
                        <option value=""><?php //echo esc_attr(homey_option('ad_text_select')); ?></option>
                        <?php 
                        // for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
                        //     echo '<option '.selected(date('H:i',$halfhour), $get_end_hour, false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
                        // }
                        ?>
                    </select>
                    <span class="help-block"><?php //echo esc_html__('List the time your property is closed for renting.', 'homey-child'); ?></span>
                </div>
            </div>
            
        </div> -->

        <div class="row mb-20">
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="min_book_hours"><?php echo esc_attr(homey_option('ad_min_hours_booking')).homey_req('min_book_days'); ?></label>
                    <input type="number" name="min_book_hours" <?php homey_required('min_book_days'); ?> value="<?php echo esc_attr($min_book_hours); ?>" class="form-control" id="min_book_hours" placeholder="<?php echo esc_attr(homey_option('ad_min_hours_booking_plac')); ?>">
                </div>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label><?php esc_html_e('How many parking spots are available?','homey-child'); ?></label>
                    <input type="number" name="parking_spots" id="parking_spots" class="form-control" value="<?php echo $slots; ?>" placeholder="<?php esc_html_e('Enter Number of Parking Spots','homey-child'); ?>">
                </div>
            </div>
            <div class="col-sm-12 col-xs-12">
                <div class="listing-form-row">
                    <div class="house-features-list">
                        <label class="label-title" style="margin-bottom: 5px;"><?php esc_html_e('Parking Facilities','homey-child'); ?></label>

                        <?php
                        $amenities = get_terms('parking', array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
                        $selected_amenities = wp_get_post_terms($listing_data->ID, 'parking', array('fields' => 'ids'));

                        if (!empty($amenities)) {
                            foreach ($amenities as $amenity) {
                                $checked = in_array($amenity->term_id, $selected_amenities) ? 'checked' : '';
                                echo '<label class="control control--checkbox">';
                                    echo '<input type="checkbox" name="listing_parking[]" id="amenity-' . esc_attr($amenity->slug) . '" value="' . esc_attr($amenity->term_id) . '" ' . $checked . '>';
                                    echo '<span class="contro-text">' . esc_attr($amenity->name) . '</span>';
                                    echo '<span class="control__indicator"></span>';
                                echo '</label>';
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
                    $content = $additional_rules;
                    $editor_id = 'additional_rules';
                    $settings =   array(
                        'id' => 'rules',
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
                $amenities_terms_id = array();
                $amenities_terms = get_the_terms( $listing_data->ID, 'listing_accessibility' );
                if ( $amenities_terms && ! is_wp_error( $amenities_terms ) ) {
                    foreach( $amenities_terms as $amenity ) {
                        $amenities_terms_id[] = intval( $amenity->term_id );
                    }
                }

                $amenities = get_terms( 'listing_accessibility', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                if (!empty($amenities)) {
                    
                    foreach ($amenities as $amenity) {

                        if ( in_array( $amenity->term_id, $amenities_terms_id ) ) {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_accessibility[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '" checked />';
                                echo '<span class="contro-text">'.esc_attr( substr($amenity->name, 0, 30) . (strlen($amenity->name) > 30 ? '...' : '') ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                        } else {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_accessibility[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '">';
                                echo '<span class="contro-text">'.esc_attr( substr($amenity->name, 0, 30) . (strlen($amenity->name) > 30 ? '...' : '') ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                        }
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
            <div class="col-sm-12 col-xs-12 listing-rules-row mt-10">
                <?php 
                // if(!empty($homey_rules)):
                //     foreach($homey_rules as $id => $rule):
                //         $args = array(
                //             'id' => $id,
                //             'rulesText' => '',
                //             'data' => $rule
                //         );

                //         get_template_part('template-parts/dashboard/submit-listing/single-rule', null, $args);
                //     endforeach; 
                // endif;
                ?>
            </div>
        </div> -->
    </div>
</div>

