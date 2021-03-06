<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Org\Net;
class UploadFile {
    private $config =   array(
        'maxSize'           =>  -1,
        'supportMulti'      =>  true,
        'allowExts'         =>  array(),
        'allowTypes'        =>  array(),
        'thumb'             =>  false,
        'imageClassPath'    =>  'ORG.Util.Image',
        'thumbMaxWidth'     =>  '',
        'thumbMaxHeight'    =>  '',
        'thumbPrefix'       =>  'thumb_',
        'thumbSuffix'       =>  '',
        'thumbPath'         =>  '',
        'thumbFile'         =>  '',
        'thumbExt'          =>  '',
        'thumbRemoveOrigin' =>  false,
        'zipImages'         =>  false,
        'autoSub'           =>  false,
        'subType'           =>  'hash',
        'dateFormat'        =>  'Ymd',
        'hashLevel'         =>  1,
        'savePath'          =>  '',
        'autoCheck'         =>  true,
        'uploadReplace'     =>  false,
        'saveRule'          =>  'uniqid',
        'hashType'          =>  'md5_file',
    );
    private $error = '';
    private $uploadFileInfo ;
    public function __get($name){
        if(isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }
    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }
    public function __isset($name){
        return isset($this->config[$name]);
    }
    public function __construct($config=array()) {
        if(is_array($config)) {
            $this->config   =   array_merge($this->config,$config);
        }
    }
    private function save($file) {
        $filename = $file['savepath'].$file['savename'];
        @unlink($filename);
        if(!$this->uploadReplace && is_file($filename)) {
            $this->error    =   '?????????????????????'.$filename;
            return false;
        }
        if( in_array(strtolower($file['extension']),array('gif','jpg','jpeg','bmp','png','swf')) && false === getimagesize($file['tmp_name'])) {
            $this->error = '??????????????????';
            return false;
        }
        if(!move_uploaded_file($file['tmp_name'], $this->autoCharset($filename,'utf-8','gbk'))) {
            $this->error = '???????????????????????????';
            return false;
        }
        if($this->thumb && in_array(strtolower($file['extension']),array('gif','jpg','jpeg','bmp','png'))) {
            $image =  getimagesize($filename);
            if(false !== $image) {
                $thumbWidth     =   explode(',',$this->thumbMaxWidth);
                $thumbHeight        =   explode(',',$this->thumbMaxHeight);
                $thumbPrefix        =   explode(',',$this->thumbPrefix);
                $thumbSuffix = explode(',',$this->thumbSuffix);
                $thumbFile          =   explode(',',$this->thumbFile);
                $thumbPath    =  $this->thumbPath?$this->thumbPath:dirname($filename).'/';
                $thumbExt = $this->thumbExt ? $this->thumbExt : $file['extension'];
                import($this->imageClassPath);
                for($i=0,$len=count($thumbWidth); $i<$len; $i++) {
                    if(!empty($thumbFile[$i])) {
                        $thumbname  =   $thumbFile[$i];
                    }else{
                        $prefix =   isset($thumbPrefix[$i])?$thumbPrefix[$i]:$thumbPrefix[0];
                        $suffix =   isset($thumbSuffix[$i])?$thumbSuffix[$i]:$thumbSuffix[0];
                        $thumbname  =   $prefix.basename($filename,'.'.$file['extension']).$suffix;
                    }
                    Image::thumb($filename,$thumbPath.$thumbname.'.'.$thumbExt,'',$thumbWidth[$i],$thumbHeight[$i],true);
                }
                if($this->thumbRemoveOrigin) {
                    unlink($filename);
                }
            }
        }
        if($this->zipImags) {
        }
        return true;
    }
    public function upload($savePath ='') {
        if(empty($savePath))
            $savePath = $this->savePath;
        if(!is_dir($savePath)) {
            if(is_dir(base64_decode($savePath))) {
                $savePath   =   base64_decode($savePath);
            }else{
                if(!mkdir($savePath)){
                    $this->error  =  '????????????'.$savePath.'?????????';
                    return false;
                }
            }
        }else {
            if(!is_writeable($savePath)) {
                $this->error  =  '????????????'.$savePath.'?????????';
                return false;
            }
        }
        $fileInfo = array();
        $isUpload   = false;
        $files   =   $this->dealFiles($_FILES);
        foreach($files as $key => $file) {
            if(!empty($file['name'])) {
                $file['key']          =  $key;
                $file['extension']  = $this->getExt($file['name']);
                $file['savepath']   = $savePath;
                $file['savename']   = $this->getSaveName($file);
                if($this->autoCheck) {
                    if(!$this->check($file))
                        return false;
                }
                if(!$this->save($file)) return false;
                if(function_exists($this->hashType)) {
                    $fun =  $this->hashType;
                    $file['hash']   =  $fun($this->autoCharset($file['savepath'].$file['savename'],'utf-8','gbk'));
                }
                unset($file['tmp_name'],$file['error']);
                $fileInfo[] = $file;
                $isUpload   = true;
            }
        }
        if($isUpload) {
            $this->uploadFileInfo = $fileInfo;
            return true;
        }else {
            $this->error  =  '????????????????????????';
            return false;
        }
    }
    public function uploadOne($file,$savePath=''){
        if(empty($savePath))
            $savePath = $this->savePath;
        if(!is_dir($savePath)) {
            if(!mkdir($savePath,0777,true)){
                $this->error  =  '????????????'.$savePath.'?????????';
                return false;
            }
        }else {
            if(!is_writeable($savePath)) {
                $this->error  =  '????????????'.$savePath.'?????????';
                return false;
            }
        }
        if(!empty($file['name'])) {
            $fileArray = array();
            if(is_array($file['name'])) {
                $keys = array_keys($file);
                $count    =   count($file['name']);
                for ($i=0; $i<$count; $i++) {
                    foreach ($keys as $key)
                        $fileArray[$i][$key] = $file[$key][$i];
                }
            }else{
                $fileArray[] =  $file;
            }
            $info =  array();
            foreach ($fileArray as $key=>$file){
                $file['extension']  = $this->getExt($file['name']);
                $file['savepath']   = $savePath;
                $file['savename']   = $this->getSaveName($file);
                if($this->autoCheck) {
                    if(!$this->check($file))
                        return false;
                }
                if(!$this->save($file)) return false;
                if(function_exists($this->hashType)) {
                    $fun =  $this->hashType;
                    $file['hash']   =  $fun($this->autoCharset($file['savepath'].$file['savename'],'utf-8','gbk'));
                }
                unset($file['tmp_name'],$file['error']);
                $info[] = $file;
            }
            return $info;
        }else {
            $this->error  =  '????????????????????????';
            return false;
        }
    }
    private function dealFiles($files) {
        $fileArray = array();
        $n = 0;
        foreach ($files as $file){
            if(is_array($file['name'])) {
                $keys = array_keys($file);
                $count    =   count($file['name']);
                for ($i=0; $i<$count; $i++) {
                    foreach ($keys as $key)
                        $fileArray[$n][$key] = $file[$key][$i];
                    $n++;
                }
            }else{
                $fileArray[$n] = $file;
                $n++;
            }
        }
        return $fileArray;
    }
    protected function error($errorNo) {
        switch($errorNo) {
            case 1:
                $this->error = '???????????????????????? php.ini ??? upload_max_filesize ??????????????????';
                break;
            case 2:
                $this->error = '?????????????????????????????? HTML ????????? MAX_FILE_SIZE ??????????????????';
                break;
            case 3:
                $this->error = '???????????????????????????';
                break;
            case 4:
                $this->error = '?????????????????????';
                break;
            case 6:
                $this->error = '????????????????????????';
                break;
            case 7:
                $this->error = '??????????????????';
                break;
            default:
                $this->error = '?????????????????????';
        }
        return ;
    }
    private function getSaveName($filename) {
        $rule = $this->saveRule;
        if(empty($rule)) {
            $saveName = $filename['name'];
        }else {
            if(function_exists($rule)) {
                $saveName = $rule().".".$filename['extension'];
            }else {
                $saveName = $rule.".".$filename['extension'];
            }
        }
        if($this->autoSub) {
            $filename['savename'] = $saveName;
            $saveName = $this->getSubName($filename).$saveName;
        }
        return $saveName;
    }
    private function getSubName($file) {
        switch($this->subType) {
            case 'date':
                $dir   =  date($this->dateFormat,time()).'/';
                break;
            case 'one':
                $dir   =  '';
                break;
            case 'hash':
            default:
                $name = md5($file['savename']);
                $dir   =  '';
                for($i=0;$i<$this->hashLevel;$i++) {
                    $dir   .=  $name{$i}.'/';
                }
                break;
        }
        if(!is_dir($file['savepath'].$dir)) {
            mkdir($file['savepath'].$dir,0777,true);
        }
        return $dir;
    }
    private function check($file) {
        if($file['error']!== 0) {
            $this->error($file['error']);
            return false;
        }
        if(!$this->checkSize($file['size'])) {
            $this->error = '???????????????????????????';
            return false;
        }
        if(!$this->checkType($file['type'])) {
            $this->error = '????????????MIME??????????????????';
            return false;
        }
        if(!$this->checkExt($file['extension'])) {
            $this->error ='???????????????????????????';
            return false;
        }
        if(!$this->checkUpload($file['tmp_name'])) {
            $this->error = '?????????????????????';
            return false;
        }
        return true;
    }
    private function autoCharset($fContents, $from='gbk', $to='utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            return $fContents;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    }
    private function checkType($type) {
        if(!empty($this->allowTypes))
            return in_array(strtolower($type),$this->allowTypes);
        return true;
    }
    private function checkExt($ext) {
        if(!empty($this->allowExts))
            return in_array(strtolower($ext),$this->allowExts,true);
        return true;
    }
    private function checkSize($size) {
        return !($size > $this->maxSize) || (-1 == $this->maxSize);
    }
    private function checkUpload($filename) {
        return is_uploaded_file($filename);
    }
    private function getExt($filename) {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }
    public function getUploadFileInfo() {
        return $this->uploadFileInfo;
    }
    public function getErrorMsg() {
        return $this->error;
    }
}
?>