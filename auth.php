<?php

if (!defined('MOODLE_INTERNAL'))
    die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/lib/authlib.php');
require_once($CFG->dirroot.'/lib/adminlib.php');

class auth_plugin_taat extends auth_plugin_base {

    /** Constructor */
    function auth_plugin_taat() {
        $this->authtype = 'taat';
        $this->get_settings();
        $autoloadfile = $this->settings['simplesamlplace']->get_setting() . '/lib/_autoload.php';
        if (file_exists($autoloadfile))
            require_once($autoloadfile);
    }

    /** Login is going through file auth/taat/login.php instead of usual login form */
    function user_login($username, $password) {
        return false;
    }

    /** Real authentication here */
    function authenticate_with_taat() {

        global $DB, $CFG, $SESSION;

        $auth = new SimpleSAML_Auth_Simple($this->settings['simplesamlspname']->get_setting());
        $auth->requireAuth(array('saml:idp' => 'https://reos.taat.edu.ee/saml2/idp/metadata.php'));

        $attributes = $auth->getAttributes();
        $idparts = explode('ee:EID:', $attributes['schacPersonalUniqueID'][0]);
        $idnumber = trim($idparts[1]);

        $conditions = array('idnumber' => $idnumber);
        $usertologin = $DB->get_record('user', $conditions, $fields='*');

        if ($usertologin !== false) {
            $this->check_for_not_allowed_roles($usertologin);
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
            $errormsg = get_string('nosuchidnumber', 'auth_taat');
        } else if (optional_param('nositeadminlogin', false, PARAM_BOOL)) {
            $errormsg = get_string('no_site_admin_login', 'auth_taat');
        } else if (optional_param('not_allowed_to_login', false, PARAM_BOOL)) {
            $errormsg = get_string('notallowedtologin', 'auth_taat');
        }
    }

    function logoutpage_hook() {
        global $CFG;
        require_logout();
        $auth = new SimpleSAML_Auth_Simple($this->settings['simplesamlspname']->get_setting());
        if ($auth->isAuthenticated())
            $auth->logout($CFG->wwwroot);
    }

    function config_form($config, $err, $user_fields) {
        foreach ($this->settings as $name => $setting) {
            if ($name == 'notallowedtologin')
                $setting->load_choices();
            echo $setting->output_html($setting->get_setting());
        }
    }

    function process_config($config) {
        $this->settings['simplesamlplace']->write_setting($config->s__simplesamlplace);
        $this->settings['simplesamlspname']->write_setting($config->s__simplesamlspname);
        $this->settings['notallowedtologin']->write_setting($config->s__notallowedtologin);
    }

    private function get_settings() {
        $context = context_system::instance();

        $settings['simplesamlplace'] = new admin_setting_configfile('simplesamlplace', new lang_string('simplesamlplace', 'auth_taat'), '', '');
        $settings['simplesamlspname'] = new admin_setting_configtext('simplesamlspname', new lang_string('simplesamlspname', 'auth_taat'), '', '');

        $roles = get_assignable_roles($context);
        $roles[0] = new lang_string('siteadministrators', 'role');
        $settings['notallowedtologin'] =
            new admin_setting_configmultiselect('notallowedtologin', new lang_string('notallowedtologin', 'auth_taat'),
                   new lang_string('notallowedtologindescription', 'auth_taat'), array(),
                       $roles);

        $this->settings = $settings;
    }

    private function check_for_not_allowed_roles($usertologin) {
        global $DB, $CFG;
        $this->settings['notallowedtologin']->load_choices();
        foreach ($this->settings['notallowedtologin']->get_setting() as $roleid) {
            if (0 == $roleid) { // Site administrator check
                foreach (explode(',', $CFG->siteadmins) as $admin) {
                    if ((int)$admin == $usertologin->id) {
                        $goto = $CFG->wwwroot . '/login/?no_site_admin_login=1';
                        break 2;
                    }
                }
            } else {
                if ($DB->count_records('role_assignments', array('roleid'=>$roleid, 'userid' => $usertologin->id))) {
                    $goto = $CFG->wwwroot . '/login/?not_allowed_to_login=1';
                    break;
                }
            }
        }
        if (isset($goto))
            redirect($goto);
    }

}


