<?php

//イメージは「蠱毒」

$source=create_source();//最初の入力ソース
$fight='fight';//評価関数
$generation_count=$argv[1]??1000;//世代数

//遺伝情報を持つ（戦わせる）オブジェクト
class gene {
  public $parent=null;//

  public $win=0;
  public $lose=0;

  //ポケモン風のステータス
  public $HP=100;
  public $AT=100;
  public $DF=100;
  public $AG=100; //はやさ
#  public $SA=100;
#  public $SD=100;
  public $WP=50;//技の威力
  public $WH=50;//技の命中率


  public function NextGen(){
    $clone= clone $this;
    
    $clone->evolv();
    
    $clone->parent=$this;
    return $clone;
  }

  //変異させる
  private function evolv(){
    
    //どこかのパラメータを足したり引いたりする
    foreach([-1,+1] as $v){
      $k=mt_rand(1,6);
      switch($k){
        case 1:$this->HP += $v;break;
        case 2:$this->AT += $v;break;
        case 3:$this->DF += $v;break;
        case 4:$this->WP += $v;break;
        case 5:$this->WH += $v;break;
        case 6:$this->AG += $v;break;
      }      
    }    
  }
  public function __toString(){
    return sprintf(
      "( %03d / %03d / %05.2f％ )  HP: %03d AT:%03d DF:%03d AG:%03d [WP:%03d WH:%03d] \n",
      $this->win,
      $this->lose,
      $this->win/($this->win + $this->lose)*100,
      $this->HP,
      $this->AT,
      $this->DF,
      $this->AG,
      $this->WP,
      $this->WH
    );
  }

}


function create_source(){
  return array_fill(0,100,new gene());
  
//  return $list;
}

//仮想バトルを行うやつ
function fight($a,$b){
  
  $aHP=$a->HP;
  $bHP=$b->HP;
  $attack=function($a,$b){
    //技
    $skill_power=$a->WP;
    
    //攻撃防御計算
    $d=($skill_power * $a->AT / $b->DF) / 50 + 2;

    //補正
    $d= $d * mt_rand(0xD9,0xFF) / 0xFF;

    //命中判定
    if($a->WH < mt_rand(1,99)){
      //ミス
      return 0;
    }

    return ($d<1)?1:floor($d);
  };



  while(0<$aHP && 0<$bHP ){
    //Aの攻撃
    $bHP -= $attack($a,$b);

    //Bの攻撃
    $aHP -= $attack($b,$a);
  }
  
  if($aHP <= $bHP){
      $a->lose+=1;
      $b->win+=1;
  }
  else{
      $a->win++;
      $b->lose++;     
  }

  return $aHP <= $bHP;
}


//世代設定まで繰り返す
for($i=0;$i<$generation_count;$i++){

  usort($source,$fight);//戦わせる
  //下位半分を切り捨て
  $source=array_slice($source,0,count($source)>>1);//半分
  /*
  echo "\n<$i 世代 の生き残り>\n";
  foreach($source as $rank=>$o){
    echo "<{$rank}> $o";
  }
  */

  $next_gen=[];
  foreach($source as $o){
    //2つ分作る
    $next_gen[]=$o->NextGen();
    $next_gen[]=$o->NextGen();
  }
  $source=$next_gen;

  echo "\r $i Gen...";
}


foreach($source as $rank=>$o){
  echo "<{$rank}> $o";
}


