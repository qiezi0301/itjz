<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Category;
class AjaxController extends Controller {
    public function get_proplist(){
		if(!IS_AJAX){
			exit(0);
		}
		$ids = I('ids', '');
		$prop_value = I('prop_value', '');
		$values = array();
		if($prop_value){
			//把字符串打散为数组
			$values = explode(',',$prop_value);
		}
		$map['id'] = array('in',$ids);
		$proplist = M('Property')->where($map)->select();
		$str = '';
		foreach($proplist as $key=>$v){
			$str .= '<label for="inputCopyfrom" class="col-sm-2 control-label">'.$v['name'].'</label>';
			$str .= '<div class="col-sm-9">';
			$valuelist = M('PropertyValue')->where("prop_id=".$v['id'])->select();
			foreach($valuelist as $k=>$v1){
				$ischecked = '';
				if(in_array($v1['id'],$values)){
					$ischecked = 'checked="checked"';
				}
				$str .= '<label class="checkbox-inline"><input type="checkbox" name="prop_value[]" value="'.$v1['id'].'" '.$ischecked.' />'.$v1['vname'].'</label>';
			}
			$str .= '</div>';
		}
		$this->ajaxReturn($str);
    }
}