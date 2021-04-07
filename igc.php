<?php

require 'Trackpoint.php';

class igc
{
    private $records_array;      // igc file converted into array
    private $trackpoints_array;
    private $start_point;        // first tracking point
    private $end_point;          // last tracking point
    //flight_info
    private $date;               // UTC date of flight
    private $fix_accuracy;       // typical fix accuracy in meters
    private $pilot_name;
    private $glider_model;       // name of the glider model
    private $glider_id;          // glider registration number

    public function __construct($url)
    {
        $file = file_get_contents($url);
        $file = preg_replace('/\s+/', ' ', $file);
        $this->records_array = explode(' ', $file);
    }

    public function readRecords()
    {
        if($this->records_array[0][0]!='A')
            return;
        $i = 0;
        $attrib_array = ['PLT'=> 'pilot_name', 'GTY' => 'glider_model', 'GID' => 'glider_id'];
        for($n=0; $n<count($this->records_array); $n++)
        {
            $record = $this->records_array[$n];

            if($record!=NULL && $record[0]=='B')
            {
                $this->trackpoints_array[$i] = new Trackpoint($record);
                $i++;
                continue;
            }
            if($record!=NULL && $record[0]=='H')
            {
                $this->setDate($record);
                $this->setFixAccuracy($record);

                foreach ($attrib_array as $letters => $attrib_name)
                {
                    if(empty($this->$attrib_name))
                    {
                        $n = $this->setAttrib($record, $n, $letters, $attrib_name);
                        $record = $this->records_array[$n];
                    }
                }
            }
        }
        $this->start_point = $this->trackpoints_array[0];
        $this->end_point = $this->trackpoints_array[count($this->trackpoints_array)-1];
    }

    private function setDate($record)
    {
        if (preg_match('/^[D]{1}[T]{1}[E]{1}[A-Z0-9]*/', substr($record, 2)))
        {
            for($j = 4; $j<strlen($record); $j++)
            {
                if(preg_match('/^[0-9]/',$record[$j]))
                {
                    $this->date['dd'] = substr($record, $j, 2);
                    $this->date['mm'] = substr($record, $j+2, 2);
                    $this->date['yy'] = substr($record, $j+4, 2);
                    break;
                }
            }
        }
    }

    private function setFixAccuracy($record)
    {
        if (preg_match('/^[F]{1}[X]{1}[A]{1}[A-Z0-9]*/', substr($record, 2)))
            $this->fix_accuracy = (int) substr($record, 5);
    }

    private function setAttrib($record, $n, $letters, $attrib)
    {
        $this->$attrib = '';
        if (preg_match('/^['.$letters[0].']{1}['.$letters[1].']{1}['.$letters[2].']{1}[A-Z0-9]*/', substr($record, 2)))
        {
            $pos = strpos($record, ':');
            if(strlen($record)>$pos)
                $this->$attrib = substr($record, $pos+1);

            $n++;
            $record = $this->records_array[$n];
            while($record[0]!='H')
            {
                $this->$attrib .= ' ';
                $this->$attrib .= $record;
                $n++;
                $record = $this->records_array[$n];
            }
        }

        return $n;
    }

    public function htmlInfo()
    {
        if($this->records_array[0][0]!='A')
            return "Invalid file. ";
        $code = ' <div id="info">
                <p>Flight information:</p><br/>
                <table>
                    <tr>
                        <th>Date: </th>
                        <td>' . $this->date['dd'] . '.' . $this->date['mm'] . '.20' . $this->date['yy'] . '</td>
                    </tr>
                    <tr>
                        <th>Start time: </th>
                        <td>' . $this->start_point->time['h'] . ':' . $this->start_point->time['m'] . '</td>
                    </tr>
                    <tr>
                        <th>End time: </th>
                        <td>' . $this->end_point->time['h'] . ':' . $this->end_point->time['m'] . '</td>
                    </tr>
                    <tr>
                        <th>Starting point coordinates: </th>
                        <td>' . $this->start_point->lat['degrees']
                                . '<sup>o</sup> '
                                . $this->start_point->lat['minutes']
                                . ','
                                . $this->start_point->lat['decimal_minutes']
                                . '\' '
                                . $this->start_point->lat['direction'] . '<br/>'
                                . $this->start_point->long['degrees']
                                . '<sup>o</sup> '
                                . $this->start_point->long['minutes']
                                . ','
                                . $this->start_point->long['decimal_minutes']
                                . '\' '
                                . $this->start_point->long['direction']
                        . '</td>
                    </tr>
                    <tr>
                        <th>Ending point coordinates: </th>
                        <td>' . $this->end_point->lat['degrees']
                                . '<sup>o</sup> '
                                . $this->end_point->lat['minutes']
                                . ','
                                . $this->end_point->lat['decimal_minutes']
                                . '\' '
                                . $this->end_point->lat['direction'] . '<br/>'
                                . $this->end_point->long['degrees']
                                . '<sup>o</sup> '
                                . $this->end_point->long['minutes']
                                . ','
                                . $this->end_point->long['decimal_minutes']
                                . '\' '
                                . $this->end_point->long['direction']
                        . '</td>
                    <tr>
                        <th>Pilot name: </th>
                        <td>'.$this->pilot_name.'</td>
                    </tr>
                    <tr>
                        <th>Glider model: </th>
                        <td>'.$this->glider_model.'</td>
                    </tr>
                    <tr>
                        <th>Glider ID: </th>
                        <td>'.$this->glider_id.'</td>
                    </tr>
                    <tr>
                        <th>Fix accuracy: </th>
                        <td>'.$this->fix_accuracy.' meters</td>
                    </tr>
                </table>
                </div>';

        return $code;
    }

    public function jsonInfo()
    {
        $info = [
            'Date' => $this->date['dd'] . '.' . $this->date['mm'] . '.20' . $this->date['yy'],
            'Start time' => $this->start_point->time['h'] . ':' . $this->start_point->time['m'],
            'End time' => $this->end_point->time['h'] . ':' . $this->end_point->time['m'],
            'Starting point coordinates' => '(' . $this->start_point->lat['degrees']
                                            . ' grades '
                                            . $this->start_point->lat['minutes'] . ','
                                            . $this->start_point->lat['decimal_minutes']
                                            . ' minutes '
                                            . $this->start_point->lat['direction'] . ') ('
                                            . $this->start_point->long['degrees']
                                            . ' grades '
                                            . $this->start_point->long['minutes'] . ','
                                            . $this->start_point->long['decimal_minutes']
                                            . ' minutes '
                                            . $this->start_point->long['direction'] . ')',
            'Ending point coordinates' =>   '(' . $this->end_point->lat['degrees']
                                            . ' grades '
                                            . $this->end_point->lat['minutes'] . ','
                                            . $this->end_point->lat['decimal_minutes']
                                            . ' minutes '
                                            . $this->end_point->lat['direction'] . ') ('
                                            . $this->end_point->long['degrees']
                                            . ' grades '
                                            . $this->end_point->long['minutes'] . ','
                                            . $this->end_point->long['decimal_minutes']
                                            . ' minutes '
                                            . $this->end_point->long['direction'] . ')',
            'Pilot name' => $this->pilot_name,
            'Glider model' => $this->glider_model,
            'Glider ID' => $this->glider_id,
            'Fix accuracy' => $this->fix_accuracy . '[m]'
        ];
        return json_encode($info);
    }

    public function getMap($key, $width, $height)
    {
        if (count($this->trackpoints_array)<1)
            return "There are no tracking points.";

        $code = '<script async
                    src="http://maps.googleapis.com/maps/api/js?key'.$key.'=&callback=initMap">
                </script>
                <br /><br />
                
                <div id="map" style="width: '.$width.'px; height: '.$height.'px; border: 2px solid #111111;"></div>
                <script type="text/javascript">
                function initMap() {
                    map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 14,
                    center: { lat: ' .$this->start_point->lat['decimal_degrees']. ', lng: '.$this->start_point->long['decimal_degrees'].' },
                    mapTypeId: "terrain",
                    });
                    flightPlanCoordinates = [
                    ';

        foreach ($this->trackpoints_array as $each)
            $code .= "{ lat: ".$each->lat['decimal_degrees'].", lng: ".$each->long['decimal_degrees']." },\n";

        $code .='];
               flightPath = new google.maps.Polyline({
                    path: flightPlanCoordinates,
                    geodesic: true,
                    strokeColor: "#FF0000",
                    strokeOpacity: 1.0,
                    strokeWeight: 2,
                });
                flightPath.setMap(map);
            }
            window.onload = initMap();
         </script>';

        return $code;
    }
}

