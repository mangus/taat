<?php

if (!defined('MOODLE_INTERNAL'))
    die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/lib/authlib.php');

// TODO: make config for this placement
require_once($CFG->dirroot . '/../simplesamlphp/lib/_autoload.php');

class auth_plugin_taat extends auth_plugin_base {

    /** Constructor */
    function auth_plugin_taat() {
        $this->authtype = 'taat';
    }

    /** Login is going through file auth/taat/login.php instead of usual login form */
    function user_login($username, $password) {
        return false;
    }

    /** Real authentication here */
    function authenticate_with_taat() {

        $auth = new SimpleSAML_Auth_Simple('hitsa-moodle-sp'); // TODO: make config for this
        $auth->requireAuth();
        $attributes = $auth->getAttributes();
        print_r($attributes); die('test');

        
        /*
        global $DB, $CFG, $SESSION;
        if ($this->id_card_inserted()) {
            $conditions = array('idnumber' => $this->get_id_number());
            $usertologin = $DB->get_record('user', $conditions, $fields='*');
            if ($usertologin !== false) {
                $USER = complete_user_login($usertologin);
                if (optional_param('password_recovery', false, PARAM_BOOL))
                    $SESSION->wantsurl = $CFG->wwwroot . '/login/change_password.php';
                $goto = isset($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;
                redirect($goto);
            } else
                $goto = $CFG->wwwroot . '/login/?no_user_with_id=1';
        } else
            $goto = $CFG->wwwroot . '/login/?no_id_card_data=1';
        redirect($goto);
        */
    }

}

