TAAT (Estonian Academic Authentication and Authorization Infrastructure, http://taat.ee) authentication plugin for Moodle (http://moodle.org).

REQUIREMENTS 

SimpleSAMLphp needs to use other datastore then "phpsession": for example "memcache" or "sql".
"phpsession" can't be used, because Moodle wants to change session module ini settings and this is not possible when there is already active SimpleSAMLphp session.

SETUP

 * Install SimpleSAMLphp as described in TAAT setup: http://taat.edu.ee/main/wp-content/uploads/sp-juhend.pdf
 * Copy the plugin files to /auth/taat directory
 * Open the plugin settings page (https://yourmoodle.com/admin/auth_config.php?auth=taat) and set SimpleSAMLphp path and SP name.
 * When everything is configured correctly, you are ready to login with TAAT!
