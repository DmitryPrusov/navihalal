<?php

class Halal_List_Table
{
    /**
     * Constructor will create the menu item
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_example_list_table_page'));

        add_action('admin_print_scripts', array ($this, 'admin_scripts'));
        add_action('admin_print_styles', array ($this, 'print_styles'));

        add_action('admin_post_nopriv_status_form', array($this, 'processing_status_form'));
        add_action('admin_post_status_form', array($this, 'processing_status_form'));

    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_example_list_table_page()
    {
        add_menu_page('Halal Data Table', 'Halal Data Table', 'manage_options', 'halal-data-table.php',
            array($this, 'list_table_page'));
    }


    public function  admin_scripts()
    {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'show_complete_text',  plugin_dir_url( __FILE__ ) . '/scripts/show_complete_text.js' );
        wp_enqueue_script( 'halal_status_change',  plugin_dir_url( __FILE__ ) . '/scripts/halal_status_change.js' );
    }

    public function print_styles () {
        wp_enqueue_style( 'show_complete_text', plugin_dir_url( __FILE__ ) . '/styles/show_complete_text.css');
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        $exampleListTable = new Halal_Data_Table();
        $exampleListTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Halal Data Table</h2>
            <?php $exampleListTable->display(); ?>
        </div>
        <?php
    }


    public function processing_status_form () {

        $id = absint(intval($_POST['status_id']));
        $selected_status = absint(intval($_POST['status']));

        global $wpdb;
       $result =  $wpdb->query("UPDATE wp_halal_certificate SET status = ".$selected_status. " WHERE id = ".$id);

       if ($result > 0) {
           echo json_encode(array('result' => 'success'));
           exit;
       }
       else {
           echo json_encode(array('result' => 'error'));
           exit;
       }
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Halal_Data_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
       // usort($data, array(&$this, 'sort_data'));
        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'id' => 'ID',
            'status' => 'Status',
            'date' => 'Date',
            'file' => 'File',
            'email' => 'Email',
            'number' => 'Number',
            'contact_person' => 'Contact Person',
            'company_name' => 'Company Name, Position',
            'field_activity' => 'Field of Activity',
           // 'position' => 'Position',
            'comment' => 'Comment',
            'type' => 'Type'
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        global $wpdb;
        $results = $wpdb->get_results("select id, status, date, file, email, number, contact_person, company_name,
        field_activity, position, comment, type  FROM wp_halal_certificate order by id desc", ARRAY_A);
        $data = $results;
        $array_types = array(1 => "Request", 2 => "Appeal", 3 => "Complain");
        $array_status = array(1 => "New", 2 => "Processed", 3 => "Closed");

        foreach ($data as $key => $client_input) {
            $link = '<a href="' . $client_input['file'] . '" target="_blank">download</a>';
            $comment = '<div class="more">'. $client_input['comment'].'</div>';
            $options = "";
            $status_id = $client_input['status'];

            for ($i=1; $i<=3; $i++) {
                $options.= '<option value="'.$i.'" '. (($i==$status_id)? 'selected="selected"' : '').'>'.$array_status[$i].'</option>';

            }
            $status = '<form action=""  method="post" id="status_form">
                                    <select name="status" id="selected_status">'
                                    .$options.
                                    '</select>
                                        <input type="submit" value="ok">
                                        <input type="hidden" name="status_id" id="status_id" value="'.$client_input['id'].'" />
                        </form>';
            $data[$key]['status'] = $status;
            $data[$key]['file'] = $link;
            $data[$key]['comment'] = $comment;
            $data[$key]['company_name'] = $client_input['company_name'] . ", ". $client_input['position'];
            unset($data[$key]['position']);
            $data[$key]['type'] = $array_types[$client_input['type']];
        }
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'status':
            case 'date':
            case 'file':
            case 'email':
            case 'number':
            case 'contact_person':
            case 'company_name':
            case 'field_activity':
            case 'position':
            case 'comment':
            case 'type':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

}