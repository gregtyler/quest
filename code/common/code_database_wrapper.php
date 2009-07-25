<?php
/**
 * The database wrapper is a singleton class (I think!), a class with only one
 * possible instance shared wherever it is used. When you run get_db for the
 * first time (with a $config passed), it calls up ADODB (or whatever) and sets
 * up the database. Every time after that, it just returns the previous database
 * connection. This saves us from all that tedious =& $this->db stuff, you can
 * just put a $this->db = code_...() into __construct() and be done with it.
 * code_bootstrap connects to the db to get the page list, so you can count on
 * it being there for most of the code. If the db isn't set up, running this on
 * it's own will silently error out, and make_db() can be run at any point in
 * the future.
 *
 * @author josh04
 * @package code_common
 */
class code_database_wrapper {
    private static $instance;
    public $db;

   /**
    * constructs shiz.
    *
    * @global int $ADODB_QUOTE_FIELDNAMES adodb setting
    * @param array $config database settings
    */
    private function __construct($config) {
        global $ADODB_QUOTE_FIELDNAMES;
        // Set up the Database
        $this->db = &ADONewConnection('mysqli'); //Get our database object.
        //$this->db->debug = true;
        ob_start(); // Do not error if the database isn't there.
        $status = $this->db->Connect(     $config['server'],
                                          $config['db_username'],
                                          $config['db_password'],
                                          $config['database']     );
        ob_end_clean();

        $ADODB_QUOTE_FIELDNAMES = 1;
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC); //Set to fetch associative arrays
    }

   /**
    * gets the current database object
    *
    * @param array $config database settings
    * @return object database
    */
    public static function &get_db($config = array()) {
        if (!self::$instance) {
            self::$instance = new code_database_wrapper($config);
        }

        return self::$instance->db;
    }

}
?>
