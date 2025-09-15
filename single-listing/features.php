<?php
global $post, $homey_local;
$amenities   = wp_get_post_terms( get_the_ID(), 'listing_amenity', array("fields" => "all"));
$facilities  = wp_get_post_terms( get_the_ID(), 'listing_facility', array("fields" => "all"));
$accessibilites  = wp_get_post_terms( get_the_ID(), 'listing_accessibility', array("fields" => "all"));

if(!empty($amenities) || !empty($facilities) || !empty($parkings)) { ?>
<div id="features-section" class="features-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <?php if(!empty($amenities)) { ?>
                <h3 class="title"><?php esc_html_e('Amenities','homey-child'); ?></h3>
                <ul class="detail-list detail-list-2-cols">
                    <?php foreach($amenities as $amenity): ?>
                        <li><i class="<?php echo $amenity->description != '' ? $amenity->description : 'homey-icon homey-icon-arrow-right-1'; ?>" aria-hidden="true"></i> <?php echo esc_attr($amenity->name); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php } ?>
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <?php if(!empty($facilities)) { ?>
                <h3 class="title"><?php echo esc_attr(homey_option('sn_features')); ?></h3>
                <ul class="detail-list detail-list-2-cols">
                    <?php foreach($facilities as $facility): ?>
                        <li><i class="<?php echo $facility->description != '' ? $facility->description : 'homey-icon homey-icon-arrow-right-1'; ?>" aria-hidden="true"></i> <?php echo esc_attr($facility->name); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <?php if(!empty($accessibilites)) { ?>
                <h3 class="title"><?php esc_html_e('Accessibility','homey-child'); ?></h3>
                <ul class="detail-list detail-list-2-cols">
                    <?php foreach($accessibilites as $accessibility): ?>
                        <li><i class="<?php echo $accessibility->description != '' ? $accessibility->description : 'homey-icon homey-icon-arrow-right-1'; ?>" aria-hidden="true"></i> <?php echo esc_attr($accessibility->name); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>