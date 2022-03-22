<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取mysql 版本
 * @return string
 */
function getMysqlVersion() {

    $conn = mysqli_connect(
        config('database.hostname'),
        config('database.username'),
        config('database.password'),
        config('database.database')
    );

    return mysqli_get_server_info($conn);
}

/**
 * 获取磁盘空间
 * @return string
 */
function getDiskSpace() {

    $isM = true;
    if (strstr(PHP_OS, 'WIN')) {

        $disk = disk_free_space("C:") / 1024 / 1024;
    } else {

        $disk = disk_free_space("/") / 1024 / 1024;
    }

    if ($disk > 1024) {
        $isM = false;
        $disk = $disk / 1024;
    }

    if ($disk > 1) {
        $diskDesc = number_format(ceil($disk));
    } else {
        $diskDesc = round($disk, 2);
    }

    $diskDesc .= ($isM) ? 'M' : 'G';

    return $diskDesc;
}

/**
 * 根据ip定位
 * @param $ip
 * @param $type
 * @return string | array
 * @throws Exception
 */
function getLocationByIp($ip, $type = 1)
{
    $ip2region = new \Ip2Region();
    $info = $ip2region->btreeSearch($ip);

    $info = explode('|', $info['region']);

    $address = '';
    foreach($info as $vo) {
        if('0' !== $vo) {
            $address .= $vo . '-';
        }
    }

    if (2 == $type) {
        return ['province' => $info['2'], 'city' => $info['3']];
    }

    return rtrim($address, '-');
}

/**
 * 计算时长
 * @param $seconds
 * @return string
 */
function changeTimeType($seconds)
{
    if ($seconds > 3600) {
        $hours = intval($seconds / 3600);
        $minutes = $seconds % 3600;
        $time = $hours . ":" . gmstrftime('%M:%S', $minutes);
    } else {
        $time = gmstrftime('%H:%M:%S', $seconds);
    }

    return $time;
}

/**
 * 计算有效期天数
 * @param $validDate
 * @return float
 */
function getValidDays($validDate)
{
    return floor((strtotime($validDate) - strtotime(date('Y-m-d H:i:s'))) / 86400);
}
/**
 * 是否全是中文
 * @param $str
 * @return bool
 */
function isAllChinese($str)
{
    // 新疆等少数民族可能有·
    if(strpos($str,'·')){
        // 将·去掉，看看剩下的是不是都是中文
        $str=str_replace("·",'',$str);
        if(preg_match('/^[\x7f-\xff]+$/', $str)){
            return true; // 全是中文
        }else{
            return false; //不全是中文
        }
    }else{
        if(preg_match('/^[\x7f-\xff]+$/', $str)){
            return true; // 全是中文
        }else{
            return false; // 不全是中文
        }
    }
}
