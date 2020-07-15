<?php
    namespace myfunc;

    class Uitls{

    }

namespace myfunc\db;
use Exception;
use PDO;
use PDOException;
use find;
class Database{
        private $uname;
        private $passw;
        private $dbnam;
        private $table;
        private $hostn;
        private $connt;
        function __construct()
        {
            $this->hostn = (isset($GLOBALS['host']) ? $GLOBALS['host'] : '');
            $this->uname = (isset($GLOBALS['username']) ? $GLOBALS['username'] : '');
            $this->passw = (isset($GLOBALS['password']) ? $GLOBALS['password'] : '');
            $this->dbnam = (isset($GLOBALS['db']) ? $GLOBALS['db'] : '');
            $this->table = (isset($GLOBALS['table']) ? $GLOBALS['table'] : '');
        }

        function connect(){
            if($this->hostn == '')
                throw new Exception('Variable host is not exist.');
            if($this->dbnam == '')
                throw new Exception('Variable db is not exist.');
            if($this->uname == '')
                throw new Exception('Variable Username is not exist.');

            try {
                $this->connt = new PDO("mysql:host=$this->hostn;dbname=$this->dbnam", $this->uname, $this->passw);
                $this->connt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            return $this->connt;
        }

        function HostName($n = null){
            if($n != null){
                $this->hostn = $n;
                return $this;
            }
            return $this->hostn;
        }

        function DbName($n = null){
            if($n != null){
                $this->dbnam = $n;
                return $this;
            }   
            return $this->dbnam;
        }

        function Username($n = null){
            if($n != null){
                $this->uname = $n;
                return $this;
            }
            return $this->uname;
        }

        function Password($n = null){
            if($n != null){
                $this->passw = $n;
                return $this;
            }
            return $this->passw;
        }

        function TableName($n = null){
            if($n != null){
                $this->table = $n;
                return $this;
            }
            return $this->table;
        }

        function get(array $params){
            if(!isset($params['sql'])){
                if($this->table == '')
                    if(!isset($params['table']))
                        throw new Exception('Variable table is not exist.');
                    else
                        $this->table = $params['table'];

                $field = (isset($params['fields']) && $params['fields'] != '' ? $params['fields'] : '*');
                $where = (isset($params['wheres']) && $params['wheres'] != '' ? $params['wheres'] : '');
                $group = (isset($params['groups']) && $params['groups'] != '' ? $params['groups'] : '');
                $order = (isset($params['orders']) && $params['orders'] != '' ? explode('::', $params['orders']) : []);
                if(count($order) > 0){
                    if(count($order) == 1)
                        $prefi = 'ASC';
                    if(count($order) == 2){
                        if($order[1] == '>')
                            $prefi = "DESC";
                        if($order[1] == '<')
                            $prefi = "ASC";
                    }
                    $order = $order[0];
                }else{
                    $order = $order[0];
                }
                $group = (isset($params['groups']) ? $params['groups'] : '');
                $sql = "SELECT $field FROM $this->table ".($where != "" ? "WHERE " . $where : '') . ($group != '' ? ' GROUP BY ' . $group : '') . " ORDER BY $order $prefi ";
            }else{
                $sql = $params['sql'];
            }
            $value = (isset($params['value']) ? $params['value'] : null);
            $sth = $this->connt->prepare($sql);
            $sth->execute($value);
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $v = $sth->fetchAll();
            return (count($v) > 1 ? $v : count($v) == 1 ? $v[0] : false);
        }
        function post($sql, $value){
            try{
                $sth = $this->connt->prepare($sql);
                $r = $sth->execute($value);
                return ($r ? $this->connt->lastInsertId() : false);
            }catch(Exception $e){
                return false;
            }
        }
        function put($sql, $value){
            try{
                $sth = $this->connt->prepare($sql);
                $r = $sth->execute($value);
                return ($r ? $sth->rowCount() : false);
            }catch(Exception $e){
                return false;
            }
        }
        function delete($sql, $value){
            try{
                $sth = $this->connt->prepare($sql);
                $r = $sth->execute($value);
                return ($r ? $sth->rowCount() : false);
            }catch(Exception $e){
                return false;
            }
        }

    }


    //$r = new Database($host = 'localhost', $username = 'root', $password = '', $db = 'godigitexample');
    $r = new Database();
    $r->HostName('localhost')->DbName('godigitexample')->Username('root');
    $r->connect();
    $id = $r->put("INSERT INTO tb_customer (customer_id, first_name, last_name, tel_no) VALUES (?,?,?,?)" , [1,'voraph', 'rtd', '080000000']);
    echo $id;
    $v = $r->get(['sql' => 'SELECT * FROM tb_customer']);
    print_r($v);
    //$r->get($groups = "date", $table = 'tb_customer', $fields = '', $wheres = "first_name = ?", $orders = "customer_id::<", $value = array(''));
    
    //print_r($r->get();