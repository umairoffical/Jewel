<?php 

function homey_create_extra_fields($sections){
    $allowed_html_array = array();
    $sections[]=  array(
        'title' => esc_html__('Extra Fields', 'homey-child' ),
        'icon' => 'dashicons dashicons-admin-settings',
        'heading' => 'Extra Fields',
        'id' => 'explore-beds',
        'fields' =>  [
            [
                'id'       => 'homey_extra_fields',
                'type'     => 'section',
                'title'    => esc_html__( 'Extra Fields', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'cancellation_policy_text',
                'type'     => 'editor',
                'title'    => esc_html__('Cancellation Policy Text', 'homey'),
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
add_filter("redux/options/homey_options/sections", 'homey_create_extra_fields');