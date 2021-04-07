<?php

class Trackpoint
{
    public $time;       // UTC time
    public $lat;        // latitude
    public $long;       // longtitude
    public $alt;        // altitude from pressure sensor
    public $alt_gps;    // altitude from GPS

    public function __construct($record)
    {
        // time
        $this->time['h'] = substr($record,1,2);
        $this->time['m'] = substr($record,3,2);
        $this->time['s'] = substr($record,5,2);

        // latitude
        $this->lat['degrees'] = substr($record,7,2);
        $this->lat['minutes'] = substr($record,9,2);
        $this->lat['decimal_minutes'] = substr($record,11,3);
        $this->lat['direction'] = substr($record,14,1);

        $sign = $this->lat['direction']=="S"?"-":"";
        $decimal = (($this->lat['minutes'].".".$this->lat['decimal_minutes'])/60)+$this->lat['degrees'];
        $this->lat['decimal_degrees'] = $sign . $decimal;

        // longitude
        $this->long['degrees'] = substr($record,16,2);
        $this->long['minutes'] = substr($record,18,2);
        $this->long['decimal_minutes'] = substr($record,20,3);
        $this->long['direction'] = substr($record,23,1);

        $sign = $this->long['direction']=="W"?"-":"";
        $decimal = (($this->long['minutes'].".".$this->long['decimal_minutes'])/60)+$this->long['degrees'];
        $this->long['decimal_degrees'] = $sign . $decimal;

        // altitude
        $this->alt = (int) substr($record, 25, 5);
        $this->alt_gps = (int) substr($record, 30, 5);
    }
}
