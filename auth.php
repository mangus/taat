<?php

if (!defined('MOODLE_INTERNAL'))
    die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/lib/authlib.php');

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

        global $DB, $CFG, $SESSION;

        // TODO: make config for this placement
        require_once($CFG->dirroot . '/../simplesamlphp/lib/_autoload.php');

        $auth = new SimpleSAML_Auth_Simple('hitsa-moodle-sp'); // TODO: make config for this
        $auth->requireAuth(array('saml:idp' => 'https://reos.taat.edu.ee/saml2/idp/metadata.php'));

        $attributes = $auth->getAttributes();
        $idparts = explode('ee:EID:', $attributes['schacPersonalUniqueID'][0]);
        $idnumber = $idparts[1];

        $conditions = array('idnumber' => $idnumber);
        $usertologin = $DB->get_record('user', $conditions, $fields='*');
        if ($usertologin !== false) {
            $USER = complete_user_login($usertologin);
            if (optional_param('password_recovery', false, PARAM_BOOL))
                $SESSION->wantsurl = $CFG->wwwroot . '/login/change_password.php';
            $goto = isset($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;
            redirect($goto);
        } else
            $goto = $CFG->wwwroot . '/login/?no_such_idnumber=1';
        redirect($goto);
    }

    /** Shows nice error messages to user */
    function loginpage_hook() {
        global $errormsg;
        if (optional_param('no_such_idnumber', false, PARAM_BOOL)) {
            $errormsg = get_string('no_such_idnumber', 'auth_taat');
        }
    }

    function perlogout_hook() {
        global $CFG;
        $auth = new SimpleSAML_Auth_Simple('hitsa-moodle-sp'); // TODO: make config for this
        $auth->logout($CFG->wwwroot);
    }

}


