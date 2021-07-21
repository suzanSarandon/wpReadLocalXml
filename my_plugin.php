<?php

/*
  Plugin Name: my_plugin
  Description: Plugin to count xml items
  Version: 2.321
  Author: Phil
  Author URI: "http://fgalanos.users.uth.gr/site_cv/index.html"
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once plugin_dir_path(__FILE__) . '/inc/GetXMLInfo.php';
require_once plugin_dir_path(__FILE__) . '/inc/XmlAlert.php';



class myXmlCountPlugin{
    function __construct(){
        global $wpdb;
        $this->charset = $wpdb->get_charset_collate();
        $this->tablename = $wpdb->prefix . "xml_info";

        add_action('activate_new-database-table/new-database-table.php', array($this, 'onActivate'));
        wp_enqueue_style('xmldisplaycss', plugin_dir_url(__FILE__) . 'xmldisplay.css');
        add_action('admin_menu', array($this, 'ourMenu'));
        add_action('admin_init', array($this, 'settings'));
        add_action('admin_post_createentry', array($this, 'createEntry'));
        add_action('admin_post_nopriv_createentry', array($this, 'createEntry'));

        $sql = "CREATE TABLE $this->tablename (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        date_created datetime NOT NULL,
        product_count bigint(20) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id))
        $this->charset; ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Settings that the admin will store in the db
    function settings(){
        add_settings_section('xcp_first_section', null, null, 'xmlCount');
        register_setting('xmlcountplugin', 'xcp_method', array('sanitize_callback' => array($this, 'sanitizeMethod'), 'default'=>'0'));
        add_settings_field('xcp_method', 'Count Method', array($this, 'methodMarkup'), 'xmlCount', 'xcp_first_section');
    }

// Custom Sanitize Method input
    function sanitizeMethod($input){
        if($input !='0' AND $input !='1'){
            add_settings_error('xcp_method', 'xcp_method_error', 'Count method must be either Manually or Automatic!');
            return get_option('xcp_method');
        }
        return $input;
    }

    function ourMenu() {
        add_menu_page('XML Counter', 'XML Counter', 'manage_options', 'ourxmlcounter', array($this, 'xmlCountMarkup'), 'dashicons-smiley', 100);
        add_submenu_page('ourxmlcounter', 'XML Count Display', 'XML Count Display', 'manage_options', 'xmlCountPage', array($this, 'xmlCountMarkup'));
        add_submenu_page('ourxmlcounter', 'XML Counter Options', 'Options', 'manage_options', 'xml-count-settings', array($this, 'optionsMarkup'));
    }

    function createEntry() {
        if (current_user_can('administrator')) {
            $info_arr = [];
            $info_arr['date_created'] = $_POST['incomingdate'];
            $info_arr['product_count'] = $_POST['incomingproductcount'];
            global $wpdb;
            $wpdb->insert($this->tablename, $info_arr);
            wp_safe_redirect(get_admin_url('admin.php?page=xmlCountPage'));
        } else {
            wp_safe_redirect(admin_url());
        }
        exit;
    }

    //plugin xml display markup
    function xmlCountMarkup() {
        date_default_timezone_set('Europe/Athens');
        $date = new \DateTime('now');
        $product_count = $this->readXML()[0];

        ?>
        <div class = 'wrap'>
            <h1>XML Count Display</h1>
            <table class="my-plugin-table">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Products Count</th>
                </tr>
                <?php
                $getXMLInfo = new GetXMLInfo();
                foreach($getXMLInfo->xml_info as $xml) { ?>
                    <tr>
                        <td><? echo $xml->id ?></td>
                        <td><? echo $xml->date_created ?></td>
                        <td><? echo $xml->product_count ?></td>
                    </tr>
                <? }?>
            </table>
        </div>
        <?php if (current_user_can('administrator')) {?>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="createentry">
                <input type="hidden" name="incomingdate"value="<? echo $date->format("Y-m-d h:i:s")?>">
                <input type="hidden" name="incomingproductcount"value="<? echo $product_count?>">
                <?
                $XmlAlert = new XmlAlert();
                submit_button('Read your XML');

                // Send email to admin if alert is a go
                if ($XmlAlert->alert){
                    wp_mail('admin@flywheel.local','XML Alert', "Products Count is off by $XmlAlert->alert% , please act accordingly !");
                    //print_r($XmlAlert->alert);
                }?>
            </form>
        <?php } ?>
    <?php }

    // Markup Display of xcp_method
    function methodMarkup(){?>
        <select name="xcp_method">
            <option value="0"<?php selected(get_option('xcp_method'), '0')?>>Manually Count XML</option>
            <option value="1"<?php selected(get_option('xcp_method'), '1')?>>Automatic Count XML</option>
        </select>
    <?php }

    //Plugin's options markup
    function optionsMarkup(){?>
        <div class = 'wrap'>
            <h1>XML Count Settings</h1>
            <form action="options.php" method="POST">
                <?
                settings_fields('xmlcountplugin');
                do_settings_sections('xmlCount');
                submit_button();
                ?>
            </form>
        </div>

    <?php }

    //Main function to process the xml file
    function readXML()
    {
        $file = trailingslashit( ABSPATH ) . 'wp-content/uploads/feed/skroutz.xml' ;
        //Opening a reader
        $reader = new XMLReader();
        $date_created = '';


        if (!$reader->open($file)) {
            die("Failed to open 'skroutz.xml'");
        }

        // Iterate through the XML nodes
        $productsCount = 1;
        while ($reader->read()) {
            if ($reader->name == 'name' && $reader->nodeType == XMLReader::ELEMENT) {
                $productsCount += 1;
            } elseif ($reader->name == 'created_at' && $reader->nodeType == XMLReader::ELEMENT) {
                $date_created = $reader->readString();
            }
        }
        //Closing the reader
        $reader->close();
        $format = 'Y-m-d H:i:s';
        $date = DateTime::createFromFormat($format, $date_created);
        return array($productsCount, $date->format('Y-m-d H:i:s'));
    }

}
$myXmlCountPlugin = new myXmlCountPlugin();
