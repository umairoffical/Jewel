<?php
global $homey_local, $hide_fields;
?>
<div class="form-step form-step-features">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr(homey_option('ad_features')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <?php if($hide_fields['amenities'] != 1) { ?>
            <div class="listing-form-row mb-20">
                <div class="house-features-list">
                    <label class="label-title mb-0" style="font-size: 16px; font-weight: 600;"><?php echo esc_attr(homey_option('ad_amenities')); ?></label>
                    <p class="mb-15" style="margin-top: -5px;"><?php esc_html_e('Select any that apply & add what we may have missed!','homey-child');?></p>

                    <?php
                    $amenities = get_terms( 'listing_amenity', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                    if (!empty($amenities)) {
                        $count = 1;
                        foreach ($amenities as $amenity) {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_amenity[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '">';
                                echo '<span class="contro-text">'.esc_attr( $amenity->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                            $count++;
                        }
                    }
                    ?>

                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['facilities'] != 1) { ?>
            <div class="listing-form-row">
                <div class="house-features-list">
                    <label class="label-title mb-0" style="font-size: 16px; font-weight: 600;"><?php echo esc_attr(homey_option('ad_facilities')); ?></label>
                    <p class="mb-15" style="margin-top: -5px;"><?php esc_html_e('Cool features? Check all that apply.','homey-child');?></p>
                    <?php
                    $facilities = get_terms( 'listing_facility', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                    if (!empty($facilities)) {
                        $count = 1;
                        foreach ($facilities as $facility) {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_facility[]" id="facility-' . esc_attr( $facility->slug ). '" value="' . esc_attr( $facility->term_id ). '">';
                                echo '<span class="contro-text">'.esc_attr( $facility->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                            $count++;
                        }
                    }
                    ?>
                </div>
            </div>
            <?php } ?>
            
        </div>
    </div>
</div>