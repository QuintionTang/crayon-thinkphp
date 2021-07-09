<?php
// +----------------------------------------------------------------------
// | Rusty Crayon WebThink[锈蜡笔Web基础架构]
// +----------------------------------------------------------------------
// | ProductionSteps：生产流程信息Model
// +----------------------------------------------------------------------
// | Copyright (c) rusty-crayon 2021-2023 https://www.rusty-crayon.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: QuintionTang <QuintionTang@gmail.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;
class AttachController extends CommonController {
    public function _initialize () {
        parent::_initialize();
    }

    public function index(){
        $this->display();
    }

    protected function _upload_init($upload,$usedfor,$subType="date") {
        $file_type = empty($_GET['filetype']) ? 'image' : trim($_GET['filetype']);
        $dir_name = $usedfor;
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2','hex','bin'),
        );
        $allow_exts_conf = explode(',', C('allow_exts'));
        $allow_max = C('attr_allow_size');
        $allow_exts = array_intersect($ext_arr[$file_type], $allow_exts_conf);
        $allow_max && $upload->maxSize = $allow_max * 1024;
        $allow_exts && $upload->allowExts = $allow_exts;
        $attach_path = C('attach_path');
        $savePath = $attach_path . $dir_name . '/';

        mkdirs($savePath);

        $upload->savePath = $savePath;
        $upload->saveRule = 'uniqid';
        $upload->autoSub = true;
        $upload->subType = $subType;
        if ($subType=="date"){
            $upload->dateFormat = 'Y/m/d/';
        }
        
        return $upload;
    }
    public function ABC2decimal($abc)
    {
        $ten = 0;
        $len = strlen($abc);
        for($i=1;$i<=$len;$i++){
            $char = substr($abc,0-$i,1);//反向获取单个字符

            $int = ord($char);
            $ten += ($int-65)*pow(26,$i-1);
        }
        return $ten;
    }
    /**
     * 导入
     */
    public function excel_import(){
            $usedfor = empty($_GET['usedfor']) ? 'picture' : trim($_GET['usedfor']);

            $used_for = $usedfor;
            import('ORG.Net.UploadFile');
            $upload = $this->_upload_init(new \Org\Net\UploadFile(),$usedfor);// 实例化上传类
            $attach = array();
            $attachment = array();
            $attach["success"] = 0;
            $info = "";
            if(!$upload->upload()) {// 上传错误提示错误信息
                $upload_error = $upload->getErrorMsg();
                $attach["msg"] = $upload_error;
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
            }
            // 上传成功后开始处理
            if(is_array($info)){
                $info = $info[0];
                import("Org.Util.PHPExcel");
                import("Org.Util.PHPExcel.Reader.Excel5");
                import("Org.Util.PHPExcel.Reader.Excel2007");
                
                import("Org.Util.PHPExcel.IOFactory.php");

                $filePath = $info["savepath"] . $info["savename"];
                $input_file_type = \PHPExcel_IOFactory::identify($filePath);

                $objExcel = new \PHPExcel();

                $objReader = \PHPExcel_IOFactory::createReader($input_file_type);
                //$objReader ->setReadDataOnly(true); //只读取数据,会智能忽略所有空白行,这点很重要！！！
                $objPHPExcel = $objReader->load($filePath); //加载Excel文件
                $objWorksheet = $objPHPExcel->getActiveSheet();
                $data = $objWorksheet->toArray();
                $attach_path = C('attach_path');
                $subpath = date('YmdHm', time());

                $imageFileRealPath = $attach_path . "excel_img/".$subpath ."/" ;  // Excel图片存储路径

                mkdirs($imageFileRealPath);

                $i = 0;
                $rebarRows = array();
                // 下面开始处理图片
                foreach ($objWorksheet->getDrawingCollection() as $img) {
                    list($startColumn, $startRow) = \PHPExcel_Cell::coordinateFromString($img->getCoordinates()); //获取图片所在行和列
                    $imageFileName = uniqid();
                    try {
                        switch($img->getExtension()) {
                            case 'jpg':
                            case 'jpeg':
                                $imageFileName .= '.jpeg';
                                $source = imagecreatefromjpeg($img->getPath());
                                imagejpeg($source, $imageFileRealPath.$imageFileName,100);
                                break;
                            case 'gif':
                                $imageFileName .= '.gif';
                                $source = imagecreatefromgif($img->getPath());
                                $width = imagesx($source);
                                $height = imagesy($source);
                                if (function_exists("imagecreatetruecolor")) {
                                    $newImg = imagecreatetruecolor($width, $height);
                                    /* --- 用以处理缩放png图透明背景变黑色问题 开始 --- */
                                    $color = imagecolorallocate($newImg,255,255,255);
                                    imagecolortransparent($newImg,$color);
                                    imagefill($newImg,0,0,$color);
                                    ImageCopyResampled($newImg, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                                } else {
                                    $newImg = imagecreate($width, $height);
                                    ImageCopyResized($newImg, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                                }
                                imagejpeg($source, $imageFileRealPath.$imageFileName,100);
                                break;
                            case 'png':
                                $imageFileName .= '.png';
                                $source = imagecreatefrompng($img->getPath());
                                $width = imagesx($source);
                                $height = imagesy($source);
                                if (function_exists("imagecreatetruecolor")) {
                                    $newImg = imagecreatetruecolor($width, $height);
                                    
                                    /* --- 用以处理缩放png图透明背景变黑色问题 开始 --- */
                                    $color = imagecolorallocate($newImg,255,255,255);
                                    imagecolortransparent($newImg,$color);
                                    imagefill($newImg,0,0,$color);
                                    ImageCopyResampled($newImg, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                                } else {
                                    $newImg = imagecreate($width, $height);
                                    ImageCopyResized($newImg, $source, 0, 0, 0, 0, $width, $height, $width, $height);
                                }
                                imagejpeg($newImg, $imageFileRealPath.$imageFileName,100);
                                break;
                        }
                        $startColumn = $this->ABC2decimal($startColumn);
                        $data[$startRow-1][$startColumn] = $imageFileRealPath . $imageFileName;
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                    
                }
                $rowsData = array();
                foreach ($data as $key => $rowData) {
                    $serial = safty_value($rowData[0],0,'intval'); // 第一列 序号
                    $title = safty_value($rowData[1],'','trim'); // 第二列 名称
                    $logo_save_path = safty_value($rowData[2],'','trim');  // logo图形保存路径
                    $remark = safty_value($rowData[3],'','trim');  //备注

                    if ($serial >0 && $logo_save_path!=="" && $title!==""){

                        array_push($rowsData,array(
                            "serial"=>$serial,
                            "title"=>$title,
                            "logo_path"=>$logo_save_path,
                            "remark"=>$remark
                        ));
                            
                    }
                }
                $this->update_excel_data($rowsData); // 将导入的数据生成文件缓存
                $upload_result = array(
                    "count" => count($rowsData),
                    "success" => 1,
                    "state"=>"SUCCESS"
                );
                
            } else {
                $upload_result = array(
                    "message" => "上传失败!",
                    "success" => 0
                );
            }
            echo json_encode($upload_result);
    }
    
    private function update_excel_data($data){
        update_file_chache("excel_data",$data);
    }
    
    public function upload_files(){
        $usedfor = empty($_GET['usedfor']) ? 'picture' : trim($_GET['usedfor']);
            //$editorid = $_GET["editorid"];

            $uid = $manage_sess["id"];
            $used_for = $usedfor;

            import('ORG.Net.UploadFile');
            $upload = $this->_upload_init(new \Org\Net\UploadFile(),$usedfor);// 实例化上传类
            $attach = array();
            $attachment = array();
            $attach["success"] = 0;
            $info = "";
            if(!$upload->upload()) {// 上传错误提示错误信息
                $upload_error = $upload->getErrorMsg();
                $attach["msg"] = $upload_error;
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
            }
            if(is_array($info)){
                $info = $info[0];
                $attach["success"] = 1;
                $savename = $info['savename'];
                $savepath = $info["savepath"];
                $attach['save_name'] =basename($savename);
                $attach['size'] =$info["size"];
                $attach['type'] =$info["type"];
                $attach['extension'] = strtolower(array_pop(explode('.', trim($attach['save_name']))));

                $sava_path = str_replace($attach['save_name'],'',$savename);
                $attach['save_path'] =$savepath . substr($sava_path,0,strlen($sava_path)-1);

                $attach['add_time'] =time();

                $attach['hash'] = md5($attach['save_path'].$attach['add_time']);

                $attachment["uid"] = $uid;
                $attachment["add_time"] = $attach['add_time'];
                $attachment["name"] = $info["name"];
                $attachment["size"] = $attach['size'];
                $attachment["hash"] = $attach['hash'];
                $attachment["type"] = $attach['type'];
                $attachment["extension"] = $attach['extension'];
                $attachment["save_path"] = $attach['save_path'];
                $attachment["save_name"] = $attach['save_name'];
                $attachment["usedfor"] = $used_for;
                $attachment["language"] = $this->language;

                $attach_id = time();

                $attach_url = C('attach_url');    //附件显示URL,可以将附件作为独立站点
                $attach_full_url = $attach_url . J($attach['save_path'].$attach['save_name']);

                $upload_result = array(
                    "originalName" => $attachment["name"] ,
                    "name" => $attachment["name"] ,
                    "url" =>  $attach_full_url,
                    "size" => $attachment["size"] ,
                    "type" => $attachment["type"] ,
                    "attach_id" => $attach_id,
                    "fileid" => $attach_id,
                    "success" => 1,
                    "state"=>"SUCCESS"
                );

            } else {
                $upload_result = array(
                    "message" => "上传失败!",
                    "success" => 0
                );
            }
        echo json_encode($upload_result);
    }

}
?>