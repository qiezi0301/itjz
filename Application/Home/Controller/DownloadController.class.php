<?php
namespace Home\Controller;

class DownloadController extends CommonController {
    public function zip_local(){
    	$id = I('id', 0, 'intval');

		if (!$id) {
            $this->error('参数错误');
        }

		$user = get_user();
		if(!$user){
			$data['result'] = 'login';
		}
    	
    	
    	//网盘下载
    	$downlist = M('soft')->find($id);
		if($downlist['scores'] > 0 ){
			if($downlist['scores'] > $user['score']){
				$data['code'] = 'points_not_enough';//判断积分是否够下载
			}else{
				$data['code'] = 'wangpan';
				$data['wangpan_url'] = $downlist['wangpan_url'];
				$data['wangpan_pwd'] = $downlist['wangpan_pwd'];
				//查询下载记录
				$isdown = M('download')->where(array('uid' => $user['id'],'tid' => $downlist['id']))->find();
				if(empty($isdown)){
					//记录积分操作
					$log['uid'] = $user['id'];
					$log['scoreinfo'] = '-'.$downlist['scores'];
					$log['type'] = 2;
					$log['addtime'] = time();
					$log['title'] = $downlist['title'];
					//$log['url'] = $downlist['url'];
					$log['descrip'] = '下载资源，积分-'.$downlist['scores'];
					M('member_slog')->add($log);
					M('member')->where('id='.$user['id'])->setDec('score',$downlist['scores']);  //减去相应积分
				}

			}
		}

		//保存下载记录
		$downinfo = array(
			'uid'=>$user['id'],
			'tid'=>$downlist['id'],
			'mtype'=>$downlist['cid'],
			'addtime'=>time(),
			'uid_to'=>$downlist['userid'],
			'points'=>$downlist['scores'],
			'is_vip'=>0,
			'is_original'=>0
		);
		M('download')->add($downinfo);
		//下载次数添加
        M('soft')->where(array('id'=>$id))->setInc('downnum');
        echo json_encode($data); 
    }

}