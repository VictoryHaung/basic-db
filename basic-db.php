class Basic-db EXTENDS ADODB
{
    public $isshow = 0;
    function __construct($company_uuid){
        parent::__construct($company_uuid);
    }
    //新增資料-data：arr(key=>value)
    function insert_implode($data,$table_name){
        $table = $this->table($table_name,$this->company_db);
        $sql = "insert into $table (". $this->implode_tableField($data) .")value (".$this->implode_escapeString($data).")";
        $this->db_link->Execute($this->show_sql($sql));
        return true;
    }
    //更新資料-data：arr(key=>value)
    function update_implode($data,$id,$table_name){
        $table = $this->table($table_name,$this->company_db);
        $arr = array();
        foreach($data as $key => $v){
            $arr[$this->tableField($key) ."=". $this->escapeString($v)] = $v;
        }
        $sql = "UPDATE $table set ". $this->implode_str($arr) ." WHERE `id`=$id";
        $this->db_link->Execute($this->show_sql($sql));
        return true;
    }
    function implode_tableField($data){
        $str = array();
        foreach($data as $key => $v){
            $str[$key] = $this->tableField($key);
        }
        return implode(',',$str);
    }
    function implode_escapeString($data){
        $str = array();
        foreach($data as $key => $v){
            $str[$key] = $this->escapeString($v);
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
        $table      =   $this->table($table_name,$this->company_db);
        $cond       =   "$id_name=$id";
        $sql        =   "delete from $table where $cond";
        if($this->db_link->Execute($this->show_sql($sql)))
            return  true;
        return  false;
    }
    function get_array_where_data($tab,$where,$order='',$select='*'){
        $tb = $this->table($tab, $this->company_db);
        $tag_where = '';
        
        $stmt = "select $select from $tb $where $tag_where $order ";
        $rs = $this->db_link->Execute($this->show_sql($stmt));
        if($rs && $rs->RecordCount()>0){
            $arr = array();
            foreach ($rs as $key => $value)
                $arr[$key] = $value;
            return $arr;   
        }else
            return false;
    }
    function get_sql_between_date($name,$start_date='',$end_date=''){
        $start_date = ($start_date=='' && isset($_GET['start']))?$_GET['start']:$start_date;
        $end_date   = ($end_date=='' && isset($_GET['end']))    ?$_GET['end']:$end_date;
        if(isset($start_date) && $start_date!=''){
           $start = $this->inputDate2Ad($start_date);
        }else{
           $start = date('Y-m-d');
        }
        if(isset($end_date) && $end_date!=''){
           $end = $this->inputDate2Ad($end_date);
        }else{
           $end = '9999-12-31';
        }
        return "and date($name) BETWEEN date('$start') and date('$end')";
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
