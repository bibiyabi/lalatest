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
            $file = file('/var/www/html/agent/fpm.log');
        } else {
            $file = file(dirname(__FILE__) . '/fpm.log');
        }
        $array = '';

        $acceptedconn = '';
        $listenqueue = '';
        $maxlistenqueue = ''; // 请求等待队列最高的数量
        $listenqueuelen = ''; // socket等待队列长度
        $idleprocesses = ''; // 空闲进程数量
        $activeprocesses = ''; //活跃进程数量
        $totalprocesses = ''; //总进程数量
        $maxactiveprocesses = ''; //最大的活跃进程数量（FPM启动开始算）
        $maxchildrenreached = ''; //大道进程最大数量限制的次数，如果这个数量不为0，那说明你的最大进程数量太小了，请改大一点
        $slowrequests = '';
        $hostname = '';

        foreach( $file as $lineNum => $lineData ) {
            if (empty($lineData)) continue;
            $data = json_decode($lineData, true);
            $hostname = $data['hostname'];
            $data['time'] = date("Y-m-d H:i:s", strtotime($data['time'] . " +8 hours"));
            $acceptedconn .= '{x:"'.$data['time'].'", y:'.$data['acceptedconn'].'},';
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
