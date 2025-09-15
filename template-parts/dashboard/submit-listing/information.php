<?php
global $homey_local, $hide_fields;
$openning_hours_list = homey_option('openning_hours_list');
$openning_hours_list_array = explode( ',', $openning_hours_list );
?>
<div class="form-step form-step-information">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html__(homey_option('ad_section_info'), 'homey');?></h2>
                <p class="description-block mb-0"><?php echo esc_html__('Fill all the mandatory fields', 'homey'); ?></p>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <?php $room_type = homey_get_terms('room_type'); ?>

            <?php if($hide_fields['room_type'] != 1) { ?>
            <div class="row">
                <div class="col-sm-12 col-xs-12"><label> <?php echo esc_attr(homey_option('ad_room_type')).homey_req('room_type'); ?> </label></div>
                <?php if(!empty($room_type)) { ?>

                    <?php foreach($room_type as $room) { ?>    
                    <div class="col-sm-4 col-xs-12">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input type="radio" name="room_type" <?php homey_required('room_type'); ?> value="<?php echo esc_attr($room->term_id); ?>">
                                <span class="control-text"><?php echo esc_attr($room->name); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php } ?>

            <div class="row">
                <?php if($hide_fields['listing_title'] != 1) { ?>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="listing_title"><?php echo esc_attr(homey_option('ad_title')).homey_req('listing_title'); ?></label>
                        <input type="text" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_title')), 'homey'); ?>" name="listing_title" id="listing_title" class="form-control" <?php homey_required('listing_title'); ?> placeholder="<?php echo esc_attr(homey_option('ad_title_plac'));?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['description'] != 1) { ?>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="description"><?php echo esc_attr(homey_option('ad_des')); ?></label>
                        <?php 
                        // default settings - Kv_front_editor.php
                        $content = '';
                        $editor_id = 'description';
                        $settings =   array(
                            'wpautop' => true, // use wpautop?
                            'media_buttons' => false, // show insert/upload button(s)
                            'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                            'textarea_rows' => '10', // rows="..."
                            'tabindex' => '',
                            'editor_css' => '', //  extra styles for both visual and HTML editors buttons, 
                            'editor_class' => '', // add extra class(es) to the editor textarea
                            'teeny' => false, // output the minimal editor config used in Press This
                            'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                            'quicktags' => false // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                        );
                        wp_editor( $content, $editor_id, $settings ); ?>
                    </div>
                </div>
                <?php } ?>

                <?php
                if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                
                    if(!empty($fields_array)) {
                        foreach ( $fields_array as $value ) {
                            $field_title = $value->label;
                            $field_name = $value->field_id;
                            $field_type = $value->type;
                            $field_placeholder = $value->placeholder;

                            $field_title = homey_wpml_translate_single_string($field_title);

                            $field_placeholder = homey_wpml_translate_single_string($field_placeholder);

                            if($field_type == 'textarea') {

                                if($hide_fields[$field_name] != 1) {
                                    
                                    echo '<div class="col-sm-12">';
                                    echo '<div class="form-group">';
                                    echo '<label for="'.$field_name.'">'.$field_title.homey_req($field_name).'</label>';
                                    // default settings - Kv_front_editor.php
                                    $content1 = '';
                                    $editor_id1= $field_name;
                                    $settings1 =   array(
                                        'wpautop' => true, // use wpautop?
                                        'media_buttons' => false, // show insert/upload button(s)
                                        'textarea_name' => $editor_id1, // set the textarea name to something different, square brackets [] can be used here
                                        'textarea_rows' => '5', // rows="..."
                                        'tabindex' => '',
                                        'editor_css' => '', //  extra styles for both visual and HTML editors buttons, 
                                        'editor_class' => '', // add extra class(es) to the editor textarea
                                        'teeny' => false, // output the minimal editor config used in Press This
                                        'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                        'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                        'quicktags' => false // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                    );
                                    wp_editor( $content1, $editor_id1, $settings1 );
                                    echo '</div>';
                                    echo '</div>';
                                }

                            }
                        } 
                    }
                }
                ?>

            </div>
            <div class="row">

                <?php if($hide_fields['listing_type'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="listing_type"> <?php echo esc_attr(homey_option('ad_listing_type')).homey_req('listing_type'); ?> </label>
                        <select name="listing_type[]" class="selectpicker" id="listing_type" multiple="multiple" data-live-search="false" data-live-search-style="begins">
                            <?php
                            $listing_type = get_terms (
                                array(
                                    "listing_type"
                                ),
                                array(
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'hide_empty' => false,
                                    'parent' => 0
                                )
                            );

                            homey_get_taxonomies_with_id_value( 'listing_type', $listing_type, -1);
                            ?>

                        </select>
                    </div>
                </div>
                <?php } ?>
                
                <?php if($hide_fields['listing_bedrooms'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="listing_bedrooms"> <?php echo esc_attr(homey_option('ad_no_of_bedrooms')).homey_req('listing_bedrooms'); ?> </label>
                        <input type="text" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_no_of_bedrooms')), 'homey'); ?>" name="listing_bedrooms" id="listing_bedrooms" <?php homey_required('listing_bedrooms'); ?> class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_bedrooms_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['guests'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="guests"> <?php esc_html_e('Max Guests', 'homey-child'); ?> <?php echo homey_req('guests'); ?> </label>
                        <input type="number" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_no_of_guests')), 'homey'); ?>" name="guests" id="guests" <?php homey_required('guests'); ?> class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_guests_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['beds'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="beds"> <?php echo esc_attr(homey_option('ad_no_of_beds')).homey_req('beds'); ?> </label>
                        <input type="number" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_no_of_beds')), 'homey'); ?>" name="beds" id="beds" <?php homey_required('beds'); ?> class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_beds_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['baths'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="baths"> <?php echo esc_attr(homey_option('ad_no_of_bathrooms')).homey_req('baths'); ?> </label>
                        <input type="number"  data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_no_of_bathrooms')), 'homey'); ?>"  name="baths" id="baths" <?php homey_required('baths'); ?> class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_bathrooms_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['listing_rooms'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="listing_rooms"> <?php echo esc_attr(homey_option('ad_listing_rooms')).homey_req('listing_rooms'); ?> </label>
                        <input type="number" class="form-control" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_listing_rooms')), 'homey'); ?>"  name="listing_rooms" id="listing_rooms" <?php homey_required('listing_rooms'); ?> placeholder="<?php echo esc_attr(homey_option('ad_listing_rooms_plac'));?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['listing_size'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="listing_size"><?php esc_html_e('Property Size (Sqft)', 'homey-child');?> <?php echo homey_req('listing_size'); ?> </label>
                        <input type="number" class="form-control" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_listing_size')), 'homey'); ?>" name="listing_size" id="listing_size" <?php homey_required('listing_size'); ?> placeholder="<?php echo esc_attr(homey_option('ad_size_placeholder'));?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['listing_size_unit'] != 1) { ?>
                <!-- <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="listing_size_unit"> <?php //echo esc_attr(homey_option('ad_listing_size_unit')).homey_req('listing_size_unit'); ?> </label> -->
                        <input type="hidden" class="form-control" disabled value="Sqft" name="listing_size_unit" id="listing_size_unit" <?php homey_required('listing_size_unit'); ?>>
                    <!-- </div>
                </div> -->
                <?php } ?>

                <div class="col-sm-12 col-xs-12 listing-opening-hours mt-15">
                    <div class="form-group">
                        <h4 class="mb-0"><?php esc_html_e('Opening Hours', 'homey-child'); ?></h4>
                        <p class="mb-10"><?php esc_html_e('Set your opening hours to let guests know what times your location is open to host booking (i.e your general availability).', 'homey-child'); ?></p>
                        <div class="opening-hours-container">
                            <div class="opening-hours-row">
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <label for=""><?php esc_html_e('Day', 'homey-child'); ?></label>
                                        <h5 class="mt-10"><?php esc_html_e('Monday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <label for=""><?php esc_html_e('Are you available?', 'homey-child'); ?></label>
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[mon_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[mon_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <label for=""><?php esc_html_e('Open Time', 'homey-child'); ?></label>
                                        <select class="form-control" name="timeperiod[mon_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <label for=""><?php esc_html_e('Close Time', 'homey-child'); ?></label>
                                        <select class="form-control" name="timeperiod[mon_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <h5 class="mt-10"><?php esc_html_e('Tuesday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[tue_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[tue_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[tue_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[tue_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <h5 class="mt-10"><?php esc_html_e('Wednesday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[wed_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[wed_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[wed_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[wed_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <h5 class="mt-10"><?php esc_html_e('Thursday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[thu_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[thu_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[thu_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[thu_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <h5 class="mt-10"><?php esc_html_e('Friday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[fri_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[fri_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[fri_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[fri_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <h5 class="mt-10"><?php esc_html_e('Saturday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[sat_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[sat_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[sat_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[sat_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row timeperiod-single-day">
                                    <div class="col-sm-2 col-xs-6">
                                        <h5 class="mt-10"><?php esc_html_e('Sunday', 'homey-child'); ?></h5>
                                    </div>
                                    <div class="col-sm-3 col-xs-6">
                                        <div class="check-day-availability">
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab"> 
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[sun_available]" value="yes">
                                                    <span class="control-text"><?php esc_html_e('Yes', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="control control--radio radio-tab">
                                                    <input type="radio" class="radio_check_avaialable" name="timeperiod[sun_available]" value="no" checked="checked">
                                                    <span class="control-text"><?php esc_html_e('No', 'homey-child'); ?></span>
                                                    <span class="control__indicator"></span>
                                                    <span class="radio-tab-inner"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[sun_open_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 8) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-3 col-xs-6 check_is_available" style="display: none;">
                                        <select class="form-control" name="timeperiod[sun_close_time]">
                                            <?php
                                                $times = homey_get_times_options();
                                                foreach ($times as $key => $time) {
                                                    if($key == 20) {
                                                        $selected = 'selected';
                                                    }else{
                                                        $selected = '';
                                                    }
                                                    echo '<option '.$selected.' value="'.$key.'">'.$time.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(@$hide_fields['affiliate_booking_link'] != 1) { ?>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="affiliate_booking_link"> <?php echo esc_attr(homey_option('ad_affiliate_booking_link', 'Affiliate Booking Link')); ?> </label>
                        <input type="text" class="form-control" name="affiliate_booking_link" id="affiliate_booking_link" placeholder="<?php echo esc_attr(homey_option('ad_affiliate_booking_link_plac', 'Enter Affiliate Booking Link'));?>">
                    </div>
                </div>
                <?php } ?>

                <?php
                if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                
                    if(!empty($fields_array)) {
                        foreach ( $fields_array as $value ) {
                            $field_title = $value->label;
                            $field_name = $value->field_id;
                            $field_type = $value->type;
                            $field_placeholder = $value->placeholder;

                            $field_title = homey_wpml_translate_single_string($field_title);

                            $field_placeholder = homey_wpml_translate_single_string($field_placeholder);

                            if($field_type == 'select') {

                                $options = unserialize($value->fvalues);
                                $options_option = '';
                                foreach ($options as $key => $val) {
                                    $val = homey_wpml_translate_single_string($val);
                                    $options_option .= '<option value="'.$key.'">'.$val.'</option>';
                                }

                                if($hide_fields[$field_name] != 1) {
                                    echo '<div class="col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="'.$field_name.'">'.$field_title.homey_req($field_name).'</label>
                                            <select '.homey_get_required($field_name).' name="'.$field_name.'" class="selectpicker" data-live-search="true" data-live-search-style="begins">
                                                <option selected="selected" value="-1">'.esc_html__($field_placeholder, 'homey').'</option>
                                                '.$options_option.'
                                            </select>
                                        </div>
                                    </div>';
                                }

                            } elseif($field_type == 'text') {
                                
                                if($hide_fields[$field_name] != 1) {
                                    echo '<div class="col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="'.$field_name.'">'.$field_title.homey_req($field_name).'</label>
                                            <input '.homey_get_required($field_name).' class="form-control" name="'.$field_name.'" placeholder="'.$field_placeholder.'">
                                        </div>
                                    </div>';
                                }
                            }
                        } 
                    }
                }
                ?>

            </div>
        </div>
    </div>

    <?php if($hide_fields['section_openning'] != 1) { ?>
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr(homey_option('ad_section_openning')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div class="row">
                <div class="row">
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <label for="property-title"><?php echo esc_attr(homey_option('ad_mon_fri')); ?></label>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <select name="mon_fri_open" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                                <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                                <?php 
                                foreach ($openning_hours_list_array as $hour) {
                                    echo '<option value="'.trim($hour).'">'.esc_html__(trim($hour), 'homey').'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <select name="mon_fri_close" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                                <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                                <?php 
                                foreach ($openning_hours_list_array as $hour) {
                                    echo '<option value="'.trim($hour).'">'.esc_html__(trim($hour), 'homey').'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <label class="control control--checkbox radio-tab">
                                <input name="mon_fri_closed" type="checkbox">
                                <span class="contro-text"><?php echo esc_attr(homey_option('ad_close')); ?></span>
                                <span class="control__indicator"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <label for="property-title"><?php echo esc_attr(homey_option('ad_sat')); ?></label>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <select name="sat_open" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                                <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                                <?php 
                                foreach ($openning_hours_list_array as $hour) {
                                    echo '<option value="'.trim($hour).'">'.esc_html__(trim($hour), 'homey').'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <select name="sat_close" class="selectpicker" id="property-type" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                                <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                                <?php 
                                foreach ($openning_hours_list_array as $hour) {
                                    echo '<option value="'.trim($hour).'">'.esc_html__(trim($hour), 'homey').'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <label class="control control--checkbox radio-tab">
                                <input name="sat_closed" type="checkbox">
                                <span class="contro-text"><?php echo esc_attr(homey_option('ad_close')); ?></span>
                                <span class="control__indicator"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <label for="property-title"><?php echo esc_attr(homey_option('ad_sun')); ?></label>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <select name="sun_open" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                                <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                                <?php 
                                foreach ($openning_hours_list_array as $hour) {
                                    echo '<option value="'.trim($hour).'">'.esc_html__(trim($hour), 'homey').'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <select name="sun_close" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                                <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                                <?php 
                                foreach ($openning_hours_list_array as $hour) {
                                    echo '<option value="'.trim($hour).'">'.esc_html__(trim($hour), 'homey').'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="form-group">
                            <label class="control control--checkbox radio-tab">
                                <input name="sun_closed" type="checkbox">
                                <span class="contro-text"><?php echo esc_attr(homey_option('ad_close')); ?></span>
                                <span class="control__indicator"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } // End openning Hours ?>
</div>