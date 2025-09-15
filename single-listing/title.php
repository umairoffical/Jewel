<?php
global $post, $homey_prefix, $listing_author;

$detail_layout = homey_option('detail_layout');
if( isset( $_GET['detail_layout'] ) ) {
    $detail_layout = $_GET['detail_layout'];
}
$title_class = 'block-top-title block';
if( $detail_layout == 'v5' || $detail_layout == 'v6' ) {
    $title_class = 'block-top-title';
}

$is_superhost = $listing_author['is_superhost'];

$rating = homey_option('rating');
$total_rating = get_post_meta( $post->ID, 'listing_total_rating', true );
$homey_permalink = get_the_permalink();
$homey_permalink_review_link = $homey_permalink.'#reviews-section';
?>
<div class="title-section title-v1">
    <div class="<?php echo esc_attr($title_class); ?>">
        <div class="block-body">
            <?php get_template_part('template-parts/breadcrumb'); ?>
            <h1 class="listing-title"><?php the_title(); ?> <?php homey_listing_featured(get_the_ID()); ?></h1>
            <?php //if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
                <!-- <div class="rating">
                    <?php //echo homey_get_review_v2($total_rating, $post->ID, $homey_permalink_review_link); ?>
                </div> -->
            <?php //} ?>

            <?php get_template_part('single-listing/item-address', null, array('address_tag_class' => 'item-address', 'prefix_address' => '<i class="homey-icon homey-icon-style-two-pin-marker"></i>', 'postfix_address' => '')); ?>

           

            <?php if($is_superhost) { ?>
                <div class="superhost-info-icon">
                    <i class="homey-icon homey-icon-single-neutral-circle"></i> <?php echo esc_html__('Superhost', 'homey');?>
                </div>
            <?php } ?>

            <?php if($is_superhost) { ?>
                <div class="host-avatar-wrap avatar">
                    <span class="super-host-icon">
                        <i class="homey-icon homey-icon-award-badge-1"></i>
                    </span>
                    <?php echo ''.$listing_author['photo']; ?>
                </div>
            <?php } ?>

            <div class="listing-contact-save hidden-xs">
                <?php
                $fav_icon_class = "homey-icon-love-it";
                $currentSaveText = esc_html__('Save', 'homey');
                if(homey_check_is_favorite($post->ID) > 0){
                    $fav_icon_class = "homey-icon-love-it-full-01";
                    $currentSaveText = esc_html('Saved', 'homey');
                } ?>
                <a class="add_fav"
                   data-saved-text="<?php echo esc_html__('Saved', 'homey');?>"
                   data-save-text="<?php echo esc_html__('Save', 'homey');?>"
                   data-removed-text="<?php echo esc_html__('Removed', 'homey');?>"
                   data-remove-text="<?php echo esc_html__('Remove', 'homey');?>"

                   data-single-page="1" data-listid="<?php echo $post->ID; ?>"  href="javascript:void(0);"><i class="homey-icon <?php echo $fav_icon_class; ?>"></i><?php echo esc_html__($currentSaveText, 'homey');?></a>
                <a class="homey-print" id="homey-print" data-listing-id="<?php echo $post->ID; ?>" href="javascript:void(0);"><i class="homey-icon homey-icon-print-text"></i><?php echo esc_html__('Print', 'homey');?></a>
            </div>
        </div><!-- block-body -->
    </div><!-- block -->
</div><!-- title-section -->
