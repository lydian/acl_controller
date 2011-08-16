<?
abstract class User{
    var $account;
    var $roles=array();
    function __construct($account=""){
        // Call the Model constructor
        // $this->load->helper('url');
        $this->account = $account;

        $CI = & get_instance();
        if($this->account!=""){ 
            $CI->session->set_userdata('user', array("class"=>get_class($this), "data"=>serialize($this)));
        }

    }
    function getUsername(){
            return $this->account;
    }
    function getUserRole($namespace){
        if(array_key_exists($namespace, $this->roles))
            return $this->roles["$namespace"];
    }
    static abstract function authUser($account, $password, $params);
    #abstract function getUserRolie($account, $controller, $params);

}
?>
