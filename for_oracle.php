<?php
class DY_DBA 
{
    public $isshow  = 0;
    public $connect;
    var $table;
    var $ora_userid = "";
    var $ora_pwd    = "";
    var $tnsname    = "";
    function __construct(){
        $this->getDbConnect();    
    }
    function getDbConnect(){
        $this->connect = oci_connect($this->ora_userid,$this->ora_pwd,$this->tnsname);
        if (!$this->connect) { 
            die("資料連結失敗！"); 
        }
        //$this->db_link->Connect($this->tnsname, $this->ora_userid,$this->ora_pwd);
    }
    function table($table_name,$db_name=''){
        return $table_name.$db_name;
    }
    function tableField($field_name){
        $start = '`';
        $end = '`';
        return  $start.$field_name.$end;
    }
    function escapeString($str){
        return addslashes($str);
    }
    //新增資料-data：arr(key=>value)
    function insert_implode($data,$table_name){
        $table = $this->table($table_name);
        $sql = "INSERT INTO $table (". $this->implode_tableField($data) .") VALUES (".$this->implode_escapeString($data).")";
        $stid = oci_parse($this->connect,$this->show_sql($sql));
        foreach ($data as $key => $value) {
            oci_bind_by_name($stid, ":".$key, $value);
        }
        oci_execute($stid);
        //return $this->db_link->_insertid();
    }
    //更新資料-data：arr(key=>value)
    function update_implode($data,$id,$table_name){
        $table = $this->table($table_name);
        $arr = array();
        foreach($data as $key => $v){
            $arr[$key ."=:". $key] = ":".$v;
        }
        $sql = "UPDATE $table set ". $this->implode_str($arr) ." WHERE id=:rid";
        $stid = oci_parse($this->connect,$this->show_sql($sql));
        foreach ($data as $key => $value) {
            oci_bind_by_name($stid, ":".$key, $value);
        }
        oci_bind_by_name($stid, ':rid', $id);
        oci_execute($stid);
        return true;
    }
    function implode_tableField($data){
        $str = array();
        foreach($data as $key => $v){
            $str[$key] = $key;//$this->tableField($key);
        }
        return implode(',',$str);
    }
    function implode_escapeString($data){
        $str = array();
        foreach($data as $key => $v){
            $str[$key] = ':'.$this->escapeString($key);
        }
        return implode(',',$str);
    }
    function implode_str($data,$s=''){
        $str = array();
        foreach($data as $key => $v){
            if($s!='')
                $str[$key] = $s.($v).$s;
            else
                $str[$key] = $key;
        }
        return implode(',',$str);
    }
    function delete_tag($id,$table_name,$id_name='id'){
        $id         =   $this->escapeString($id);
        $table      =   $this->table($table_name);
        $cond       =   "$id_name=:$id_name";
        $sql        =   "delete from $table where $cond";
        $stid = oci_parse($this->connect,$this->show_sql($sql));
        oci_bind_by_name($stid, ":".$id_name, $id);
        if(oci_execute($stid))
            return  true;
        return  false;
    }
    
    function get_array_where_data($tab,$where,$order='',$select='*'){
        $tb = $this->table($tab); 
        $sql = "select $select from $tb where $where $order"; 
        
        $rs = oci_parse($this->connect,$this->show_sql($sql));
        oci_execute($rs);
        if($rs) {
            $arr = array();
            while ($row = oci_fetch_array($rs, OCI_BOTH))
                $arr[] = $row;
            return $arr;    
        }else
            return false;
    }
    //分頁 page：目前第幾頁   num_limit：一頁分幾筆
    function get_array_where_limit($page,$num_limit,$tab,$where,$order='',$select='*'){
        $tb = $this->table($tab);  
        $sql = "select count(*) as count from $tb where $where";
        $rs = oci_parse($this->connect,$this->show_sql($sql));
        oci_execute($rs);
        if($rs) {
            while ($row = oci_fetch_array($rs, OCI_BOTH))
                $count = $row[0];
            $this->show($count);
        }
        $max = $page * $num_limit + 1;
        
        $page = (($page==1)?1:$page - 1);
      
        $min = ($page==1)?1:$page * $num_limit + 1;
        $sql = "with tmp_minus as (
SELECT * FROM $tab WHERE ROWNUM < $max       and $where
MINUS 
SELECT * FROM $tab WHERE ROWNUM < $min and $where
)
select * from tmp_minus 
$order";
        $rs = oci_parse($this->connect,$this->show_sql($sql));
        oci_execute($rs);
        if($rs) {
            $arr = array();
            while ($row = oci_fetch_array($rs, OCI_BOTH))
                $arr[] = $row;
            return $arr;    
        }else
            return false;
    }
    
    function show($arr){
        if($this->isshow == 1){
            echo '<pre>';
            print_r($arr);
            echo '</pre>';
        }
    }
    function show_sql($sql){
        if($this->isshow == 1)
            echo "<hr> $sql <hr>";
        return $sql;
    }

}
