<?php
// +----------------------------------------------------------------------
// | Rusty Crayon WebThink[锈蜡笔Web基础架构]
// +----------------------------------------------------------------------
// | function
// +----------------------------------------------------------------------
// | Copyright (c) rusty-crayon 2021-2023 https://www.rusty-crayon.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: QuintionTang <QuintionTang@gmail.com>
// +----------------------------------------------------------------------

function J($str){
    return str_replace('./', '', str_replace('//', '/', $str));
}

/**
 * [isMobile 判断手机号码格式]
 * @param  [type]  $phonenumber [description]
 * @return boolean              [description]
 */
function isMobile($phonenumber){
    if(preg_match("/^1[34578]{1}\d{9}$/",$phonenumber)){  
        return true; 
    }else{  
        return false;
    } 
}
function getFileType($original_name){
    $ext_array = array(
        "image"=>array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
        "word"=>array("doc","docx"),
        "excel"=>array("xls","xlsx"),
        "powerpoint"=>array("ppt","pptx"),
        "pdf"=>array("pdf"),
        "video"=>array('swf', 'flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb')
    );
    $extension = strtolower(array_pop(explode('.', trim($original_name))));
    $file_type = "";
    foreach ($ext_array as $key => $ext_info) {
        if (in_array($extension,$ext_info)){
            $file_type = $key;
            break;
        }
    }
    return safty_value($file_type,"file","trim");
}
function getFileColor($fileType){
    $ext_array = array(
        "image"=>"text-purple",
        "word"=>"text-primary",
        "excel"=>"text-success",
        "powerpoint"=>"text-secondary",
        "pdf"=>"text-danger",
        "video"=>"text-warning"
    );
    return safty_value($ext_array[$fileType],"text-info","trim");
}
function RJ($str,$type="pic",$uploadURL=""){
    //echo C('DEFAULT_THEME');
    $upload_url = "";
    if ($type == "pic"){
        $upload_url = str_replace('./', '/', str_replace('//', '/', $str));
        if ($uploadURL!=""){
            $upload_url = str_replace('/data/upload/', $uploadURL,$upload_url);
        }
    } else {
        $upload_url = str_replace('../', '/', str_replace('//', '/', $str));
    }
    return $upload_url;
}
/**
 * [_redirect 重定向]
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function _redirect($url){
    header('Location:'.$url);
    exit;
}

/**
 * [getConfig 获取配置项的值]
 * @param  [type] $fieldname [description]
 * @return [type]            [description]
 */
function getConfig($fieldname){
    $fieldname = C('CACHE_PREFIX') . $fieldname;
    return C($fieldname);
}

/**
 * [msubstr 字符串截取]
 * @param  [type]  $str      [description]
 * @param  [type]  $length   [description]
 * @param  integer $start    [description]
 * @param  string  $charset  [description]
 * @param  boolean $suffix   [description]
 * @param  string  $more_str [description]
 * @return [type]            [description]
 */
function msubstr($str, $length, $start = 0, $charset = "utf-8", $suffix = true,$more_str="...")
{
    $str = trim(strip_tags($str));
    $str = str_replace("\r\n", '', $str); //清除换行符
    $str = str_replace("\n", '', $str); //清除换行符
    $str = str_replace("\t", '', $str); //清除制表符
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return strlen($str) > $length ? $slice . $more_str : $slice;
}

function mkdirs($dir, $mode = 0777){
    if (is_dir($dir) || mkdir($dir, $mode)) return TRUE;
    if (!mkdirs(dirname($dir), $mode)) return FALSE;
    return mkdir($dir, $mode);
}
   
function Value2Tag($value,$delimiter="</p><p>"){
    $array_value = explode(C("TAG_DELI"),$value);
    $str_tags = join($delimiter, $array_value);
    return $str_tags;
}
/**
 * [update_file_chache 更新文件缓存]
 * @param  string $name  [缓存文件名称]
 * @param  string $value [缓存文件内容]
 * @return string        [文件缓存内容]
 */
function update_file_chache($name, $value = '',$path = "") {
	if ($path == ""){
        $path = DATA_PATH;
    } else {
        $path = DATA_PATH .$path."/";
    }
    return F($name, $value, $path);
}
/**
 * [get_file_cache 获取文件缓存内容]
 * @param  string $name [缓存文件名称]
 * @return string       [缓存文件内容]
 */
function get_file_cache($name,$path = "") {
    if ($path == ""){
        $path = DATA_PATH;
    } else {
        $path = DATA_PATH .$path."/";
    }
    $value = F($name, "", $path);
    return $value;
}

/**
 * 获取安全参数值，用户接收客户端传递过来的参数
 */
function safty_value($data,$default='',$filter=null){
	if (empty($data)){
    	$data = $default;
    } else {
    	//$data = $data['attr_value'];
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');

        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }elseif(is_int($filters)){
                $filters    =   array($filters);
            }

            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return isset($default)?$default:NULL;
                    }
                }
            }
        } else {
            $data       =    isset($default)?$default:NULL;
        }
    }
    return $data;
}

function fdate($time,$formate='ymd')
{
    if (!$time)
        return false;
    $fdate = '';
    if ($formate === "ymd"){
        $fdate = date('Y-m-d', $time);
    } else if ($formate === "ymdhi"){
        $fdate = date('Y年m月d日 H:i', $time);
    } else if ($formate === "Y-m-d Hi"){
        $fdate = date('Y-m-d H:i', $time);
    } else {
        $d = time() - intval($time);
        $ld = $time - mktime(0, 0, 0, 0, 0, date('Y'));
        $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y'));
        $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y'));
        $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y'));
        if ($d == 0) {
            $fdate = '刚刚';
        } else {
            switch ($d) {
                case $d < $atd:
                    $fdate = date('Y年m月d日', $time);
                    break;
                case $d < $td:
                    $fdate = '后天' . date('H:i', $time);
                    break;
                case $d < 0:
                    $fdate = '明天' . date('H:i', $time);
                    break;
                case $d < 60:
                    $fdate = $d . '秒前';
                    break;
                case $d < 3600:
                    $fdate = floor($d / 60) . '分钟前';
                    break;
                case $d < $dd:
                    $fdate = floor($d / 3600) . '小时前';
                    break;
                case $d < $yd:
                    $fdate = '昨天' . date('H:i', $time);
                    break;
                case $d < $byd:
                    $fdate = '前天' . date('H:i', $time);
                    break;
                case $d < $md:
                    $fdate = date('m月d日 H:i', $time);
                    break;
                case $d < $ld:
                    $fdate = date('m月d日', $time);
                    break;
                default:
                    $fdate = date('Y年m月d日', $time);
                    break;
            }
        }
    }

    return $fdate;
}

?>