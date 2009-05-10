<?php
/**
 * code_login.class.php
 *
 * logs players in and out
 * @author josh04
 * @package code_public
 */
class code_login extends code_common {

   /**
    * class override. calls parents, sends kids home.
    *
    * @return string html
    */
    public function construct_page() {
        $this->initiate("skin_index");
        if($this->player->is_member) {
            $code_login = $this->log_out();
        } else {
            $code_login = $this->register_or_login();
        }

        parent::construct_page($code_login);
    }

   /**
    * logs the current user out. Destroys, session, backdates cookie.
    *
    * @return string html
    */
    public function log_out() {
        $username = htmlentities($_POST['username'],ENT_COMPAT,'UTF-8');
        session_unset();
        session_destroy();
        setcookie("hash", NULL, mktime() - 36000000, "/");
        $login_message = "You have logged out.";
        $log_out = $this->skin->index_guest($username, $login_message);
        return $log_out;
    }

   /**
    * switches between login and register
    *
    * @return string html
    */
    public function register_or_login() {
        if ($_GET['action'] == 'register_submit') {
            $register_or_login = $this->register_submit();
        } else if ($_GET['action'] == 'register') {
            $register_or_login = $this->register();
        } else {
            $register_or_login = $this->log_in();
        }

        return $register_or_login;
    }

   /**
    * logs user in
    *
    * @return string html
    */
    public function log_in() {
        $username = htmlentities($_POST['username'],ENT_COMPAT,'UTF-8');
        if ($username == "") {
            $login_message = "Please enter a username.";
            $log_in = $this->skin->index_guest($username, $login_message);
        } elseif ($_POST['password'] == "") {
            $login_message = "Please enter your password.";
            $log_in = $this->skin->index_guest($username, $login_message);
        } else {
            $player_query = $this->db->execute("SELECT `id`, `username`, `password` FROM `players` WHERE `username`=? AND `password`=?", array($_POST['username'], sha1($_POST['password'])));
            if ($player_query->recordcount() == 0) {
                $login_message = "Incorrect Username/Password.";
                $log_in = $this->skin->index_guest($username, $login_message);
            } else {
                $login_rand = substr(md5(uniqid(rand(), true)), 0, 5);
                $player_db = $player_query->fetchrow();
                $update_player['login_rand'] = $login_rand;
                $update_player['last_active'] = time();
                $player_query = $this->db->AutoExecute('players', $update_player, 'UPDATE', 'id = '.$player_db['id']);
                $hash = sha1($player_db['id'].$player_db['password'].$login_rand);
                $_SESSION['userid'] = $player_db['id'];
                $_SESSION['hash'] = $hash;
                setcookie("cookie_hash", $hash, mktime()+2592000);
                header("Location: index.php");
                exit;
            }
        }
        return $log_in;
    }

   /**
    * displays registration screen
    *
    * @return string html
    */
    public function register() {
        $register = $this->skin->register("", "", "");
        return $register;
    }

   /**
    * registers a user.
    *
    * @return string html
    */
    public function register_submit() {

        $username = htmlentities($_POST['username'],ENT_COMPAT,'UTF-8');
        $email = htmlentities($_POST['email'],ENT_COMPAT,'UTF-8');

        $player_query = $this->db->execute("SELECT id FROM players WHERE username=?", array($_POST['username']));

        if ($username == "") {
            $register_submit = $this->skin->register($username, $email, "You need to fill in your username.");
            return $register_submit;
        } else if (strlen($_POST['username']) < 3) {
            $register_submit = $this->skin->register($username, $email, "Your username must be longer than 3 characters.");
            return $register_submit;
        } else if (!preg_match("/^[-_a-zA-Z0-9]+$/", $_POST['username'])) {
            $register_submit = $this->skin->register($username, $email, "Your username may contain only alphanumerical characters.");
            return $register_submit;
        } else if ($player_query->recordcount() > 0) {
            $register_submit = $this->skin->register($username, $email, "That username has already been used.");
            return $register_submit;
        }


        if (!$_POST['password']) {
            $register_submit = $this->skin->register($username, $email, "You need to fill in your password.");
            return $register_submit;
        } else if ($_POST['password'] != $_POST['password_confirm']) {
            $register_submit = $this->skin->register($username, $email, "You didn't type in both passwords correctly.");
            return $register_submit;
        } else if (strlen($_POST['password']) < 3) {
            $register_submit = $this->skin->register($username, $email, "Your password must be longer than 3 characters.");
            return $register_submit;
        }

        //Check email
        if (!$_POST['email']) {
            $register_submit = $this->skin->register($username, $email, "You need to fill in your email.");
            return $register_submit;
        } else if ($_POST['email'] != $_POST['email_confirm']) {
            $register_submit = $this->skin->register($username, $email, "You didn't type in both email address correctly.");
            return $register_submit;
        } else if (strlen($_POST['email']) < 3) {
            $register_submit = $this->skin->register($username, $email, "Your email must be longer than 3 characters.");
            return $register_submit;
        } else if (!preg_match("/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i", $_POST['email'])) {
            $$register_submit = $this->skin->register($username, $email, "Your email format is wrong.");
            return $register_submit;
        } else {
            $email_query = $this->db->execute("select `id` from `players` where `email`=?", array($_POST['email']));
            if ($email_query->recordcount() > 0) {
                $register_submit = $this->skin->register($username, $email, "That email has already been used. Please create only one account, creating more than one account will cause all your accounts to be deleted.");
                return $register_submit;
            }
        }

        $insert['username'] = $_POST['username'];
        $insert['password'] = sha1($_POST['password']);
        $insert['email'] = $_POST['email'];
        $insert['registered'] = time();
        $insert['last_active'] = time();
        $insert['ip'] = $_SERVER['REMOTE_ADDR'];
        $insert['verified'] = 1;

        $player_insert = $this->db->autoexecute('players', $insert, 'INSERT');
        if (!$player_insert) {
            $register_submit = $this->skin->register($username, $email, "Error registering new user.");
            return $register_submit;
        }
        $player_id = $this->db->Insert_Id();

        $body = "<strong>Welcome to CHERUB Quest!</strong>
        <p>CHERUB Quest is a online browser game for fans of
        the CHERUB series by Robert Muchamore, but isn't
        affiliated so don't contact him with problems.
        If you have any problems, for that matter, look at the
        Help Page or use the Ticket System to contact an Admin
        (TeknoTom, JamieHD, Commander of One, Josh0-4 and Grego)</p>

        <p>But above all, have fun!</p>";

        $mail_query = $this->db->execute("INSERT INTO mail(`to`,`from`,`subject`,`body`,`time`) VALUES (?, '1', 'Welcome to CHERUB Quest!', ?, time() )",array($newmemberid, $body) );

        $register_submit = $this->skin->index_guest($username, "Congratulations! You have successfully registered. You may login to the game now. Enjoy your stay on campus.");
        return $register_submit;

    }

}
?>
