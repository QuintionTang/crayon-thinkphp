<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Model;
use Think\Model;
use Think\Model\RelationModel;
class BaseModel extends RelationModel {
    var $attach_fields = array('img', 'extimg');
    var $editor_fields=array('info');
    protected function _after_find(&$result, $options) {
        parent::_after_find($result,$options);
        if (method_exists($this, '_parse_item')) {
            $result = $this->_parse_item($result);
        }
        $result = $this->parse($result);
    }
    protected function _after_getField(&$result, $options) {
        parent::_after_getField($result,$options);
        if(!is_array($result) &&in_array($options['field'],$this->attach_fields)){
            $result=attach($result, $this->name,true);
        }
    }
    function _after_select(&$result, $options) {
        parent::_after_select($result,$options);
        foreach ($result as $key => $val) {
            if (method_exists($this, '_parse_item')) {
                $result[$key] = $this->_parse_item($val);
            }
            $result[$key] = $this->parse($result[$key]);
        }
    }
    function parse($info) {
        foreach ($this->attach_fields as $val) {
            if (array_key_exists($val, $info)) {
                $info['_'.$val] = attach($info[$val], $this->name,true);
            }
        }
        foreach($this->editor_fields as $val){
            if(array_key_exists($val,$info)){
                $info[$val]=parse_editor_info($info[$val]);
            }
        }
        return $info;
    }

}

?>
