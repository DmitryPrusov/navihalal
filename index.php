<?php
/*
Plugin Name: NAVI Halal
Plugin URI: http://navidesign.com.ua/
Description: contact form validation + data export + data in admin table
Version: 1.0.0
Author: dilleader
Author URI: http://navidesign.com.ua/
*/
require_once dirname(__FILE__) . '/classes/halal_list_table.php';
require_once dirname(__FILE__) . '/classes/halal_contact_form.php';
require_once dirname(__FILE__) . '/classes/halal_export_data.php';
require_once dirname(__FILE__) . '/classes/halal_contact_us.php';



if(is_admin())
{
    new Halal_Export_Data();
    new Halal_List_Table();
}
new Halal_Contact_Form();









