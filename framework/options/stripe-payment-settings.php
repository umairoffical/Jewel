<?php 

function homey_create_stripe_payment_settings($sections){
    $allowed_html_array = array();
    $sections[]=  array(
        'title' => esc_html__('Stripe Payment Settings', 'homey-child' ),
        'icon' => 'dashicons dashicons-admin-settings',
        'heading' => 'Stripe Payment Settings',
        'id' => 'explore-beds',
        'fields' =>  [
            [
                'id'       => 'homey_stripe_payment_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Stripe Payment Settings', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'stripe_payment_settings',
                'type'     => 'editor',
                'title'    => esc_html__('Stripe Payment Settings', 'homey'),
                'default'  => 'Cancellation Policy Dummy Text',
                'args' => array(
                    'teeny' => true,
                    'wpautop' => false,
                    'media_buttons' => false,
                    'textarea_rows' => 10
                )
            ],
            [
                'id'       => 'overtime_policy_text',
                'type'     => 'editor',
                'title'    => esc_html__('Overtime Policy Text', 'homey'),
                'default'  => 'Overtime Policy Dummy Text',
                'args' => array(
                    'teeny' => true,
                    'wpautop' => false,
                    'media_buttons' => false,
                    'textarea_rows' => 10
                )
            ],
        ],
    );

    return $sections;

}
add_filter("redux/options/homey_options/sections", 'homey_create_stripe_payment_settings');