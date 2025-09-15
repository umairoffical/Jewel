<?php

if (!defined('ABSPATH')) {
    exit;
}

class Homey_Overtime_Threads {
    private static $instance = null;
    private $table_name;
    
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'homey_overtime_threads';
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Homey_Overtime_Threads();
        }
        return self::$instance;
    }

    // Create table on theme activation
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            thread_id bigint(20) NOT NULL,
            sender_id bigint(20) NOT NULL,
            receiver_id bigint(20) NOT NULL,
            message_id bigint(20) NOT NULL,
            price_per_hour decimal(10,2) NOT NULL,
            extra_hour int NOT NULL,
            total_amount decimal(10,2) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            new_reservation_id bigint(20) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY thread_id (thread_id),
            KEY sender_id (sender_id),
            KEY receiver_id (receiver_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Debug: Check if table was created
        if ($this->check_table_exists()) {
            error_log('Overtime table created successfully');
            return true;
        } else {
            error_log('Failed to create overtime table');
            return false;
        }
    }

    // Check if table exists
    public function check_table_exists() {
        global $wpdb;
        $result = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'");
        return $result === $this->table_name;
    }

    // Insert new overtime record
    public function insert($data) {
        global $wpdb;
        
        $defaults = array(
            'thread_id' => 0,
            'sender_id' => 0,
            'receiver_id' => 0,
            'message_id' => 0,
            'price_per_hour' => 0,
            'extra_hour' => 0,
            'total_amount' => 0,
            'status' => 'pending',
            'new_reservation_id' => 0,
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['thread_id']) || empty($data['sender_id']) || 
            empty($data['receiver_id']) || empty($data['price_per_hour']) || 
            empty($data['extra_hour']) || empty($data['message_id'])) {
            return false;
        }
        
        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'thread_id' => $data['thread_id'],
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'message_id' => $data['message_id'],
                'price_per_hour' => $data['price_per_hour'],
                'extra_hour' => $data['extra_hour'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
                'new_reservation_id' => $data['new_reservation_id']
            ),
            array('%d', '%d', '%d', '%d', '%f', '%d', '%f', '%s', '%d')
        );
        
        return $inserted ? $wpdb->insert_id : false;
    }

    // Get record by thread ID
    public function get_by_thread_id($thread_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE thread_id = %d",
                $thread_id
            )
        );
    }

    // Get records by sender ID
    public function get_by_sender_id($sender_id) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE sender_id = %d ORDER BY created_at DESC",
                $sender_id
            )
        );
    }

    // Get records by receiver ID
    public function get_by_receiver_id($receiver_id) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE receiver_id = %d ORDER BY created_at DESC",
                $receiver_id
            )
        );
    }

    // Update status
    public function update_status($id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }

    // Get records by status
    public function get_by_status($status) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE status = %s ORDER BY created_at DESC",
                $status
            )
        );
    }

    // Delete record
    public function delete($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }
}

// Function to initialize table
function homey_overtime_init_table() {
    error_log('Attempting to create overtime table...');
    $overtime = Homey_Overtime_Threads::getInstance();
    return $overtime->create_table();
}

// Hook for theme activation
add_action('after_switch_theme', 'homey_overtime_init_table');

// Hook for when plugins are loaded (as backup)
add_action('plugins_loaded', 'homey_overtime_init_table');

// Add to init hook as well (third attempt if others fail)
add_action('init', 'homey_overtime_init_table', 0);

// Function to manually check table
function homey_check_overtime_table() {
    $overtime = Homey_Overtime_Threads::getInstance();
    if ($overtime->check_table_exists()) {
        return "Table exists!";
    } else {
        return "Table does not exist!";
    }
}

// Function to drop and recreate the table
function homey_drop_and_recreate_overtime_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'homey_overtime_threads';
    
    // Drop the table
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    
    // Recreate the table
    return homey_overtime_init_table();
}

// Helper functions for easy access
function homey_add_overtime($data) {
    $overtime = Homey_Overtime_Threads::getInstance();
    return $overtime->insert($data);
}

function homey_get_overtime_by_thread($thread_id) {
    $overtime = Homey_Overtime_Threads::getInstance();
    return $overtime->get_by_thread_id($thread_id);
}

function homey_get_overtime_by_sender($sender_id) {
    $overtime = Homey_Overtime_Threads::getInstance();
    return $overtime->get_by_sender_id($sender_id);
}

function homey_get_overtime_by_receiver($receiver_id) {
    $overtime = Homey_Overtime_Threads::getInstance();
    return $overtime->get_by_receiver_id($receiver_id);
}

function homey_update_overtime_status($id, $status) {
    $overtime = Homey_Overtime_Threads::getInstance();
    return $overtime->update_status($id, $status);
}

// get record by message id
function homey_get_overtime_by_message_id($message_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'homey_overtime_threads';
    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE message_id = %d",
            $message_id
        )
    );
    return $result;
}

// update reservation_id by message_id
function homey_update_reservation_id_by_message_id($message_id, $reservation_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'homey_overtime_threads';
    $wpdb->update($table_name, array('new_reservation_id' => $reservation_id), array('message_id' => $message_id));
}

// Example usage:
/*
$data = array(
    'thread_id' => 123,
    'sender_id' => get_current_user_id(),
    'receiver_id' => 456,
    'price_per_hour' => 25.00,
    'extra_hour' => 2,
    'total_amount' => 50.00,
    'status' => 'pending'
);
$overtime_id = homey_add_overtime($data);
*/
