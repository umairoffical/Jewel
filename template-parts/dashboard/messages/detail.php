<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/12/16
 * Time: 8:13 PM
 */

global $wpdb, $current_user;

wp_get_current_user();
$current_user_id =  $current_user->ID;
$tabel = $wpdb->prefix . 'homey_threads';
$thread_id = intval($_REQUEST['thread_id']);
$user_status = 'Offline';

$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ASC';

if ( isset( $_GET['seen'] ) && $_GET['seen'] == 1 ) {
    homey_update_message_status( $current_user_id, $thread_id );
}

$sql_thread = $wpdb->prepare(
    "
    SELECT * 
    FROM $tabel 
    WHERE id = %d
    ",
    $thread_id
);

$homey_thread = $wpdb->get_row($sql_thread);

$homey_thread_messages = $wpdb->prefix . 'homey_thread_messages';

$sql_messages = $wpdb->prepare(
    "
    SELECT * 
    FROM $homey_thread_messages 
    WHERE thread_id = %d
    ORDER BY id ".$sort,
    $thread_id
);

$homey_messages = $wpdb->get_results($sql_messages);

$thread_author = $homey_thread->sender_id;
$thread_sender_delete = $homey_thread->sender_delete;
$thread_receiver_delete = $homey_thread->receiver_delete;

if ( $thread_author == $current_user_id ) {
    $thread_author = $homey_thread->receiver_id;
}

$sender_id = $homey_thread->sender_id;
$receiver_id = $homey_thread->receiver_id;

$user_can_reply = false;
if($sender_id == $current_user_id || $receiver_id == 0 || $receiver_id == $current_user_id || homey_is_admin()) {
    $user_can_reply = true;
}


$thread_author_first_name  =  get_the_author_meta( 'first_name', $thread_author );
$thread_author_last_name  =  get_the_author_meta( 'last_name', $thread_author );
$thread_author_display_name = get_the_author_meta( 'display_name', $thread_author );
if( !empty($thread_author_first_name) && !empty($thread_author_last_name) ) {
    $thread_author_display_name = $thread_author_first_name.' '.$thread_author_last_name;
}

// $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $thread_author );
// $image_array = wp_get_attachment_image_src( $author_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

$homey_author_info = homey_get_author_by_id('60', '60', 'img-circle', $thread_author);
$user_custom_picture = $homey_author_info['photo'];

if( !$user_custom_picture ) {
    $user_custom_picture = get_template_directory_uri().'/images/profile-avatar.png';
}

//making reservation link
if($homey_thread->sender_id != $current_user->ID){
    $dashboard_reservations = homey_get_template_link('template/dashboard-reservations.php');
}else{
    $dashboard_reservations = homey_get_template_link('template/dashboard-reservations2.php');
}

$reservation_url  = add_query_arg(
    array(
        'reservation_detail' => $homey_thread->listing_id
    ),
    $dashboard_reservations
);

//end of making reservation Url
?>

<?php if ( isset( $_GET['success'] ) && $_GET['success'] == true ) { ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
        <?php esc_html_e( 'The message has been sent.', 'homey' ); ?>
    </div>
<?php } ?>

<?php if ( isset( $_GET['success'] ) && $_GET['success'] == false ) { ?>
    <div class="alert alert-error alert-dismissible" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
        <?php esc_html_e( 'Oopps something getting wrong, please try again!', 'homey' ); ?>
    </div>
<?php } ?>

<?php
if( $user_can_reply || homey_is_admin() > 0 ) { ?>
<div class="messages-area-user-info">
    <div class="messages-area-user-status">
        <div class="media">
            <div class="media-left">
                <a class="media-object">
                    <?php echo  $user_custom_picture ; ?>
                    <!-- <img src="<?php echo esc_url( $user_custom_picture ); ?>" class="img-circle" alt="<?php echo esc_attr($thread_author_display_name); ?>"> -->
                </a>
            </div>
            <div class="media-body media-middle">
                <div class="msg-user-info">
                    <div class="msg-user-left">
                        <a><strong><?php echo ucfirst( $thread_author_display_name ); ?></strong> <?php esc_html_e('on', 'homey'); ?> <strong><a href="<?php echo esc_url( $reservation_url ).'&message='.intval($homey_thread->id); ?>"><?php echo get_the_title( $homey_thread->listing_id ); ?></strong></a></div>
                        <div class="user-status">
                            <?php
                            if ( homey_is_user_online( $thread_author ) ) {
                                echo '<i class="homey-icon homey-icon-circle text-success" aria-hidden="true"></i> '.esc_html__('Status:', 'homey').' <strong>'.esc_html__('Online', 'homey').'</strong>';
                            } else {
                                echo '<i class="homey-icon homey-icon-circle text-danger" aria-hidden="true"></i> '.esc_html__('Status:', 'homey').' <strong>'.esc_html__('Offline', 'homey').'</strong>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="messages-area">
    
    <div class="msgs-reply-list">
        
        <?php foreach ( $homey_messages AS $message ) {

            $message_class = '';
            $message_id = $message->id;
            $message_author = $message->created_by;

            if(!empty($message->id)){
                $overtime_message = homey_get_overtime_by_message_id($message->id);
                if(!empty($overtime_message)){
                    $overtime_message_class = 'overtime-message';
                    $message_status = $overtime_message->status;
                    $new_reservation_id = $overtime_message->new_reservation_id;
                    $messge_type = 'overtime';
                }else{
                    $overtime_message_class = '';
                    $message_status = '';
                    $messge_type = '';
                }
            }
            
            $message_author_last_name  =  get_the_author_meta( 'last_name', $message_author );
            $message_author_display_name = get_the_author_meta( 'display_name', $message_author );
            if( !empty($message_author_first_name) && !empty($message_author_last_name) ) {
            $message_author_display_name = $message_author_first_name.' '.$message_author_last_name;
            }
            $message_author_name = ucfirst( $message_author_display_name );
           
            if ( $message_author == $current_user_id ) {
                $message_author_name = esc_html__( 'Me', 'homey' );
                $message_class = 'msg-me';
            }

            // $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $message_author );
            // $image_array = wp_get_attachment_image_src( $author_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

            $message_owner_info = homey_get_author_by_id('60', '60', 'img-circle', $message_author);
            $message_author_picture = $message_owner_info['photo'];

            if ( empty( @$message_author_picture )) {
                $message_author_picture = get_template_directory_uri().'/images/profile-avatar.png';
            }

            if($current_user_id == $message_author) {
                $delete = $message->sender_delete;
            } else {
                $delete = $message->receiver_delete;
            }

            if($delete != 1) {
            ?>
            <div id="message-<?php echo intval($message_id); ?>" class="media <?php echo $overtime_message_class; ?> <?php echo esc_attr($message_class); ?>">
                <div class="media-left">
                    <a href="#" class="media-object">
                        <?php echo $message_author_picture; ?>
                    </a>
                </div>

                <div class="media-body">
                    <div class="msg-user-info">
                        <div class="msg-user-left">
                            <div><strong><?php echo esc_attr($message_author_name); ?></strong>
                                <span class="message-date">
                                    <span><i class="homey-icon homey-icon-calendar-3"></i> <?php echo date_i18n( homey_convert_date(homey_option('homey_date_format')), strtotime( $message->time ) ); ?> </span>
                                    <span><i class="homey-icon homey-icon-time-clock-circle"></i>  <?php echo date_i18n( get_option('time_format'), strtotime( $message->time ) ); ?> </span>
                                    <?php
                                    if(!empty($message_status)){
                                        if($message_status == 'pending'){
                                            echo '<span style="background-color: #FFC107; padding: 2px 7px; font-weight: 600; margin-left: 10px; border-radius: 4px;">'.esc_html__('Pending', 'homey').'</span>';
                                        }elseif($message_status == 'approved'){
                                            echo '<span style="background-color: #40813F; padding: 2px 7px; font-weight: 600; margin-left: 10px; border-radius: 4px; color: #fff;">'.esc_html__('Approved    ', 'homey').'</span>';
                                        }elseif($message_status == 'rejected'){
                                            echo '<span style="background-color: #FF0000; padding: 2px 7px; font-weight: 600; margin-left: 10px; border-radius: 4px; color: #fff;">'.esc_html__('Rejected', 'homey').'</span>';
                                        }
                                    }
                                    ?>
                                </span>

                            </div>
                        </div>
                        <?php if($user_can_reply == 1 || homey_is_admin() > 0 ) { ?>
                        <!-- <div class="custom-actions">
                            <button class="homey_delete_message btn-action" data-message-id="<?php //echo intval($message_id); ?>" data-created-by="<?php //echo intval($message_author); ?>" data-toggle="tooltip" data-placement="top" title="<?php //esc_attr_e('Delete', 'homey'); ?>"><i class="homey-icon homey-icon-bin-1-interface-essential"></i></button>
                        </div> -->
                        <?php } ?>
                    </div>

                    <?php if($messge_type == 'overtime'){ ?>
                        <p><?php echo nl2br(str_replace("\\","", wp_specialchars_decode($message->message))); ?></p>
                    <?php }else{ ?>
                        <p><?php echo str_replace("\\","", wp_specialchars_decode($message->message)); ?></p>
                    <?php } ?>

                    <?php
                    if ( !empty( $message->attachments ) ) {

                        $attachments = unserialize( $message->attachments );

                        if ( sizeof( $attachments ) ) {

                            echo '<ul>';

                            foreach ( $attachments AS $attachment ) {

                                $attachment_url  = wp_get_attachment_url( $attachment );

                                echo '<li> <a href="' . esc_url($attachment_url) . '" target="_blank"> ' . get_the_title( $attachment ) . ' </a> </li>';
                            }

                            echo '</ul>';

                        }

                    }
                    ?>

                    <?php 

                    if($message_status == 'approved' && !empty($new_reservation_id)){
                        $reservation_page = homey_get_template_link_dash('template/dashboard-reservations.php');
                        $reservation_detail_link = add_query_arg('reservation_detail', $new_reservation_id, $reservation_page);
                        ?>
                            <div class="show-reservation-detail">
                                <a href="<?php echo esc_url($reservation_detail_link); ?>" target="_blank"><?php esc_html_e('View Reservation', 'homey'); ?></a>
                            </div>
                        <?php
                    }

                    if($messge_type == 'overtime' && $message_status == 'pending' && homey_is_renter()){
                        ?>
                        <div class="overtime-message-actions">
                            <a href="#" data-message-id="<?php echo intval($message_id); ?>" data-thread-id="<?php echo intval($thread_id); ?>" class="btn btn-primary approve-overtime-message"><?php esc_html_e('Approve', 'homey'); ?></a>
                            <a href="#" data-message-id="<?php echo intval($message_id); ?>" data-thread-id="<?php echo intval($thread_id); ?>" class="btn btn-danger reject-overtime-message"><?php esc_html_e('Reject', 'homey'); ?></a>
                        </div>
                        <?php
                    }
                    ?>

                </div>

            </div>
            <?php }
        }?>
        
    </div>
    <div class="media msg-send-block">
        
        <?php
        if($user_can_reply == 1 || homey_is_admin() > 0 ) {
        if($thread_sender_delete != 1 && $thread_receiver_delete != 1) { ?>
        <div class="media-left">
            <div class="media-object">
                <?php
                // $current_user_picture_id =  intval(get_the_author_meta( 'homey_author_picture_id' , $current_user_id ));

                $homey_current_user_info = homey_get_author_by_id('60', '60', 'img-circle', $current_user_id);
                $author_photo = $homey_current_user_info['photo'];

                // $image_array = wp_get_attachment_image_src( $current_user_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

                if ( empty( @$author_photo )) {
                    $current_user_picture = get_template_directory_uri().'/images/profile-avatar.png'; 
                    ?>

                        <img src="<?php echo esc_url($current_user_picture); ?>" class="img-circle" alt="<?php the_author_meta( 'display_name', $current_user_id ) ?>">
                
                <?php }else{
                    echo $current_user_picture = $author_photo;
                }

                ?>
                
            </div>
        </div>
        <div class="media-body">
            <h4 class="media-heading"><?php echo esc_html__('Reply', 'homey'); ?></h4>
            <form class="form-msg" method="post">
                <input type="hidden" name="start_thread_message_form_ajax" value="<?php echo wp_create_nonce('start-thread-message-form-nonce'); ?>"/>
                <input type="hidden" name="thread_id" value="<?php echo intval($thread_id); ?>"/>
                <input type="hidden" name="action" value="homey_thread_message">

                <div class="msg-type-block">
                    <div class="arrow"></div>
                    <textarea name="message" id="message" rows="5" class="form-control" placeholder="<?php esc_attr_e( 'Type your message here...', 'homey' ); ?>"></textarea>

                    <div class="msg-attachment-row">
                        <div class="msg-attachment">
                            <ul id="listing-thumbs-container" class="list-inline">
                                <li class="new-attach" id="thread-message-attachment">
                                    <div class="attach-icon new-attachment">
                                        <i class="homey-icon homey-icon-attachment-interface-essential"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div id="plupload-container"></div>
                        <div id="errors-log"></div>
                    </div>

                </div>
                <?php if(homey_is_admin() || homey_is_host()){ ?>
                    <div class="form-msg-btns" style="display:flex; justify-content:space-between;">
                        <?php   
                        $reservation_status = get_post_meta($homey_thread->listing_id, 'reservation_status', true);
                        if($reservation_status == 'available'){
                            ?>
                            <a href="#" style="display:block; padding:0px 25px;" data-toggle="modal" data-target="#overtimeModal" class="btn btn-primary"><?php esc_html_e('Send Overtime Quotation','homey-child');?></a>
                            <?php
                        }
                        ?>
                        <button class="btn btn-success start_thread_message_form"><?php echo esc_html__('Send', 'homey'); ?></button>
                    </div>
                <?php }else{ ?>
                    <div class="form-msg-btns">
                        <button class="btn btn-success start_thread_message_form"><?php echo esc_html__('Send', 'homey'); ?></button>
                    </div>
                <?php }?>
                <div class="messages-notification text-right"></div>
            </form>
        </div>
        <?php
        }else{
          echo '<div id="message_auth" class="error notice is-dismissible"><p>'.esc_html_e("This message thread is deleted from the owner, so you can't reply this.", 'homey').'</p></div>';
        }
    }else{
          echo '<div id="message_auth" class="error notice is-dismissible"><p>'.esc_html_e('You are not allowed to access this', 'homey').'</p></div>';
        }?>
    </div><!-- end .msg-send-block -->

</div>
<?php } else { ?>

    <div class="messages-area">
        <p><?php esc_html_e('You are not allowed to access this', 'homey'); ?></p>
    </div>

<?php } ?>
<?php get_template_part('template-parts/dashboard/messages/overtime-modal');?>
