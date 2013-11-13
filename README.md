TAAT (Estonian Academic Authentication and Authorization Infrastructure, http://taat.ee) authentication plugin for Moodle (http://moodle.org).

= REQUIREMENTS = 

SimpleSAMLphp needs to use other datastore then "phpsession": for example "memcache" or "sql".
"phpsession" can't be used, because Moodle wants to change session module ini settings and this is not possible when there is already active SimpleSAMLphp session.
