<?php


class XmlAlert
{

    function __construct()
    {
        global $wpdb;
        $tablename = $wpdb->prefix . 'xml_info';

        // Query the last created product count vs the last-1
        $q_count_last = "SELECT product_count FROM $tablename WHERE id=(SELECT max(id) FROM $tablename)";
        $q_count_pre = "SELECT product_count FROM $tablename WHERE id=(SELECT max(id)-1 FROM $tablename)";


        $this->count_last = $wpdb->get_results($wpdb->prepare($q_count_last));
        $this->count_pre = $wpdb->get_results($wpdb->prepare($q_count_pre));

        $count_last = $this->count_last[0]->product_count;
        $count_pre = $this->count_pre[0]->product_count;

        $this->alert = $this->getAlert($count_last, $count_pre);
    }

    function getAlert($last, $pre){
        if (!$pre){
            return 0;
        }
        $percentChange = (abs($last-$pre)/(($last+$pre)/2))*100;
        if ($percentChange >= 30) {
            return $percentChange;
        } else {
            return 0;
        }
    }

}