<?php
// +----------------------------------------------------------------------
// | Input 
// +----------------------------------------------------------------------
// | 对浏览器端发送到WEB服务器端的所有数据进行过滤。
// +----------------------------------------------------------------------
// | Copyright (c) rusty-crayon 2021-2023 https://www.rusty-crayon.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: QuintionTang <QuintionTang@gmail.com>
// +----------------------------------------------------------------------
namespace Org\Util;
class Input {
    private $filter =   null;
    private static $_input  =   array('get','post','request','env','server','cookie','session','globals','config','lang','call');
    public static $htmlTags = array(
        'allow' => 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a',
        'ban' => 'html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml',
    );
    static public function getInstance() {
        return get_instance_of(__CLASS__);
    }
    public function __call($type,$args=array()) {
        $type    =   strtolower(trim($type));
        if(in_array($type,self::$_input,true)) {
            switch($type) {
                case 'get':      $input      =& $_GET;break;
                case 'post':     $input      =& $_POST;break;
                case 'request': $input      =& $_REQUEST;break;
                case 'env':      $input      =& $_ENV;break;
                case 'server':   $input      =& $_SERVER;break;
                case 'cookie':   $input      =& $_COOKIE;break;
                case 'session':  $input      =& $_SESSION;break;
                case 'globals':   $input      =& $GLOBALS;break;
                case 'files':      $input      =& $_FILES;break;
                case 'call':       $input      =   'call';break;
                case 'config':    $input      =   C();break;
                case 'lang':      $input      =   L();break;
                default:return NULL;
            }
            if('call' === $input) {
                $callback    =   array_shift($args);
                $params  =   array_shift($args);
                $data    =   call_user_func_array($callback,$params);
                if(count($args)===0) {
                    return $data;
                }
                $filter =   isset($args[0])?$args[0]:$this->filter;
                if(!empty($filter)) {
                    $data    =   call_user_func_array($filter,$data);
                }
            }else{
                if(0==count($args) || empty($args[0]) ) {
                    return $input;
                }elseif(array_key_exists($args[0],$input)) {
                    $data    =   $input[$args[0]];
                    $filter =   isset($args[1])?$args[1]:$this->filter;
                    if(!empty($filter)) {
                        $data    =   call_user_func_array($filter,$data);
                    }
                }else{
                    $data    =   isset($args[2])?$args[2]:NULL;
                }
            }
            return $data;
        }
    }
    public function filter($filter) {
        $this->filter   =   $filter;
        return $this;
    }
    static public function noGPC() {
        if ( get_magic_quotes_gpc() ) {
           $_POST = stripslashes_deep($_POST);
           $_GET = stripslashes_deep($_GET);
           $_COOKIE = stripslashes_deep($_COOKIE);
           $_REQUEST= stripslashes_deep($_REQUEST);
        }
    }
    static public function forSearch($string) {
        return str_replace( array('%','_'), array('\%','\_'), $string );
    }
    static public function forShow($string) {
        return self::nl2Br( self::hsc($string) );
    }
    static public function forTarea($string) {
        return str_ireplace(array('<textarea>','</textarea>'), array('&lt;textarea>','&lt;/textarea>'), $string);
    }
    static public function forTag($string) {
        return str_replace(array('"',"'"), array('&quot;','&#039;'), $string);
    }
    static public function makeLink($string) {
        $validChars = "a-z0-9\/\-_+=.~!%@?#&;:$\|";
        $patterns = array(
                        "/(^|[^]_a-z0-9-=\"'\/])([a-z]+?):\/\/([{$validChars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([{$validChars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/])ftp\.([a-z0-9\-]+)\.([{$validChars}]+)/ei",
                        "/(^|[^]_a-z0-9-=\"'\/:\.])([a-z0-9\-_\.]+?)@([{$validChars}]+)/ei");
        $replacements = array(
                        "'\\1<a href=\"\\2://\\3\" title=\"\\2://\\3\" rel=\"external\">\\2://'.Input::truncate( '\\3' ).'</a>'",
                        "'\\1<a href=\"http://www.\\2.\\3\" title=\"www.\\2.\\3\" rel=\"external\">'.Input::truncate( 'www.\\2.\\3' ).'</a>'",
                        "'\\1<a href=\"ftp://ftp.\\2.\\3\" title=\"ftp.\\2.\\3\" rel=\"external\">'.Input::truncate( 'ftp.\\2.\\3' ).'</a>'",
                        "'\\1<a href=\"mailto:\\2@\\3\" title=\"\\2@\\3\">'.Input::truncate( '\\2@\\3' ).'</a>'");
        return preg_replace($patterns, $replacements, $string);
    }
    static public function truncate($string, $length = '50') {
        if ( empty($string) || empty($length) || strlen($string) < $length ) return $string;
        $len = floor( $length / 2 );
        $ret = substr($string, 0, $len) . " ... ". substr($string, 5 - $len);
        return $ret;
    }
    static public function nl2Br($string) {
        return preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $string);
    }
    static public function addSlashes($string) {
        if (!get_magic_quotes_gpc()) {
            $string = addslashes($string);
        }
        return $string;
    }
    static public function getVar($string) {
        return Input::stripSlashes($string);
    }
    static public function stripSlashes($string) {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        return $string;
    }
    static function hsc($string) {
        return preg_replace(array("/&amp;/i", "/&nbsp;/i"), array('&', '&amp;nbsp;'), htmlspecialchars($string, ENT_QUOTES));
    }
    static function undoHsc($text) {
        return preg_replace(array("/&gt;/i", "/&lt;/i", "/&quot;/i", "/&#039;/i", '/&amp;nbsp;/i'), array(">", "<", "\"", "'", "&nbsp;"), $text);
    }
    static public function safeHtml($text, $allowTags = null) {
        $text =  trim($text);
        $text = preg_replace('/<!--?.*-->/','',$text);
        $text =  preg_replace('/<\?|\?'.'>/','',$text);
        $text = preg_replace('/<script?.*\/script>/','',$text);
        $text =  str_replace('[','&#091;',$text);
        $text = str_replace(']','&#093;',$text);
        $text =  str_replace('|','&#124;',$text);
        $text = preg_replace('/\r?\n/','',$text);
        $text =  preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
        $text = preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
        while(preg_match('/(<[^><]+)(lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1],$text);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].$mat[3],$text);
        }
        if( empty($allowTags) ) { $allowTags = self::$htmlTags['allow']; }
        $text =  preg_replace('/<('.$allowTags.')( [^><\[\]]*)>/i','[\1\2]',$text);
        if ( empty($banTag) ) { $banTag = self::$htmlTags['ban']; }
        $text =  preg_replace('/<\/?('.$banTag.')[^><]*>/i','',$text);
        while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
        }
        while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
        }
        $text =  str_replace('\'\'','||',$text);
        $text = str_replace('""','||',$text);
        while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
        }
        $text =  str_replace('<','&lt;',$text);
        $text = str_replace('>','&gt;',$text);
        $text = str_replace('"','&quot;',$text);
        $text =  str_replace('[','<',$text);
        $text =  str_replace(']','>',$text);
        $text =  str_replace('|','"',$text);
        $text =  str_replace('  ',' ',$text);
        return $text;
    }
    static public function deleteHtmlTags($string) {
        while(strstr($string, '>')) {
            $currentBeg = strpos($string, '<');
            $currentEnd = strpos($string, '>');
            $tmpStringBeg = @substr($string, 0, $currentBeg);
            $tmpStringEnd = @substr($string, $currentEnd + 1, strlen($string));
            $string = $tmpStringBeg.$tmpStringEnd;
        }
        return $string;
    }
    static public function nl2($string, $br = '<br />') {
        if ($br == false) {
            $string = preg_replace("/(\015\012)|(\015)|(\012)/", '', $string);
        } elseif ($br != true){
            $string = preg_replace("/(\015\012)|(\015)|(\012)/", $br, $string);
        }
        return $string;
    }
}