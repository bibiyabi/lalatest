<?php

namespace App\Http\Controllers\tools;

use App\Http\Controllers\Controller;


class Fpm extends Controller
{
    public function __construct() {

    }


    public function index()
    {
        if (config('app.env') !== 'local'){
            $file = file('/home/pmuser/colin/fpm.log');
        } else {
            $file = file(dirname(__FILE__) . '/fpm.log');
        }
        $array = '';

        $acceptedconn = '';
        $listenqueue = '';
        $maxlistenqueue = '';
        $listenqueuelen = '';
        $idleprocesses = '';
        $activeprocesses = '';
        $totalprocesses = '';
        $maxactiveprocesses = '';
        $maxchildrenreached = '';
        $slowrequests = '';
        $startsince = '';
        $hostname = '';

        foreach( $file as $lineNum => $lineData ) {
            if (empty($lineData)) continue;
            $data = json_decode($lineData, true);
            $hostname = $data['hostname'];
            $data['time'] = date("Y-m-d H:i:s", strtotime($data['time'] . " +8 hours"));
            $acceptedconn .= '{x:"'.$data['time'].'", y:'.$data['acceptedconn'].'},';
            $startsince .= '{x:"'.$data['time'].'", y:'.$data['startsince'].'},';
            $listenqueue .= '{x:"'.$data['time'].'", y:'.$data['listenqueue'].'},';
            $maxlistenqueue .= '{x:"'.$data['time'].'", y:'.$data['maxlistenqueue'].'},';
            $listenqueuelen .= '{x:"'.$data['time'].'", y:'.$data['listenqueuelen'].'},';
            $idleprocesses .= '{x:"'.$data['time'].'", y:'.$data['idleprocesses'].'},';
            $activeprocesses .= '{x:"'.$data['time'].'", y:'.$data['activeprocesses'].'},';
            $totalprocesses .= '{x:"'.$data['time'].'", y:'.$data['totalprocesses'].'},';
            $maxactiveprocesses .= '{x:"'.$data['time'].'", y:'.$data['maxactiveprocesses'].'},';
            $maxchildrenreached .= '{x:"'.$data['time'].'", y:'.$data['maxchildrenreached'].'},';
            $slowrequests .= '{x:"'.$data['time'].'", y:'.$data['slowrequests'].'},';
        }

        $startsince = substr( $startsince, 0, -1);
        $acceptedconn = substr( $acceptedconn, 0, -1);
        $listenqueue = substr( $listenqueue, 0, -1);
        $maxlistenqueue = substr( $maxlistenqueue, 0, -1);
        $listenqueuelen = substr( $listenqueuelen, 0, -1);
        $idleprocesses = substr( $idleprocesses, 0, -1);
        $activeprocesses = substr( $activeprocesses, 0, -1);
        $totalprocesses = substr( $totalprocesses, 0, -1);
        $maxactiveprocesses = substr( $maxactiveprocesses, 0, -1);
        $maxchildrenreached = substr( $maxchildrenreached, 0, -1);
        $slowrequests = substr( $slowrequests, 0, -1);

        return view('tools.fpm' ,compact('acceptedconn',
        'startsince',
        'listenqueue' ,
        'maxlistenqueue',
        'listenqueuelen',
        'maxlistenqueue',
        'totalprocesses',
        'maxactiveprocesses',
        'maxchildrenreached',
        'slowrequests',
        'hostname',
        'idleprocesses',
        'activeprocesses',

    ));
    }
}
