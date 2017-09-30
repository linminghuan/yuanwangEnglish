<?php
/**
 * 文章管理Controller
 * @author: linminghuan
 * @date: 2017/9/16
 * @verson: 1.0
 * @description:  
 * （1）完成基本功能；（2017/9/16）
 * （2）添加sList方法；（2017/9/19）
 * （3）添加detail方法；（2017/9/19）
 * （4）添加首页的数据；（2017/9/24）
 */
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller 
{
    public function index()
    {
    	$article = M('articles');
    	$category = M('categories');
        $setting = M('settings');
    	$photo = M('photos');
        $recommend = M('recommends');
    	//导航栏
    	$menuData = $this->subCategory();
        $this->assign('menuData', $menuData);
    	//轮播图
    	$map['status'] = 1;
    	$sliderData = $photo->where($map)->select();
    	$this->assign('sliderData', $sliderData);
        //footer数据
        //TODO做缓存
        $footerData = $setting->where('user_id=1')->select();
        $this->assign('footerData', $footerData[0]);
        //
        $map['status'] = 1;
        $recommendData = $recommend->where($map)->select();
        $this->assign('recommendData', $recommendData);
        //通知新闻的数据recommend的数据
        $temp = $category->where("name='News & Events'")->select();
        $newsData['category_id'] =  $temp[0]['id'];
        $map['category_id'] = $temp[0]['id'];
        $map['status'] = 1;
        $newsData['data'] = $article->where($map)->order('sort')->limit(C('NEWS'))->select();
        $this->assign('newsData', $newsData);
        $this->display('Index/index');
    }

    //递归查询所有的栏目
	private function subCategory($pid = 1, $where = array())
	{ 
        $category = M('categories');
        /*if(!isset($pid)){
            $menuData = $category->where("name='menu'")->ORDER('sort')->field('id,name,pid')->select();
            $pid =  $menuData[0]['id'];
        }*/
		$categoryArr = Array();
		if(isset($where)) $map = $where;
        $map['navigation'] = array('eq', 1);
		$map['pid'] = $pid;
        $map['status'] = 1;
		$tmp = $category->where($map)->field('id,name,pid')->order('sort')->select();
		if(count($tmp) != 0){
			foreach ($tmp as $key => $value) {
				$tmp1 = $this->subCategory($value['id']);
				$value['next'] = $tmp1;
				$categoryArr[] = $value;
			}
			return $categoryArr;
		}else{
			return;
		}
	}

    //返回栏目列表
    public function sList ($id)
    {
        if(isset($id)){
            $category = M('categories');
            $setting = M('settings');
            //导航栏
            $menuData = $this->subCategory();
            $this->assign('menuData', $menuData);
            //返回左边导航栏数据
            $tmp = $category->find($id);
            $tmp['next'] = $this->subCategory($id);
            $this->assign('lNavData', $tmp);
            //footer数据
            //TODO做缓存
            $footerData = $setting->where('user_id=1')->select();
            $this->assign('footerData', $footerData[0]);
            //返回列表数据
            $article = M('articles');
            $p = 1;
            if(isset($_GET['p'])){
                $p = $_GET['p'];
            }
            $map['category_id'] = $id;
            $map['status'] = 1;
            $data = $article->where($map)->order('sort')->page($p.',6')->select();
            if(count($data) != 0){
                $count = $article->where($map)->count();
                if($count == 1){
                    $this->assign('detailData', $data[0]);
                    $type = 'detail';
                }else{
                    $this->assign('listData', $data); 
                    $Page = new \Think\Page($count,6);// 实例化分页类 传入总记录数和每页显示的记录数
                    $show = $Page->show();// 分页显示输出
                    $this->assign('page',$show);
                    $type = 'list';
                }
                //返回类型
                $this->assign('type', $type);
                $this->display('Index/show');
            }else{
                $this->error('404,暂无数据！');
            }
            
        }else{
            $this->error('404, 没找到！');
        }
    }

    //返回文章详情
    public function detail($category_id, $id)
    {
        if($id){
            $category = M('categories');
            $setting = M('settings');
            //导航栏
            $menuData = $this->subCategory();
            $this->assign('menuData', $menuData);
            //返回左边导航栏数据
            $tmp = $category->find($category_id);
            $tmp['next'] = $this->subCategory($category_id);
            $this->assign('lNavData', $tmp);
            //footer数据
            //TODO做缓存
            $footerData = $setting->where('user_id=1')->select();
            $this->assign('footerData', $footerData[0]);
            //返回文章内容
            $article = M('articles');
            $map['status'] = 1;
            $map['id'] = $id;
            $data = $article->where($map)->select();
            if(count($data) != 0){
                $this->assign('detailData', $data[0]);
                $this->assign('type', 'detail');
                $this->display('Index/show');
            }else{
                $this->error('404,没找到！');
            }
        }else{
            $this->error('404,没找到！');
        }
    }

}