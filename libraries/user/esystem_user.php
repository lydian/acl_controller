<?php
require("user.php");
class Esystem_user extends User{
    var $namespace;
    function __construct($account = "", $namespace=""){
        if($account!=""){
            $this->account = $account;
            $this->namespace = $namespace;
            $this->setEsystemRole($namespace);
            parent::__construct($account);
        }
    }
    static function authUser($account, $password, $namespace){
        $CI = & get_instance();
        $db = $CI->load->database('esystem',TRUE);
        $db->select("type, password")->from("{$namespace}_users")->where("username", $account);
        $query = $db->get();
        if($query->num_rows()<1){
            log_message('info', "User[$account] not Exists");
            return;
        }
        $result = $query->row();
        if($result->password != sha1("這是鹽巴!!".$password)){
            log_message('info', "User[$account] wrong password");
            return;
        }
        return new Esystem_user($account, $namespace);
    }
        
    function setEsystemRole($namespace){
        $CI = & get_instance();
        $db = $CI->load->database('esystem',TRUE);
        if(!$db->table_exists("{$namespace}_users"))
             return ;
        $db->select("`type`")->from("{$namespace}_users")->where("username", $this->account);
        $result = $db->get()->row();
        $this->roles[$namespace] = $result->type;
        return $this->roles[$namespace];
    }
}
?>
