<?php
namespace myDatabaseUtils;
use Exception;
use PDO;
use PDOException;
class myDatabaseUtils{
        private $uname;
        private $passw;
        private $dbnam;
        private $table;
        private $hostn;
        private $connt;
        private $dbtyp;
        
        function __construct()
        {
            $this->hostn = (isset($GLOBALS['host']) ? $GLOBALS['host'] : '');
            $this->uname = (isset($GLOBALS['username']) ? $GLOBALS['username'] : '');
            $this->passw = (isset($GLOBALS['password']) ? $GLOBALS['password'] : '');
            $this->dbnam = (isset($GLOBALS['db']) ? $GLOBALS['db'] : '');
            $this->table = (isset($GLOBALS['table']) ? $GLOBALS['table'] : '');
            $this->dbtyp = (isset($GLOBALS['type']) ? $GLOBALS['type'] : '');
        }

        function connect(){
            if($this->hostn == '')
                throw new Exception('Variable host is not exist.');
            if($this->dbnam == '')
                throw new Exception('Variable db is not exist.');
            if($this->uname == '')
                throw new Exception('Variable Username is not exist.');

            try {
                $this->connt = new PDO("$this->dbtyp:host=$this->hostn;dbname=$this->dbnam", $this->uname, $this->passw);
                $this->connt->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            return $this->connt;
        }

        function disconnect(){
            $this->connt = null;
        }

        function DbType($n = null){
            if($n != null){
                $this->dbtyp = $n;
                return $this;
            }
            return $this->dbtyp;
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
            if($n != null || $n == ''){
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
                if(count($order) == 2){
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
                    if(count($order) == 1){
                        $order = $order[0];
                    }else{
                        $order = '';
                    }
                }
                $group = (isset($params['groups']) ? $params['groups'] : '');
                $sql = "SELECT $field FROM $this->table ".($where != "" ? "WHERE " . $where : '') . ($group != '' ? ' GROUP BY ' . $group : '') . ($order != '' ? " ORDER BY $order $prefi " : '');
            }else{
                $sql = $params['sql'];
            }
            $value = (isset($params['value']) ? $params['value'] : null);
            $sth = $this->connt->prepare($sql);
            $sth->execute($value);
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $v = $sth->fetchAll();
            return (count($v) > 0 ? $v : false);
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
