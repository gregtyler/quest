<?php
/**
 * Base class for modular design
 *
 * code_common is the base class for 90% of the other classes in the project -
 * it only handles pretty much universal concepts such as database connections,
 * the skin, the player etc - shouldn't be anything specific to one part.
 *
 * (TODO) general skin functions are kept in here, which is probably wrong.
 * nowhere else to keep them though :(
 *
 * @package code_common
 * @author josh04
 */

class code_common {

   /**
    * The database object. Runs queries.
    *
    * @var QuestDB
    */
    public $db;

   /**
    * The current set of skin templates.
    *
    * @var skin_common
    */
    public $skin;

   /**
    * The current player object.
    *
    * @var code_player
    */
    public $player;

   /**
    * The databate config details, from config.php
    *
    * @var array
    */
    public $config;

   /**
    * The cronometer!
    *
    * @var code_cron
    */
    public $cron;

   /**
    * The current section.
    *
    * @var string
    */
    public $section;

   /**
    * The current page.
    *
    * @var string
    */
    public $page;

   /**
    * The settings array from the database.
    *
    * @var array
    */
    public $settings = array();

   /**
    * The pages array from the database.
    *
    * @var array
    */
    public $pages = array();

   /**
    * The class stored in this variable will be loaded by make_player as the player object.
    *
    * @var string
    */
    public $player_class = "code_player";

   /**
    * Default skin
    *
    * @var string
    */
    public $override_skin = "";

   /**
    * Ajax mode disables skin dressing
    *
    * @var bool
    */
    public $ajax_mode = false;

   /**
    * The common constructor for every class which extends code_common.
    *
    * Accepts and sets the section name and the page name, and an optional
    * config array. Also invokes code_database_wrapper to get the database
    * object.
    * 
    * @param string $section name of the site chunk we're in
    * @param string $page name of our particular page
    * @param string $config config values
    */
    public function __construct($section = "", $page = "", $config = array()) {
        $this->section = $section;
        $this->page = $page;
        $this->config = $config;
        $this->db = code_database_wrapper::get_db($this->config);
    }

   /**
    * The generic "oh no" error page.
    *
    * @param string $error error text
    */
    public function error_page($error) {
        
        if (!$this->skin) {
            $this->make_skin();
        }

        if (isset($this->settings['name'])) {
            $site_name = $this->settings['name'];
        } else {
            $site_name = "Quest";
        }

        $output = $this->skin->start_header("Error", $site_name, "default.css");

        $output .= $this->skin->error_page($error);

        $output .= $this->skin->footer();

        print $output;
        exit;
    }

   /**
    * Builds the page header.
    *
    * (TODO) Contains half-baked skin changing code.
    *
    * @return string html
    */
    public function start_header() {
        
        $css = $this->player->skin;

        if(!isset($this->player->skin)) {
            $css = "default.css";
        }

        if (isset($this->settings['name'])) {
            $site_name = $this->settings['name'];
        } else {
            $site_name = "Quest";
        }

        if ($this->skin->title) {
            $page_title = $this->skin->title($site_name);
        } else {
            $page_title = $site_name;
        }

        $start_header = $this->skin->start_header($page_title, $site_name, "default.css");
        return $start_header;
    }

   /**
    * Makes the player object.
    *
    * Makes $player a code_player object.
    */
    public function make_player() {
        session_start();
        // (DONE) player shirt and percentage code has moved
        // Get our player object
        if ($this->player_class != "code_player") {
            require_once("code/player/".$this->player_class.".php");
        }

        $this->player = new $this->player_class($this->settings, $this->config);
        $this->player->page = $this->page;
        //Is our player a member, or a guest?
        $allowed = $this->player->make_player();
        if (!$allowed) {
            $this->error_page($this->lang->page_not_exist);
        }
    }

   /**
    * Constructs the settings table.
    *
    * A simple database call.
    */
    public function make_settings() {
        $settings_query = $this->db->execute("SELECT * FROM `settings`");
        while ($setting = $settings_query->fetchrow()) {
            $this->settings[$setting['name']] = $setting['value'];
        }
    }

   /**
    * Manual database connection function.
    *
    * Connects to the database, if for whatever reason this failed when the
    * class was constructed.
    *
    * @param bool $skipError if set, the error message isn't displayed if the connection fails
    * @return void
    */
    public function make_db($skipError = false) {
        $this->db =& code_database_wrapper::get_db($this->config);

        if (!$this->db->IsConnected() && !$skipError) {
            if (!$this->skin) {
                $this->make_skin();
            }
            $this->error_page($this->lang->failed_to_connect);
        }
    }

   /**
    * Makes skin template object.
    *
    * If the player has a skin, use that. needs skin selection code to be
    * useful, tbqh.
    * (DONE) skin code
    *
    * @param string $skin_name skin name
    */
    public function make_skin($skin_name = "") {
        $alternative_skin = "";

        // Is there a default skin in the settings?
        if ($this->settings['default_skin']) {
            $alternative_skin = $this->settings['default_skin'];
        }

        // Does the player have a personal skin set?
        if ($this->player->skin) {
            $alternative_skin = $this->player->skin;
        }

        // Does the module specify a skin which must be used?
        if ($this->override_skin) {
            $alternative_skin = $this->override_skin;
        }

        if ($alternative_skin) {
            // If there is an alternate skin_common to be load, do so.
            if (file_exists("skin/".$alternative_skin."/common/".$alternative_skin."_skin_common.php")) {
                        require_once("skin/".$alternative_skin."/common/".$alternative_skin."_skin_common.php");
            }
            // If there is a alternate, section-specific skin_common to load, do so.
            if (file_exists("skin/".$alternative_skin."/".$this->section."/_skin_".$this->section.".php")) {
                        require_once("skin/".$alternative_skin."/".$this->section."/_skin_".$this->section.".php");
            } else if (file_exists("skin/".$this->section."/_skin_".$this->section.".php")) {
                // If there's a section-specific skin_common NOT of a custom skin, load that.
                require_once("skin/".$this->section."/_skin_".$this->section.".php");
            }
        } else if (file_exists("skin/".$this->section."/_skin_".$this->section.".php")) { // conflicting class names
            // ditto the above
            require_once("skin/".$this->section."/_skin_".$this->section.".php");
        }
        if ($skin_name) {
            // Load the main event, as it were. The default requested skin file.
            require_once("skin/".$this->section."/".$skin_name.".php");
            if ($alternative_skin) {
                // If there's an alternate skin version, grab that too and change the class name to be loaded.
                if (file_exists("skin/".$alternative_skin."/".$this->section."/".$alternative_skin."_".$skin_name.".php")) {
                    require_once("skin/".$alternative_skin."/".$this->section."/".$alternative_skin."_".$skin_name.".php");
                    $skin_class_name = $alternative_skin."_".$skin_name;
                } else {
                    $skin_class_name = $skin_name;
                }
            } else {
                $skin_class_name = $skin_name;
            }

            $this->skin = new $skin_class_name;
        } else {
            // Load the default skin_common, no extras.
            if ($alternative_skin) {
                $class_name = $alternative_skin."_skin_common";
                if (class_exists($class_name)) {
                    $this->skin = new $class_name;
                } else {
                    $this->skin = new skin_common;
                }
            } else {
                $this->skin = new skin_common;
            }
        }

        // Some quick lang naughtiness.
        $this->skin->lang =& $this->lang;
    }

   /**
    * Gets our language string constants
    */
    public function make_default_lang() {
        require_once("skin/lang/en/lang_error.php"); // (TODO) language string abstraction
        $this->lang = new lang_error;
    }

   /**
    * Gets any customisation
    *
    * (TODO) Player language choice?
    */
    public function make_extra_lang() {
        $alternative_skin = "";

        if ($this->settings['default_skin']) {
            $alternative_skin = $this->settings['default_skin'];
        }

        if ($this->player->skin) {
            $alternative_skin = $this->player->skin;
        }

        if ($this->override_skin) {
            $alternative_skin = $this->override_skin;
        }

        if ($alternative_skin) {
            if (file_exists("skin/".$alternative_skin."/lang/en/".$alternative_skin."_lang_error.php")) {
               require_once("skin/".$alternative_skin."/lang/en/".$alternative_skin."_lang_error.php");
               $class_name = $alternative_skin."_lang_error";
               $this->lang = new $class_name;
            }
        }
        $lang_query = $this->db->execute("SELECT * FROM `lang`");
        while ($lang = $lang_query->fetchrow()) {
            $name = $lang['name'];
            if (isset($this->lang->$name)) {
                $this->lang->$name = $lang['override'];
            }
        }
    }

   /**
    * This happy function runs the code which assembles the side bar.
    *
    * Calls code_menu, lets it do all the work.
    *
    * @return string html
    */
    public function make_menu() {
        require_once("code/common/code_menu.php");
        $menu = new code_menu($this->player, $this->section, $this->page, $this->pages, $this->settings);
        $menu->make_skin('skin_menu');
        $make_menu = $menu->make_menu();
        return $make_menu;
    }

   /**
    * Cronometer!
    *
    * Is it that time again? Makes code_cron and runs the crons which need
    * running.
    *
    */
    public function cron() {
        require_once('code/common/code_cron.php');
        $this->cron = new code_cron;
        $this->cron->update();
    }

   /**
    * Groups together all the standard constructing functions.
    *
    * @param string $skin_name name of skin file to load - if left blank, loads skin_common (not recommended)
    */
    public function initiate($skin_name = "") {
        $this->make_default_lang();
        $this->make_db();
        $this->make_settings();
        $this->cron();
        $this->make_player();
        $this->make_extra_lang();
        $this->make_skin($skin_name);
    }

   /**
    * Main page function.
    *
    * Pieces the site together. Intended to be overriden by child class to
    * generate $page.
    *
    * @param string $page contains the html intended to go between the menu and the bottom.
    */
    public function finish_page($page) {
        if (!$this->ajax_mode) {
            $output = $this->start_header();

            $output .= $this->make_menu();

            if ($this->settings['database_report_error'] && $this->db->_output_buffer) {
                $page = $this->skin->error_box($this->db->_output_buffer).$page;
            }
            $output .= $this->skin->glue($page);
            $output .= $this->skin->footer();
        } else {
            $output = $page;
        }
        return $output;
    }

    /**
     * parses bbcode to return html
     * (TODO) preg_replace is sloooooow
     *
     * @param string $code bbcode
     * @param boolean $full whether to fully parse
     * @return string html
     */
    public function bbparse($code, $full=false) {
        $code = htmlentities($code, ENT_QUOTES, 'utf-8', false); //things should be htmlentitised _before_ they go into the DB - you shouldn't do it twice
        if ($full) {
            $code = nl2br($code);
        }

        preg_match_all("/\[code\](.*?)\[\/code\]/is", $code, $matches, PREG_SET_ORDER);
        foreach($matches as $match) {
            $match[1] = str_replace("[", "&#91;", $match[1]);
            $code = str_replace($match[0], "<pre>" . $match[1] . "</pre>", $code);
        }

        $match = array (
                "/\[b\](.*?)\[\/b\]/is",
                "/\[i\](.*?)\[\/i\]/is",
                "/\[u\](.*?)\[\/u\]/is",
                "/\[s\](.*?)\[\/s\]/is",
                "/\[url\](.*?)\[\/url\]/is",
                "/\[url=(.*?)\](.*?)\[\/url\]/is",
                "/\[img\](.*?)\[\/img\]/is",
                "/\[colo(u)?r=([a-z]*?|#?[A-Fa-f0-9]*){3,6}\](.*?)\[\/colo(u)?r\]/is",
                );
        $replace = array (
                "<strong>$1</strong>",
                "<em>$1</em>",
                "<ins>$1</ins>",
                "<del>$1</del>",
                "<a href='$1' target='_blank'>$1</a>",
                "<a href='$1' target='_blank'>$2</a>",
                "<img src='$1' alt='[image]' />",
                "<span style='color:$2;'>$3</span>",
                );
        $code = preg_replace($match, $replace, $code);
        while($this->bbparse_quote($code)) continue; // a fake loop that drills through
        return $code;
    }
    /**
     * parses quotes in bbcode (looped for multiple quotes)
     *
     * @param string $code bbcode
     * @param boolean $full whether to fully parse
     * @return string html
     */
    public function bbparse_quote(&$code) {
        $match = array (
                "/\[quote=(.+?)\](.*?)\[\/quote\]/is",
                "/\[quote=?\](.*?)\[\/quote\]/is",
                );
        $replace = array (
                "<div class='quote-head'>Quote ($1)</div><div class='quote'>$2</div>",
                "<div class='quote-head'>Quote</div><div class='quote'>$1</div>",
                );
        $code = preg_replace($match, $replace, $code, -1, $count);
        return $count;
}

    /**
     * Updates a specific setting.
     *
     * (TODO) code_settings?
     *
     * @param string setting name
     * @param string setting value
     * @return boolean success
     */
    public function setting_update($name, $value) {
        // If no query needs to be executed, the update is complete!
        $setting_query = true;
        // If they're arrays, we're going cyclic
        if (is_array($name) && is_array($value)) {
            if (count($name)!=count($value)) {
                return false;
            }
            foreach ($name as $setting_index => $setting_name) {
                if ($this->settings[$setting_name] == $value[$setting_index]) {
                    continue;
                }
                $setting_query = $this->db->execute("UPDATE `settings` SET `value`=? WHERE `name`=?",array($value[$setting_index],$setting_name));

                if (!$setting_query) {
                    return false;
                }
                $this->settings[$setting_name] = $value[$setting_index];
            }
            return true;
        } else {
            $setting_query = $this->db->execute("UPDATE `settings` SET `value`=? WHERE `name`=?", array($value, $name));
            if ($setting_query)  {
                $this->settings[$name] = $value;
                return $true;
            }
        }
        return false; // This will only occur if it screws up. I expect.
    }

   /**
    * paginates. move to code_common?
    *
    * @param int $max total number of thingies
    * @param int $current current offset
    * @param int $per_page number per page
    * @return string html
    */
    public function paginate($max, $current, $per_page) {
        $num_pages = intval($max / $per_page);

        if ($max % $per_page) {
            $num_pages++; // plus one if over a round number
        }

        $current_page = intval($current/$per_page);

        $current_html = $this->skin->paginate_current($current_page, $current, $this->section, $this->page);

        $pagination = array( ($current_page - 2) => ($current - 2*$per_page),
            ($current_page - 1) => ($current - $per_page),
            ($current_page + 1) => ($current + $per_page),
            ($current_page + 2) => ($current + 2*$per_page));

        foreach ($pagination as $page_number => $page_start) {
            if ($page_number >= 0 && $num_pages > $page_number) {
                $paginate_links[$page_number] = $this->skin->paginate_link($page_number, $page_start, $this->section, $this->page);
            }
        }

        $paginate_links[$current_page] = $current_html;
        ksort($paginate_links);

        foreach ($paginate_links as $link) {
            $paginate .= $link;
        }

        $paginate_final = $this->skin->paginate_wrap($paginate);
        return $paginate_final;

    }

   /**
    * performs a hook action
    *
    * @param string $hook name of the hook to be executed
    */
    public function do_hook($hook) {
        global $hooks;

        if(!isset($hooks[$hook])) return false;
        $mod = $hooks[$hook][0];
        $file = $hooks[$hook][1];
        $function = $hooks[$hook][2];

        $include_path = "././hooks/".$mod."/".$file;

        if(!file_exists($include_path)) return;

        // So we don't include anything that slipped outside the function into the page
        ob_start();
            include($include_path);
        ob_end_clean();

        return $function($this);
    }

}

?>
