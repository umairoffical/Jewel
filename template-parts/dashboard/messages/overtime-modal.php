<?php
global $homey_local;
$thread_id = intval($_REQUEST['thread_id']);
?>
<div class="modal fade" id="overtimeModal" tabindex="-1" role="dialog" aria-labelledby="overtimeModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" style="padding: 30px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h2 class="modal-title" style="margin-bottom: 10px;" id="overtimeModalLabel"><?php esc_html_e('Additional Hours','homey-child');?></h2>
                
                <form id="overtime-form" class="form-horizontal">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label><?php esc_html_e('Hours Overtime*','homey-child');?></label>
                        <input type="number" class="form-control" id="overtime_hours" name="overtime_hours" value="0">
                    </div>

                    <div class="form-group" style="margin-bottom: 10px;">
                        <label><?php esc_html_e('Booked Hourly Rate*','homey-child');?></label>
                        <input type="number" class="form-control" id="price_per_hour" name="price_per_hour" value="0" min="0" step="0.01">
                    </div>

                    <div class="calculation-info">
                        <div class="calculation-details">
                            <p><?php esc_html_e('Total Amount','homey-child');?> : <span id="overtime_total_amount">0</span>$</p>
                        </div>
                    </div>

                    <div class="form-group text-right" style="margin-bottom: 0px;">
                        <input type="hidden" id="start_thread_message_form_ajax" name="start_thread_message_form_ajax" value="<?php echo wp_create_nonce('start-thread-message-form-nonce'); ?>"/>
                        <input type="hidden" id="thread_id" name="thread_id" value="<?php echo intval($thread_id); ?>"/>
                        <button type="submit" style="padding:0px 25px;" class="btn btn-primary send_overtime_policy"><?php esc_html_e('Send','homey-child');?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>