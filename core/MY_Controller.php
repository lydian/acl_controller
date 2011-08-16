<?
class MY_Controller extends CI_Controller{
    var $_acl_auth = array();
    var $_acl_rule = array();
    var $_acl_forward = array();
    var $_user;
    
    var $namespace;
    function __construct(){
        parent::__construct();
        $params = $this->router->uri->rsegments;
        $method = array_shift($params);
        //$this->_remap();
    }
    function _remap($method, $params=array()){
        $this->load->helper("url");
        if($this->router->class !="auth" && $method!="login"){
            $this->session->unset_userdata("from");
        }

        if(isset($this->_acl_forward[$method])){
            foreach($this->_acl_forward[$method] as $rule){
                if($this->_is_matched($this->get_user(), $rule["role"])){
                    return $this->_goto($rule["target"], $params);
                }
            }
        }
        if(!$this->has_permission($this->router->class, $method)){
            if($this->session->userdata("user")){
                show_error('You don\'t have permission to access this page');
            }
            else{
                $this->session->set_userdata("from", $this->router->uri->uri_string);
                $this->session->set_userdata("_acl_auth", $this->_acl_auth);
                redirect("auth/login");
            }

        }

        if(method_exists($this->router->class, $method) && !(strpos($method, "_") === 0)){
            return $this->_goto($method, $params);
        }
        show_404('PAGE');
        
    }
    function _goto($method, $params){
       /*
        if(method_exists($this->router->class, "_remap") ){
            call_user_func_array(array($this->router->class, "_remap"), array($method, $params));
        }else{
        */
            call_user_func_array(array($this->router->class, $method), $params);
        //}

        if(isset($this->title) && $this->title!="")
            $this->theme->write_component("title", $this->title);
        $this->theme->render();
        return;
    }

    function get_user(){
        if(isset($this->_user)) 
            return $this->_user;

        if($this->session->userdata("user")){
            $user = $this->session->userdata("user");
            $this->load->library("user/{$user["class"]}");
            $user = unserialize($user["data"]);
            $this->_user = $user;
            return $user;
        }
    }

    function add_auth_method($method, $params=""){
        if($params!="" || is_array($params)){
            $this->_acl_auth[] = array($method, $params);
        }
        else{
            $this->_acl_auth[] = $method;
        }
    }

    function add_acl_rule($method, $users=array()){
        $this->_acl_rule[$method] =  $users;
    }
    function add_acl_forward($method, $role, $target){
        $this->_acl_forward[$method][] =  array("role"=>$role, "target"=>$target);
    }

    function has_permission($controller, $method){
        if(isset($this->_acl_rule))
            $acl = $this->_acl_rule;
        
        if(!$acl || !(isset($acl[$method]) || isset($acl["*"])))
            return True;

        
        $method = (!isset($acl[$method]))?"*":$method;
        $user= $this->get_user(); 
        if($user && $this->_is_matched($user, $acl[$method])){
            return True;
        } 
    }
    function _is_matched($user, $acl_group){
        $role = ($user)?"@".$user->getUserRole($this->namespace):"";
        if(!(array_search($role, $acl_group)===False)){
            return True;
        }
        $username = ($user)?$user->getUsername():"";
        if(!(array_search($username, $acl_group)===False)){
            return True;
        }
    }
}
?>
