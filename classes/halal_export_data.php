<?php

class Halal_Export_Data {

    public function __construct()
    {
        add_action('admin_menu', array ($this, 'halal_admin_menu'));
        add_action('admin_post_export_data', array ($this, 'export_data_halal'));

        add_action('admin_print_scripts', array ($this, 'hkdc_admin_scripts'));
        add_action('admin_print_styles', array ($this, 'print_style'));

    }


    public function halal_admin_menu()
    {
        add_menu_page('Halal Data Export', 'Halal Data Export', 'manage_options', 'halal-dashboard',  array ($this, 'make_excel_file'));
    }

    public function  hkdc_admin_scripts()
    {
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'show_datepicker',  plugin_dir_url( __FILE__ ) . '/scripts/show_datepicker.js' );
    }

    public function print_style()
    {
        wp_register_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
    }




   public function make_excel_file()

    { ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Halal Export Data</h2>
            <br>
            <br>
            <br>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" id="data_export">
            <label for="type">Type:  </label>
            <select name ='type' id="type">
                <option value="1">Request</option>
                <option value="2">Appeal</option>
                <option value="3">Complain</option>
                <option selected="selected" value="4">All</option>
            </select>
                    <br>
            <label for="status">Status: </label>
            <select name ='status' id="status">
                <option value="1">New</option>
                <option value="2">Processed</option>
                <option value="3">Closed</option>
                <option selected="selected" value="4">All</option>
            </select>
                    <br>
                    <p>Pick Dates: <input type="text" id="from" class="datepicker" name="from" />  <input type="text" id="to" name="to" class="datepicker" /> </p>
            <br>
            <input type="hidden" name="action" value="export_data">
            <input type="submit" value="Export Data!">
        </form>
        </div>
    <?php }


    public  function check_date($date) {

        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {
            return $date;
        } else {

            wp_safe_redirect( wp_get_referer() );
        }
    }




   public function export_data_halal()
   {

        require_once dirname(__FILE__) . '/PHPExcel.php';

        $type = absint(intval($_POST['type']));
        $status = absint(intval($_POST['status']));

        $date_from = $this->check_date($_POST['from']);
        $date_to = $this->check_date($_POST['to']);

        $date_from = "'".$date_from. "'";
        $date_to = "'".$date_to."'";

        global $wpdb;

        if (($type !=4) and ($status !=4)) {
            $results = $wpdb->get_results("select * from wp_halal_certificate
         where (date between " . $date_from . " and " . $date_to . ") and type =" . $type . " and status=" . $status . " order by id desc",
                ARRAY_A);
        }
        elseif (($type == 4) and ($status !=4)) {
            $results = $wpdb->get_results("select * from wp_halal_certificate
         where (date between " . $date_from . " and " . $date_to . ") and status=" . $status . " order by id desc",
                ARRAY_A);
        }

        elseif (($type != 4) and ($status == 4)) {
            $results = $wpdb->get_results("select * from wp_halal_certificate
         where (date between " . $date_from . " and " . $date_to . ") and type =" . $type . "order by id desc",
                ARRAY_A);
        }

        else {
            $results = $wpdb->get_results("select * from wp_halal_certificate
         where (date between " . $date_from . " and " . $date_to . ") order by id desc",
                ARRAY_A);
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");

        $ews = $objPHPExcel->getSheet(0);

        $ews->setCellValue('a1', 'ID');
        $ews->setCellValue('b1', 'Status');
        $ews->setCellValue('c1', 'Date');
        $ews->setCellValue('d1', 'File');
        $ews->setCellValue('e1', 'Email');
        $ews->setCellValue('f1', 'Number');
        $ews->setCellValue('g1', 'Contact Person');
        $ews->setCellValue('h1', 'Company Name');
        $ews->setCellValue('i1', 'Field of activity');
        $ews->setCellValue('j1', 'Position');
        $ews->setCellValue('k1', 'Comment');
        $ews->setCellValue('l1', 'Type');

        for ($col = ord('a'); $col <= ord('l'); $col++) {
            $ews->getColumnDimension(chr($col))->setAutoSize(true);
        }


        $header = 'a1:l1';
        $ews->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
        $style = array(
            'font' => array('bold' => true,),
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
        );
        $ews->getStyle($header)->applyFromArray($style);
        $objPHPExcel->setActiveSheetIndex(0);

       $i = 2;
       $array_types = array(1 => "Request", 2 => "Appeal", 3 => "Complain");
       $array_status = array(1 => "New", 2 => "Processed", 3 => "Closed");


        foreach ($results as $certificate_data) {
            $certificate_data['type'] = $array_types[$certificate_data['type']];
            $certificate_data['status'] = $array_status[$certificate_data['status']];
            $sheet = array($certificate_data);
            $objPHPExcel->getActiveSheet()->fromArray($sheet, null, "A{$i}");
            $url = str_replace('http://', '', $certificate_data['file']);
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$i)->getHyperlink()->setUrl('http://www.'.$url);
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3,$i)->setValue(' download ');
            $i++;
        }


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $date = date('m/d/Y h:i:s a');
        $string = 'Content-Disposition: attachment;filename="export_halal_'.$date.'.xlsx"';
        header($string);
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
