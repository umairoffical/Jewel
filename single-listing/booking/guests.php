<?php
global $homey_local;
$booking_hide_fields = homey_option('booking_hide_fields');

$guest_prices = get_post_meta(get_the_ID(), 'homey_guest_price', true);

if(@$booking_hide_fields['guests'] != 1) {
?>
<div class="search-guests single-guests-js">

	<?php 
		if(!empty($guest_prices)):
			?>
				<select name="guests" id="select-listings-guests" class="form-control selectpicker select-listings-guests">
					<option value=""><?php esc_html_e("Please Select Guest",'homey-child');?></option>
					<?php
					foreach($guest_prices as $key => $price):
						if($price['available'] == 'yes' && !empty($price['price'])):
							?>
								<option value="<?php echo $price['price'];?>"><?php echo str_replace('_', ' ', $key);?> <?php esc_html_e('Guests | ','homey-child');?><?php echo homey_formatted_price($price['price'], true, true);?></option>
							<?php
						endif;
					endforeach;
					?>
				</select>
			<?php
		endif; 
	?>
</div>
<?php } ?>