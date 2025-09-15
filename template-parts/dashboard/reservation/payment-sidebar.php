<?php
global $reservationID;
$reservation_type = get_post_meta($reservationID, 'reservation_type', true) ?? '';
$reservation_overtime_hours = get_post_meta($reservationID, 'reservation_overtime_hours', true) ?? '';
$reservation_overtime_price_per_hour = get_post_meta($reservationID, 'reservation_overtime_price_per_hour', true) ?? '';
$totalprice = get_post_meta($reservationID, 'reservation_upfront', true);
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
				?>
					<div class="payment-list-price-detail clearfix">
						<div class="pull-left">
							<div class="payment-list-price-detail-total-price"><?php esc_html_e('Total', 'homey'); ?></div>
						</div>
						<div class="pull-right text-right">
							<div class="payment-list-price-detail-total-price"><?php echo $totalprice; ?></div>
						</div>
					</div>
					<div class="reservation-detail-page-payment" id="">
						<ul>
							<li class="homey_price_first"><?php echo $reservation_overtime_hours; ?> x <?php esc_html_e('Hours', 'homey'); ?> <span><?php echo $totalprice; ?></span></li>
						</ul>
					</div>
				<?php
			}else{

				echo homey_calculate_reservation_cost_day_date_child($reservationID);

			}
		    ?>

		</div><!-- payment-list --> 
	</div><!-- block-body -->
</div>