<?php


// Class to get xml entries and fields as objects and attrs
class GetXMLInfo
{
    function __construct()
    {
        global $wpdb;
        $tablename = $wpdb->prefix . 'xml_info';
        $this->args = $this->getArgs();
        $query = "SELECT * FROM $tablename ";
        $this->xml_info = $wpdb->get_results($wpdb->prepare($query));
    }

    function getArgs()
    {
        $temp = array(
            'id' => sanitize_text_field($_GET['id']),
            'date_created' => sanitize_text_field($_GET['date_created']),
            'product_count' => sanitize_text_field($_GET['product_count']),
        );
    }

}