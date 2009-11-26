<?php
/**
 * Main page generation functions and what
 *
 * @package code_common
 * @author josh04
 */
class code_page_generation {
    public $settings = array();
    public $player;
    public $section = "";
    public $page = "";
    public $lang;
    public $skin;

   /**
    * tracks down which bloody skin files to use
    *
    * @param code_common $common page that's calling
    * @return code_page_generation skin object
    */
    public function load_core($common) {
        $this->settings =& $common->settings;
        $this->player =& $common->player;
        $this->page = $common->page;
        $this->section = $common->section;
        $this->lang =& $common->lang;
        return $this;
    }

   /**
    * makes the skin object
    *
    * warning: will only set the code_common $skin property once. if you're
    * RE-defining which skin you'll be using for some reason, you'll need to
    * assign it yourself
    *
    * @param string $skin_name skin name
    * @param string $override special skin name
    * @return skin_common tha skin!
    */
    public function make_skin($skin_name = "", $override = "") {
        $alternative_skin = "";

        // Is there a default skin in the settings?
        if ($this->settings->get['default_skin']) {
            $alternative_skin = $this->settings->get['default_skin'];
        }

        // Does the player have a personal skin set?
        if ($this->player->skin) {
            $alternative_skin = $this->player->skin;
        }

        // Does the module specify a skin which must be used?
        if ($override) {
            $alternative_skin = $override;
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

            $skin = new $skin_class_name;
        } else {
            // Load the default skin_common, no extras.
            if ($alternative_skin) {
                $class_name = $alternative_skin."_skin_common";
                if (class_exists($class_name)) {
                    $skin = new $class_name;
                } else {
                    $skin = new skin_common;
                }
            } else {
                $skin = new skin_common;
            }
        }

        // Some quick lang naughtiness.
        $skin->lang =& $this->lang;

        $this->skin =& $skin;

        if (!isset($common->skin)) {
            $common->skin =& $skin;
        }

        return $skin;
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

        if (isset($this->settings->get['name'])) {
            $site_name = $this->settings->get['name'];
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
    * The generic "oh no" error page.
    *
    * @param string $error error text
    */
    public function error_page($error) {

        if (!$this->skin) {
            $this->make_skin();
        }

        if (isset($this->settings->get['name'])) {
            $site_name = $this->settings->get['name'];
        } else {
            $site_name = "Quest";
        }

        $output = $this->skin->start_header("Error", $site_name, "default.css");

        $output .= $this->skin->error_page($error);

        $output .= $this->skin->footer();

        print $output;
        exit;
    }

}
?>