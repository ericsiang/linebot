<?php

class PdoConnect{
    //主機
    protected $dbHost;
    //port
    protected $dbPort;
    //資料庫名稱
    protected $dbName;
    //使用者名稱
    protected $dbUser;
    //使用者帳號
    protected $dbPass;
    //資料表
    protected $table;
    //編碼
    protected $charset;
    //pdp連線
    protected $pdo;

    protected $options;

    public function __construct($config){
        $this->dbHost=$config['dbHost'];
        $this->dbPort=$config['dbPort'];
        $this->dbName=$config['dbName'];
        $this->dbUser=$config['dbUser'];
        $this->dbPass=$config['dbPass'];
        $this->charset=$config['charset'];
        
        $this->connect();
        $this->initOptions();
    }



    protected function connect(){
         //連結的數據源，以下是使用mysql資料庫，並設為本地端，和資料庫名稱
        $dsn	='mysql:host='.$this->dbHost.';dbname='.$this->dbName.';port='.$this->dbPort.'';
       
        //判斷是否連接資料庫成功
        try{
            //初始化PDO，連接數據源，資料庫登入的帳密
            $this->pdo = new PDO($dsn, $this->dbUser, $this->dbPass);
            //設為UTF8
            $this->pdo	->	query("SET NAMES utf8");

            //防止only_full_group_by
            $better_sql_defaults=array("set SESSION sql_mode ='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

            foreach ($better_sql_defaults as $sql){
                $this->pdo->query($sql);
            }

        } catch (PDOException $e){
            //連接資料庫失敗
            throw new PDOException("Connect database Error!(連接資料庫失敗) ".$e->getMessage()); 	
        }
    }

    protected function initOptions(){
        $arr=['where','table','field','order','group','having','limit'];
        
        foreach($arr as $value){
            $this->options[$value]='';
        }

    }

    public function field($field){
        //如果不為空
        if(!empty($field)){
            if(is_string($field)){
                $this->options['field']=$field;
            }else if(is_array($field)){
                $this->options['field']=join(',',$config);
            }
        }else{
            $this->options['field']='*';
        }

        return $this;
    }

    public function table($table){
        //如果不為空
        if(!empty($table)){
            $this->options['table']=$table;
        }

        return $this;
    }

    public function where($where){
        //如果不為空
        if(!empty($where)){
            $this->options['where']='where '.$where;
        }
        return $this;
    }

    public function group($group){
        //如果不為空
        if(!empty($group)){
            $this->options['group']='group by '.$group;
        }

        return $this;
    }

    public function having($having){
        //如果不為空
        if(!empty($having)){
            $this->options['having']='having '.$having;
        }

        return $this;
    }

    public function order($order){
        //如果不為空
        if(!empty($order)){
            $this->options['order']='order by '.$order;
        }

        return $this;
    }

    public function limit($limit){
        //如果不為空
        if(!empty($order)){
            if(is_string($limit)){
                $this->options['limit']='limit '.$limit;
            }else if(is_array($limit)){
                $this->options['limit']='limit '.join(',',$limit);
            }
        }

        return $this;
    }

    public function select(){
        $sql='select %FIELD% from %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %limit%';
       
        //字串置換
        $sql=str_replace(['%FIELD%','%TABLE%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%limit%'],[$this->options['field'],$this->options['table'],$this->options['where'],$this->options['group'],$this->options['having'],$this->options['order'],$this->options['limit']],$sql);

        $this->sql=$sql;
     
    }

    public function query($sql){
        //清空Options
        $this->initOptions();
        
        $res=$this->pdo->prepare($sql);
        $result=$res->execute([]);
        $select_data=[];
        if(!$result){
			$this->pre($res->errorInfo());
		}else{
            while($row=$res->fetch(PDO::FETCH_ASSOC)){
                $select_data[]=$row;
            }
        }
        
        return $select_data;
    }

    public function insert($data){
        //判斷陣列值中是字串，要加雙引號
        $data=$this->parseValue($data);
        $keys=array_keys($data);
        $values=array_values($data);
        //var_dump($data);
        $sql='insert into %TABLE%  (%FIELD%) values (%VALUES%)';
        //字串置換
    
        $sql=str_replace(['%TABLE%','%FIELD%','%VALUES%'],[$this->options['table'],join(',',$keys),join(',',$values)],$sql);

        $this->sql=$sql;
        
        $insertId=$this->execute($sql,'insert');
        return $insertId;
    }

    public function delete(){
        $sql='delete from %TABLE%  %WHERE%';
        $sql=str_replace(['%TABLE%','%WHERE%'],[$this->options['table'],$this->options['where']],$sql);

        $this->$sql=$sql;
        $count=$this->execute($this->$sql,'delete');
		return $count;
    }    

    public function update($data){
        $sql='update %TABLE% set %VALUES% %WHERE%';
        $data=$this->parseValue($data);

        foreach($data as $key => $value){
            $newData[]=$key.'='.$value;
        }

        $sql=str_replace(['%TABLE%','%VALUES%','%WHERE%'],[$this->options['table'],join(',',$newData),$this->options['where']],$sql);

        $this->$sql=$sql;
        
        $count=$this->execute($this->$sql,'update');
		return $count;
    }

    public function execute($sql,$action){
        //清空Options
        $this->initOptions();

        $res=$this->pdo->prepare($sql);
		$result=$res->execute([]);
		if(!$result){
			$this->pre($sql);
			$this->pre($res->errorInfo());
        }
        
        switch($action){
            case 'insert':
                $insertId = $this->pdo->lastInsertId();
                return $insertId;
            break;

            case 'update':
                $count=$res->rowCount();
                return $count;
            break;

            case 'delete':
                $count=$res->rowCount();
                return $count;
            break;

        }

        
    }

    protected function is_valid_json( $raw_json ){
        return ( json_decode( $raw_json , true ) == NULL ) ? false : true ; // Yes! thats it.
    }

    //陣列值中是字串加雙引號
    protected function parseValue($data){
        foreach($data as $key => $value){
            if($this->is_valid_json($value)){
                $value="'".$value."'";
            }else{
                if(is_string($value)){
                    $value='"'.$value.'"';
                }
            }
            
            

            $newData[$key]=$value;
        }

        return $newData;
    }

    public function pre($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public function __get($name){
        if($name=='sql'){
            return $this->sql;
        }
    }    

    //銷毀
    public function __destruct(){
        $this->pdo=null;
    }

}



?>  
