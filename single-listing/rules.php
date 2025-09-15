<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$smoke            = homey_get_listing_data('smoke');
$pets             = homey_get_listing_data('pets');
$party            = homey_get_listing_data('party');
$children         = homey_get_listing_data('children');
$additional_rules = homey_get_listing_data('additional_rules');

$cancellation_policy = homey_option('cancellation_policy_text');
$overtime_policy = homey_option('overtime_policy_text');

$rules = get_post_meta(get_the_ID(), 'homey_rules', true);
$location_rules = get_post_meta(get_the_ID(), 'homey_additional_rules', true);
?>
<div id="rules-section" class="rules-section">
    <?php if(!empty($location_rules)) { ?>
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <h3 class="title mb-10"><?php esc_html_e('Location Rules', 'homey-child'); ?></h3>
                <div><?php echo $location_rules; ?></div>
            </div>
        </div>
    </div>
    <?php } ?>
    <?php if(!empty($rules)) { ?>
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_terms_rules')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <ul class="rules_list detail-list">
                        <?php foreach($rules as $rule) { ?>
                        <li>
                            <i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i>
                            <?php echo $rule['title'] ?>:
                            <strong><?php echo ucfirst($rule['is_allowed']); ?></strong>
                        </li> 
                        <?php } ?>
                    </ul>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
    <?php } ?>
    <?php if(!empty($cancellation_policy)) { ?>
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <h3 class="title mb-10"><?php echo esc_attr($homey_local['cancel_policy']); ?></h3>
                <div><?php echo $cancellation_policy; ?></div>
            </div>
        </div>
    </div>
    <?php } ?>
    <?php if(!empty($overtime_policy)) { ?>
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <h3 class="title mb-10"><?php esc_html_e('Overtime Policy', 'homey-child'); ?></h3>
                <div><?php echo $overtime_policy; ?></div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
