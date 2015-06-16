<?php
class App_Page{

    public static $dbRes = null;
    public static $html = null;
    public static function Paging($class, $method, $data, 
            $page = 1, $listNum = 20, $pageShow = 10) {
        self::$dbRes = array();
        self::$html = '';
        if(is_object($data) && method_exists($class, $method)){
            $pageData = clone $data;
            $pageData->orderBy = null;
            $pageData->field = ' count(*) AS count_n ';
            $pageData->limit = null;
            $count = $class::$method($pageData);
            $num = $count[0]['count_n'];
            $pageNumber = ceil($num/$listNum);
            $page= intval($page);
            if($page > $pageNumber){
                $page = $pageNumber;
            }
            if($page <1 ) {
                $page = 1;
            }
            $data->limit = (($page-1)*$listNum) . "," . $listNum;
            $dbRes = $class::$method($data);
            $currPage = self::getCurrentPage();
            $html = '';
            if($num > $listNum){
                $jumpPage = $currPage;
                if(strpos($currPage,'?') > -1) {
                    $jumpPage = $currPage.'&';
                }else{
                    $jumpPage = $currPage.'?';
                }
                
                $html .='<div class="row"><ul class="pagination">';
                $startPage = $page;
                $leftInt = ceil($pageShow/2);
                $rightInt = $pageShow - $leftInt ;
                if($page < $leftInt ){
                    $startPage = 1;
                } else {
                    if($page + $rightInt + 1 >  $pageNumber  ){
                        $startPage = $pageNumber - $pageShow +1;
                    } else {                       
                        $startPage = $page - $leftInt +1;
                    }
                }
                $endPage = $startPage + $pageShow -1;
                if($endPage > $pageNumber){
                    $endPage = $pageNumber;
                }
                for ($i = $startPage; $i <= $endPage; $i++){
                    if($startPage != 1 && $i ==$startPage ){
                        $html .= '<li class="arrow"><a href="'.$jumpPage .'pg=1" >&laquo;</a></li>';
                    }
                    $html .= '<li '.(($i == $page)?'class="current"':'')
                    .'><a href="'.$jumpPage .'pg='.$i.'" >'.$i.'</a></li>';
                    if($endPage != $pageNumber && $i == $endPage){
                        $html .= '<li '.(($i == $page)?'class="current"':'')
                        .'><a href="'.$jumpPage .'pg='.$pageNumber.'" >&raquo;</a></li>';
                    }
                }
               $html .= '&nbsp;总页:  '.$pageNumber;
               $html .= "</ul></div>";
            }
            self::$dbRes = $dbRes;
            self::$html = $html;
        }
      return  true;
    }

    public static function getCurrentPage(){
        $current = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
        if(isset($_SERVER["REQUEST_URI"])){
            $pos = strpos($_SERVER["REQUEST_URI"], '?');
            if($pos !== false){
                $current .= substr($_SERVER["REQUEST_URI"], 0, $pos );
                $query = substr($_SERVER["REQUEST_URI"], $pos +1);
                $aryExplode = explode('&', $query);
                $newAry = Array();
                foreach($aryExplode as $equalKey => $equalValue){
                    $aryEqual = explode('=', $equalValue);
                    if($aryEqual[0] != 'pg'){
                        $newAry[$equalKey] = $equalValue;
                    }
                }
                if(!empty($newAry)){
                    $current .= '?' . implode('&', $newAry);
                }
            }else {
                $current .= $_SERVER["REQUEST_URI"];
            }
        }
        return $current;
    }
}
?>