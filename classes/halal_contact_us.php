<?php


class Halal_Contact_Us
{

    public function __construct()
    {
        add_action('admin_post_nopriv_contact_us', array($this, 'processing_contact_form'));
        add_action('admin_post_contact_us', array($this, 'processing_contact_form'));

        add_action( 'wp_print_scripts', array ($this, 'contact_us_script'));

    }

    public function my_contact_form_generate_response($type, $error_container = "")
    {
        header('Content-Type: application/json');
        if ($type == 'success') {
            echo json_encode(array('result' => 'success'));
            exit;

        } else {
            echo json_encode(array('result' => 'error', 'text_error' => $error_container));
            exit;
        }
    }

    public function contact_us_script () {

        wp_enqueue_script( 'halal_contact_us_submit',  plugin_dir_url( __FILE__ ) . '/scripts/halal_contact_us_submit.js' );

    }

    public static function clean($value = "")
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = strip_tags($value);
        $value = htmlspecialchars($value);

        return $value;
    }

    public static function check_length($value = "", $min, $max)
    {
        $result = (mb_strlen($value) < $min || mb_strlen($value) > $max);
        return !$result;
    }


    public function processing_contact_form()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $fullname = isset($_POST['contact-full-name-field']) ? self::clean($_POST['full-name-field']) : '';
            $email = isset($_POST['contact-email-field']) ? self::clean($_POST['email-field']) : '';
            $number = isset($_POST['contact-number-field']) ? self::clean($_POST['number-field']) : '';
            $company_name = isset($_POST['contact-company-name-field']) ? self::clean($_POST['company-name-field']) : '';
            $field = isset($_POST['activity-field']) ? self::clean($_POST['activity-field']) : '';
            $position = isset($_POST['position-field']) ? self::clean($_POST['position-field']) : '';



            $file = &$_FILES['my_file_upload'];

            $type = intval(self::clean($_POST['type']));

            $error_container = array();

            // not empty required fields:
            $required_fields = array(

                'full-name-field' => $fullname,
                'email-field' => $email,
                'number-field' => $number,
                'activity-field' => $field,
            );

            foreach ($required_fields as $name => $value) {
                if ($value == '' || !isset ($name)) {
                    $error_container[$name] = 'Field is required for imput';
                }
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            // required text fields > 3 symbols

            $check_lenght_fields = array(
                'full-name-field' => $fullname,
                'activity-field' => $field,
            );

            foreach ($check_lenght_fields as $name => $value) {
                if (!self::check_length($value, 3, 50)) {
                    $error_container[$name] = 'Length should be at least 3 symbols';
                }
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            // only digits and spaces and at least 7 digits:

            if (!preg_match('/^[\d\s]+$/', $number)) {
                $error_container['number-field'] = 'Only spaces and digits are allowed';
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            if (!preg_match('/([^\d]*\d){7}/', $number)) {
                $error_container['number-field'] = 'Number should contain at least 7 digits';
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            // check correctness of email:

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_container['email-field'] = 'Invalid email address';
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            // file validation (only pdf, jpg, png mimes are permitted):


            if (!is_uploaded_file($file['tmp_name'])) {
                $error_container['my_file_upload'] = 'Please upload the application file';
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            $overrides = array(
                'test_form' => false,
                'mimes' => array('png' => 'image/png', 'jpg|jpeg' => 'image/jpeg', 'pdf' => 'application/pdf')
            );
            $movefile = wp_handle_upload($file, $overrides);

            if (isset($movefile['error'])) {
                $error_container['my_file_upload'] = $movefile['error'];
            }

            if (!empty($error_container)) {
                $this->my_contact_form_generate_response('error', $error_container);
            }

            // VALIDATION FINISHED SUCCESSFULY:

            global $wpdb;

            // filling in database:

            $wpdb->insert(
                'wp_halal_certificate',
                array(
                    'file' => $movefile['url'],
                    'email' => $email,
                    'number' => $number,
                    'contact_person' => $fullname,
                    'company_name' => $company_name,
                    'field_activity' => $field,
                    'position' => $position,
                    'comment' => $comment,
                    'type' => $type
                ));

            $this->my_contact_form_generate_response('success');
            exit;

        }
    }
}