<?php
    /*
     All Emoncms code is released under the GNU Affero General Public License.
     See COPYRIGHT.txt and LICENSE.txt.

        ---------------------------------------------------------------------
        Emoncms - open source energy visualisation
        Part of the OpenEnergyMonitor project:
        http://openenergymonitor.org
    */

    // no direct access
    defined('Telemetry_ENGINE') or die('Restricted access');

function input_controller()
{
    //return array('content'=>"ok");

    global $mysqli, $redis, $user, $session, $route, $settings;

    // There are no actions in the input module that can be performed with less than write privileges
    if (!$session['write']) return array('content'=>false);

    global $feed;
    $result = false;

    require_once "Ext_Modules/feed/feed_model.php";
    $feed = new Feed($mysqli,$redis, $settings["feed"]);

    require_once "Ext_Modules/input/input_model.php";
    $input = new Input($mysqli,$redis, $feed);

    require_once "Ext_Modules/process/process_model.php";
    $process = new Process($mysqli, $input, $feed, $user->get_timezone($session['userid']));



    if ($route->format == 'html')
    {
        if ($route->action == 'api') $result = view("Ext_Modules/input/Views/input_api.php", array());
        if ($route->action == 'node') $result =  view("Ext_Modules/input/Views/input_node.php", array());
        if ($route->action == 'process') $result = view("Ext_Modules/input/Views/process_list.php",array('inputid'=> intval(get('inputid'))));
        if ($route->action == 'view') $result =  view("Ext_Modules/input/Views/input_view.php", array());
    }

    if ($route->format == 'json')
    {
        /*

        input/bulk.json?data=[[0,16,1137],[2,17,1437,3164],[4,19,1412,3077]]

        The first number of each node is the time offset (see below).

        The second number is the node id, this is the unique identifer for the wireless node.

        All the numbers after the first two are data values. The first node here (node 16) has only one data value: 1137.

        Optional offset and time parameters allow the sender to set the time
        reference for the packets.
        If none is specified, it is assumed that the last packet just arrived.
        The time for the other packets is then calculated accordingly.

        offset=-10 means the time of each packet is relative to [now -10 s].
        time=1387730127 means the time of each packet is relative to 1387730127
        (number of seconds since 1970-01-01 00:00:00 UTC)

        Examples:

        // legacy mode: 4 is 0, 2 is -2 and 0 is -4 seconds to now.
          input/bulk.json?data=[[0,16,1137],[2,17,1437,3164],[4,19,1412,3077]]
        // offset mode: -6 is -16 seconds to now.
          input/bulk.json?data=[[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]]&offset=-10
        // time mode: -6 is 1387730121
          input/bulk.json?data=[[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]]&time=1387730127
        // sentat (sent at) mode:
          input/bulk.json?data=[[520,16,1137],[530,17,1437,3164],[535,19,1412,3077]]&offset=543

        See pull request for full discussion:
        https://github.com/emoncms/emoncms/pull/118
        */

        if ($route->action == 'bulk')
        {
            $valid = true;

            if (!isset($_GET['data']) && isset($_POST['data']))
            {
                $data = json_decode(post('data'));
            }
            else
            {
                $data = json_decode(get('data'));
            }

            $userid = $session['userid'];

            $dropped = 0; $droppednegative = 0;

            $len = count($data);
            if ($len>0)
            {
                if (isset($data[$len-1][0]))
                {
                    // Sent at mode: input/bulk.json?data=[[45,16,1137],[50,17,1437,3164],[55,19,1412,3077]]&sentat=60
                    if (isset($_GET['sentat'])) {
                        $time_ref = time() - (int) $_GET['sentat'];
                    }  elseif (isset($_POST['sentat'])) {
                        $time_ref = time() - (int) $_POST['sentat'];
                    }
                    // Offset mode: input/bulk.json?data=[[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]]&offset=-10
                    elseif (isset($_GET['offset'])) {
                        $time_ref = time() - (int) $_GET['offset'];
                    } elseif (isset($_POST['offset'])) {
                        $time_ref = time() - (int) $_POST['offset'];
                    }
                    // Time mode: input/bulk.json?data=[[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]]&time=1387729425
                    elseif (isset($_GET['time'])) {
                        $time_ref = (int) $_GET['time'];
                    } elseif (isset($_POST['time'])) {
                        $time_ref = (int) $_POST['time'];
                    }
                    // Legacy mode: input/bulk.json?data=[[0,16,1137],[2,17,1437,3164],[4,19,1412,3077]]
                    else {
                        $time_ref = time() - (int) $data[$len-1][0];
                    }

                    foreach ($data as $item)
                    {
                        if (count($item)>2)
                        {
                            // check for correct time format
                            $itemtime = (int) $item[0];

                            $time = $time_ref + (int) $itemtime;
                            $nodeid = $item[1];

                            $inputs = array();
                            $name = 1;
                            for ($i=2; $i<count($item); $i++)
                            {
                                $value = (float) $item[$i];
                                $inputs[$name] = $value;
                                $name ++;
                            }

                            $array = array(
                                'userid'=>$userid,
                                'time'=>$time,
                                'nodeid'=>$nodeid,
                                'data'=>$inputs
                            );

                            // Start of rate limiter
                            $lasttime = 0;
                            if ($redis->exists("limiter:$userid:$nodeid")) {
                                $lasttime = $redis->get("limiter:$userid:$nodeid");
                            }

                            if (($time-$lasttime)>=1)
                            {
                                $redis->set("limiter:$userid:$nodeid",$time);
                                $str = json_encode($array);

                                if ($redis->llen('buffer')<10000) {
                                    $redis->rpush('buffer',$str);
                                } else {
                                    $valid = false;
                                    $error = "Too many connections, input queue is full";
                                }
                            } else {
                                if (($time-$lasttime)<0) $droppednegative ++;
                                $dropped ++;
                            }
                        } else { $valid = false; $error = "Format error, bulk item needs at least 3 values"; }
                    }
                } else { $valid = false; $error = "Format error, last item in bulk data does not contain any data"; }
            } else { $valid = false; $error = "Format error, json string supplied is not valid"; }

            if ($dropped) {
                $valid = false;
                $error = "Request exceed's max node update rate of 1 per second: $dropped $droppednegative times";
            }

            // $valid = true;

            if ($valid) {
                $result = 'ok';
            } else {
                $result = "Error: $error\n";
            }
        }

        // input/post.json?node=10&json={power1:100,power2:200,power3:300}
        // input/post.json?node=10&csv=100,200,300

        if ($route->action == 'post')
        {
            $valid = true; $error = "";

            $nodeid = get('node');
            if ($nodeid && !is_numeric($nodeid)) { $valid = false; $error = "Nodeid must be an integer, nodeid given was not numeric"; }
            $nodeid = (int) $nodeid;

            if (isset($_GET['time'])) $time = (int) $_GET['time']; else $time = time();

            $data = array();

            $datain = false;
            // code below processes input regardless of json or csv type
            if (isset($_GET['json'])) $datain = get('json');
            if (isset($_GET['csv'])) $datain = get('csv');
            if (isset($_GET['data'])) $datain = get('data');
            if (isset($_POST['data'])) $datain = post('data');

            if ($datain!="")
            {
                $json = preg_replace('/[^\p{L}_\p{N}\s\-.:,]/u','',$datain);
                $datapairs = explode(',', $json);

                $csvi = 0;
                for ($i=0; $i<count($datapairs); $i++)
                {
                    $keyvalue = explode(':', $datapairs[$i]);

                    if (isset($keyvalue[1])) {
                        if ($keyvalue[0]=='') {$valid = false; $error = "Format error, json key missing or invalid character"; }
                        if (!is_numeric($keyvalue[1])) {$valid = false; $error = "Format error, json value is not numeric"; }
                        $data[$keyvalue[0]] = (float) $keyvalue[1];
                    } else {
                        if (!is_numeric($keyvalue[0])) {$valid = false; $error = "Format error: csv value is not numeric"; }
                        $data[$csvi+1] = (float) $keyvalue[0];
                        $csvi ++;
                    }
                }

                $packet = array(
                    'userid' => $session['userid'],
                    'time' => $time,
                    'nodeid' => $nodeid,
                    'data'=>$data
                );

                if (count($data)>0 && $valid) {
                    $str = json_encode($packet);
                    if ($redis->llen('buffer')<10000) {
                        $redis->rpush('buffer',$str);
                    } else {
                        $valid = false; $error = "Too many connections, input queue is full";
                    }
                }
            }
            else
            {
                $valid = false;
                $error = "Request contains no data via csv, json or data tag";
            }

            if ($valid) $result = 'ok';
            else $result = "Error: $error\n";
        }

        if ($route->action == "clean") $result = $input->clean($session['userid']);
        if ($route->action == "list") $result = $input->getlist($session['userid']);
        if ($route->action == "getinputs") $result = $input->get_inputs($session['userid']);
        if ($route->action == "getallprocesses") $result = $process->get_process_list();

        if (isset($_GET['inputid']) && $input->belongs_to_user($session['userid'],get("inputid")))
        {
            if ($route->action == "delete") $result = $input->delete($session['userid'],get("inputid"));

            if ($route->action == 'set') $result = $input->set_fields(get('inputid'),get('fields'));

            if ($route->action == "process")
            {
                if ($route->subaction == "set") $result = $input->set_processlist(get('inputid'), post('processlist'));
                if ($route->subaction == "add") $result = $input->add_process($process,$session['userid'], get('inputid'), get('processid'), get('arg'), get('newfeedname'), get('newfeedinterval'),get('engine'));
                if ($route->subaction == "list") $result = $input->get_processlist(get("inputid"));
                if ($route->subaction == "delete") $result = $input->delete_process(get("inputid"),get('processid'));
                if ($route->subaction == "move") $result = $input->move_process(get("inputid"),get('processid'),get('moveby'));
                if ($route->subaction == "reset") $result = $input->reset_process(get("inputid"));
            }
        }
    }

    return array('content'=>$result);
}
