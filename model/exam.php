<?php
//*
//* 试卷相关（组卷、试卷列表、试卷内容）
//*
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// | 组卷的方法有待进一步优化，优化的内容如下：
// | 优化组卷，原来是先选题，再去掉已经组卷了的题目。
// | 要优化的是：选题时先根据试卷的题目id，查找已经组卷的题目，
// | 再使用where id not in选题，这样选出来的题目就是未组过卷的了，
// | 再从题目里，选择符合条件的进行组卷
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//*
namespace yoncms\model;
//*
class exams extends model{
  //* 存储已经组过卷的题目，以判断数据是否已经存在
  private $data = array();
  //* 所有题目
  private $artData = array();
  //* 选择题
  private $choice = '';
  //* 判断题
  private $judg = '';
  //* 填空题
  private $fill = '';
  //* 默认数据库表
  private $tab = 'exam';
	//* 用户id
	private $uid = '';
	//* post数据
	private $post = array();

	protected function __construct(){
		RND === 0 && exit(eCode('noAccess'));
		parent::__construct();
		$this->uid = $_SESSION['id'];
		$this->post = $_POST;
	}

  private function Tab( $a=0 ){
    $this->setTab( $this->getTab( $a ) );
    return;
  }

  private function examTab(){
    $this->setTab( $this->tab);
    return;
  }

  //显示试卷列表
  public function examList(){
    RND === 0 && Exit( 'No Access!' );
    $arr = array(
      //* 获取的结果有试卷id和标题，标题可以
      //* 在列表显示出来，id可以进行传递以查
      //* 找试卷的内容
      //* 试卷列表值需要id和标题就可以
      'Cols'=>'id,title',
      'where'=>array( 'owner'=>$this->uid ),
      'orderby'=> 'id desc',
      //* 最多60份
      'limit'=>array( 60 )
    );
    $this->examTab();
    $res = $this->FindAll( $arr );
    $this->assign( 'dir', rUrl('templates/exam/fenlei/') );
    $this->assign( 'res', $res );
    return;
  }
	//* 清空个人所组的所有试卷
	public function emptyTab(){
		RND === 0 && Exit( 'No Access!' );
		$this->del(array('where'=>'owner='.$this->uid));
	}
  //* 当个人组的试卷份数超过100份，就进行删除
  private function delExam(){
    //* 先查找会员里id最小的，也就是最早的数据，
    //* 和该会员的总数据数，如果超过3，就删除id
    //* 小的
    $arr = array(
      'Cols'=>'min(id)as mid,count(*)as cid',
      'where'=>array( 'owner'=>$this->uid )
    );
    $this->examTab();
    $res = $this->Find( $arr );

    if( $res['cid'] > 120 ){
      //* 删除id最小的行
      $arr = array(
        'where'=>array( 'id'=> $res['mid'] )
      );
      $this->del( $arr );
    }
  }
  //* 查询试卷的具体内容和答案，父链接是zujuan/myExams
  public function mContent(){
    RND === 0 && Exit( 'No Access!' );
    $arg = $this->operParam();
    $arr = array(
      'Cols' => 'title,concat_ws(",",choice,judgment,fillspace)cid',
      'where' => array( 'id' => $arg['id'] )
    );
    $this->examTab();
    //* 先取出对应试卷的题目的id
    $res = $this->Find( $arr );
    //* 试卷的标题
    $this->assign( 'titl', $res['title'] );
    $this->mAnswer( $res );
    return;
  }

  //* 获取试卷的答案
  private function mAnswer( $res ){
    $wrr = explode( ',', $res['cid'] );
    $arr = array(
      'Cols' => 'id,content',
      'where'=> array( 'id'=> $wrr,'fg'=>'or' )
    );
    $this->setTab( 'article' );
    //* 再查找出所有的题目
    $cont = $this->FindAll( $arr );
    $this->setTab( 'answer' );
    //* 再查找出所有的答案
    $answer = $this->FindAll( $arr );
    //* 排序
    $arr = $this->examPx( $cont, $answer, $wrr );
    $this->assign( 'cont', $arr[0] );
    $this->assign( 'answer', $arr[1] );
    return;
  }
  //* 对试卷的题目和答案根据试卷的顺序进行排序
  //* 参数1是题目，2是答案，3是试卷的顺序
  private function examPx( $art, $ans, $col ){
    $arr = array();
    $brr = array();
    foreach( $col as $v ){
      foreach( $art as $k=>$vv ){
        if( $v == $vv['id'] ){
          $arr[] = array( 'content'=>$vv['content'] );
          $brr[] = array( 'content'=>$ans[$k]['content'] );
        }
      }
    }
    return array( $arr, $brr );
  }
	//* 以上方法是试卷的内容、信息，下面的是组卷的方法 ***********************************
	//

	//group_concat把字段里的数据进行连接
	//max符合条件的行的指定字段的最大值

	//* *********************************************************+*********
  //* 带条件组卷的相关条件，分两种情况，一种是根据年级/主题，  |
	//* 组完整的卷子（包括3种题型，总分100），一种是就根据自定义 |
	//* 的条件，不组完整的卷子，也就无需考率分数，题数也自定义   |
	//* *********************************************************+*********
	//* order by(rand())查询效率低，所以先找出较多的数据，然后shuffle()打乱顺序再组合;
	//* 先把符号条件的题目全找出来，再去重，再按数量提取
	public function termsExam(){
		RND === 0 && Exit( 'No Access!' );
		$arrs = array();
		$arr = $this->terms();
		//* 不考虑题型，但是要计算总分，就是比自动组卷多了个条件
		$wh = getType( $arr[0] )!='array' ? 'where':'whis';
		$arrs = $this->yearZt( $arr[0] );
		if( !$arr[1] ){
			//* 数据要先去重后进一步筛选，25题选择题，1判断，7填空且为46分
			$this->mterm( $arrs, $wh );
		}else{
		  //* 有几种题型就得循环几次，并且按limit要求选择题数
			$key = '';
			$arrs = $this->setWhis( $wh, $arrs );
			//* 根据有几种题型进行循环，每循环一次生成一种题型数据
			foreach( $arr[1] as $k=>$v ){
				 //* 在where条件里加入题型
				 $arrs[$wh]['shape'] = $v;
				 //* fg用以指定各条件之间的关系，是and还是or
				 $arrs[$wh]['fg'][] = 'and';
				 $this->mterm( $arrs[$wh], $wh );
				 //* 打乱顺序
				 shuffle( $this->artData );
				 //* 截取指定数量的题目，也就是limit了
				 array_splice( $this->artData, $arr[2][$k] );
				 //* 结果进行拼接，并赋值给各自的属性
				 $key = $v == 0 ? 'choice' : ( $v == 1 ? 'jugd' : 'fill' );
				 $this->$key = implode( ',', arrCols( $this->artData,'id' ) );
		  }
		}
		//* id写入数据库
		$this->insertData();
		//* 组过的times加1
		$this->zjTimes();
	}
	private function setWhis( $wh, $arr ){
		$crr = array();
		foreach( $arr as $k=>$v ){
			$crr[$wh][$k] = $v;
		}
		return $crr;
	}
	//* 根据条件查找数据
	private function mterm( $arr, $wh ){
		$crr = array(
			'cols' => 'id, shape, score',
			$wh		 => $arr
		);
		$this->Tab( );
		//* 去掉已经组卷过的题目
		$this->artData = $this->FindAll( $crr );
		$this->artDel();
		if( !isset($arr['shape'] ))
			$this->choose();
	}
	//* 选题
	private function choose(){
		$this->autoExam();
		$this->autoExam(1, 1);
		$this->fillExam( );
	}
	//* 把post的数据拆分成3个数组
	private function terms(){
		//* 由于题目类型有三种，题目的数量不相同，所以根据题目类型进行组卷
		//* arr用来存储除shape外的条件
		$arr = array();
		//* shape用来存储非空的shape
		$shape = array();
		$limit = array();
		foreach( $this->post as $k => $v ){
			if( $v == '' ) continue;
			if( !strstr( $k,'shape') ) 
				$arr[$k] = $v;
			else{
				$shape[] = substr( $k, -1 );
				$limit[] = $v;
			}
		}
		return [$arr, $shape, $limit];
	}
	//* 生成年级/主题组卷的条件
	private function yearZt( $arr ){
		//* 分两种情况：多数据（years/zhutis）或单数据（year/zhuti）
		$crr = array();
		$n = 0;
		$i = 0;
		foreach( $arr as $k=>$v ){
			if( $k == 'title' )
				continue;
			if( isset( $arr[$k] ) ){
				if( $k == 'timer' ) {
				  $k = $k . '>';
					$v = $v.str_repeat( '0', 6 );
				}
				if( substr( $k, -1 ) == 's' ){
					$k = substr( $k, 0, -1 );
					$v = array( $v );
					$i = 1;
				}
				$crr[$k] = $v;
				if( $n > 0 )
					$crr['fg'][] = 'and';
				$n++;
			}
		}
		return $crr;
	}
  //* 组到试卷的题目，组卷次数zjTimes加1
  private function zjTimes(){
    $id = $this->choice . ',' . $this->judg . ',' . $this->fill;
    $id = explode( ',', $id );
    $arr = array(
      'Cols'=>array('zjTimes=zjTimes+'=>1),
      //update的where可以使用id=a||id=c，或者where id in( a,c )
      'where'=>array( 'id'=>$id,'fg'=>'or' )
    );
    $this->setTab( 'article' );
    //组卷的同时，被组卷的题目zjTimes（组卷次数）加1
    $aff = $this->alter( $arr );
    $aff = $aff ? rCode( 'okExam' ) : 'Fail';
    echo $aff . rCode( 'backTime' );
    return;
  }

  //* 获取已经存在于试卷的题目 *****************************************
  private function inExam(){
    //* 获取已经存在于试卷的题目id
    $arr = array(
      'Cols'=>'group_concat(concat_ws(",",choice,judgment,fillspace))as artDt',
      'where'=>array( 'owner'=>$this->uid )
    );
    $this->examTab();
		//* 设置group_concat的长度，默认是1024，如果超出会被截取，
		//* 如果不需要也没必要设置太大，否则影响速度
		$this->setIni( 'SET GLOBAL group_concat_max_len=10240' ); 
    $res = $this->Find( $arr );
		//echo $res['artDt'];
    //* 获取的id转成数组并去掉重复的id
    $res = array_unique( explode( ',', $res['artDt'] ) );
    return $res;
  }

	//* 获取所有的题目
  private function artDt(){
    $this->Tab( );
    $arr = array(
      'Cols'=>'id,shape,score'
    );
    $this->artData = $this->FindAll( $arr );
    return ;
  }

  //* 去掉已经组过卷的题目
  private function artDel(){
    $res = $this->artData;
    $inExam = $this->inExam();
    foreach( $res as $k=>$v ){
      if( in_array( $v['id'], $inExam ) )
        unset( $res[$k] );
    }
    $this->artData = $res;
    return;
  }
	//*******************************************************************

  //* 选择题/判断题选题，参数1是题型，2是题数
  private function autoExam( $shape=0, $n=25 ){
    $res = $this->artData;
    $res = shape( $res, $shape );
    //* 随机选题
    shuffle( $res );
    //* 取题数
    array_splice( $res, $n );
    $res = implode( ',', arrCols( $res, 'id' ) );
    if( $shape == 0 )
      $this->choice = $res;
    else
      $this->judg = $res;
  }

  //* 填空题选题，填空题的总分固定46分
  private function fillExam(){
    static $n = 0;
    //* 最多运行1000次
    if( $n++ > 1000 )
      exit( rCode( 'errExam' ).rCode( 'backTime' ) );
		if( $n == 1 )
			$this->artData = shape( $this->artData, 2 );
    $res = $this->artData;
    shuffle( $res );
    $srr = array_splice( $res, 0, 7 );
    //* 先保留选好的数组
    $fill = $srr;
    //* 提取数组中的score列，组成新数组
    $arr = arrCols( $srr, 'score' );
    /*
    * 计算填空题的分数如果等于46，则返回数组
    * 否则回调函数，这个条件有可能永远不成立
    * 所以在上面设置里运行次数，以免死循环
    */
    if( array_sum( $arr ) != 46 )
      $this->fillExam();
    else{
      $this->fill = implode( ',', arrCols( $fill, 'id' ) );
    }
  }
	//*
  //* 把组好的试卷写入数据库，数据库exam表的choice、judgment、fillspace
	//* 字段的默认值必须为空，因为手动组卷时，不一定组的是什么题型，而没有
	//* 的题型就是空，所以就无需插入
	//*
  private function insertData(){
    $this->examTab();
		//* 为里避免默认的标题重复
		$tt = '试卷_' . date( 'Ymdhis', TIME ) . substr( md5(rand()), rand( 0, 24 ), 8 );
    $subject = isset( $this->post['title'] ) ? $this->post['title'] : $tt;
		$cols = 'title, owner';
		$vals = array( $subject, $this->uid );
		//* 题型有的才进行数据的写入
		if( !!$this->choice ){ //* 选择题
		  $cols .= ',choice';
			$vals[] = $this->choice;
		}
		if( !!$this->judg ){ //* 判断题
		  $cols .= ',judgment';
			$vals[] = $this->judg;
		}
		if( !!$this->fill ){ //* 填空题
		  $cols .= ',fillspace';
			$vals[] = $this->fill;
		}
		$arr = array('cols'=>$cols, 'vals'=>$vals);
    $this->addData( $arr );
    return;
  }

  //* 自动组卷
  public function examAuto( ){
    RND === 0 && Exit( 'No Access!' );
    $this->artDt();
    $arg = $this->operParam();
		//* 如果没有参数不允许题目重复
    if( !isset( $arg['rep'] ) )
      $this->artDel();
    //* 选择题
    $this->autoExam( );
    //* 判断题
    $this->autoExam( 1, 1 );
    //* 填空题
    $this->fillExam();
    //* 写入数据库
    $this->insertData();
    //* zjTimes加1
    $this->zjTimes();
  }

  //* 删除试卷
  public function mExamDel(){
    RND === 0 && Exit( 'No Access!' );
		$id = $this->operParam();
		if( !isset($id['mysj'] ) ) return;
		$id = getId( $id['mysj'] ); 
    $arr = array(
      'where'=>array(
        'id'=>$id
      )
    );
    $this->examTab();
    $aff = $this->del( $arr );
    SucFail( rCode( 'examDel' ), $aff );
  }
  //* 去掉数组里的空元素，主要是条件组卷时，可能有的条件是空的
  private function removeNull( $arr ){
    if( !$arr ) return ;
    $crr = array();
    foreach( $arr as $v ){
      if( !is_array( $v ) ){
        if( $v !== null || $v !== '' )
          $crr[] = $v;
      }else
        $crr[] = $this->removeNull( $v );
    }
    return $crr;
  }
}







