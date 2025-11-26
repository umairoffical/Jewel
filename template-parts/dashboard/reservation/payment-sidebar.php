<?php
global $reservationID;
$reservation_type = get_post_meta($reservationID, 'reservation_type', true) ?? '';
$reservation_overtime_hours = get_post_meta($reservationID, 'reservation_overtime_hours', true) ?? '';
$reservation_overtime_price_per_hour = get_post_meta($reservationID, 'reservation_overtime_price_per_hour', true) ?? '';
$totalprice = get_post_meta($reservationID, 'reservation_upfront', true);
$fullamount = get_post_meta($reservationID, 'reservation_total', true);
$host_fee = homey_option('host_fee');
$general_tax = homey_option('taxes_percent');
if(!empty($general_tax) && $general_tax > 0){
	$general_tax = $general_tax / 100;
	$general_tax_amount = $totalprice * $general_tax;
	$totalprice = $totalprice - $general_tax_amount;
}
if($host_fee > 0 && $host_fee > 0){
	$host_fee = $host_fee / 100;
	$host_fee_amount = $totalprice * $host_fee;
	$totalprice = $totalprice - $host_fee_amount;
}
?>
<div class="block">
	<div class="block-title">
		<h3 class="title"><?php esc_html_e('Payment', 'homey'); ?></h3>
	</div>
	<div class="block-body">
		<!-- zk in parts dash res payment-sidebar-->
		<div class="payment-list">

		    <?php 

			if($reservation_type == 'overtime_policy'){
				$totalprice = homey_formatted_price($totalprice, true);

					if(homey_is_host()){
						?>
							<div class="payment-list-price-detail clearfix">
								<div class="pull-left">
									<div class="payment-list-price-detail-total-price"><?php esc_html_e('Host Payment', 'homey'); ?></div>
								</div>
								<div class="pull-right text-right">
									<div class="payment-list-price-detail-total-price"><?php echo $totalprice; ?></div>
								</div>
							</div>
							<div class="reservation-detail-page-payment" id="">
								<ul>
									<li class="homey_price_first"><?php echo $reservation_overtime_hours; ?> x <?php echo homey_formatted_price($reservation_overtime_price_per_hour, true); ?> <span><?php echo homey_formatted_price($fullamount, true); ?></span></li>
									<?php if(!empty($general_tax) && $general_tax > 0){ ?>
										<li class="homey_price_first"><?php esc_html_e('Services Fee', 'homey'); ?> <span><?php echo homey_formatted_price($general_tax_amount, false); ?></span></li>
									<?php } ?>
									<?php if(!empty($host_fee) && $host_fee > 0){ ?>
										<li class="homey_price_first"><?php esc_html_e('Platform Fee', 'homey'); ?> <span><?php echo homey_formatted_price($host_fee_amount, false); ?></span></li>
									<?php } ?>
								</ul>
							</div>
						<?php
					}else{
						?>
							<div class="payment-list-price-detail clearfix">
								<div class="pull-left">
									<div class="payment-list-price-detail-total-price"><?php esc_html_e('Total', 'homey'); ?></div>
								</div>
								<div class="pull-right text-right">
									<div class="payment-list-price-detail-total-price"><?php echo homey_formatted_price($fullamount, true); ?></div>
								</div>
							</div>
							<div class="reservation-detail-page-payment" id="">
								<ul>
									<li class="homey_price_first"><?php echo $reservation_overtime_hours; ?> x <?php echo homey_formatted_price($reservation_overtime_price_per_hour, true); ?> <span><?php echo homey_formatted_price($fullamount, true); ?></span></li>
								</ul>
							</div>
						<?php
					}
			}else{
				echo homey_calculate_reservation_cost_day_date_child($reservationID);
			}
		    ?>

		</div><!-- payment-list --> 
	</div><!-- block-body -->
</div>