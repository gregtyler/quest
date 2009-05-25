<?php
/**
 * Description of skin_install
 *
 * @author josh04
 * @package skin_install
 */
class skin_install extends skin_common {

   /**
    * intro page
    *
    * @return string html
    */
    public function start() {
        $start = "Welcome to the Quest installer. To begin, click continue.<br />
            <br />
            <a href='index.php?page=database'>Continue.</a><br />
            <br />
            <a href='index.php?page=upgrade_database'>To upgrade from an ezRPG install, click here.</a>";
        return $start;
    }

   /**
    * make a db form
    *
    * @param string $db_server server url
    * @param string $db_username db username
    * @param string $db_name db name
    * @param string $message did they fuck up?
    * @return string html
    */
    public function setup_database_form($db_server, $db_username="", $db_name="", $message="") {
        $setup_database_form = $message."<br />
            <form action='index.php?page=database&amp;action=confirm' method='POST'>
                Server: <input type='text' name='db_server' value='".$db_server."' /><br />
                Username: <input type='text' name='db_username' value='".$db_username."' /><br />
                Password: <input type='password' name='db_password' /><br />
                Password Confirm: <input type='password' name='db_password_confirm' /><br />
                Database: <input type='text' name='db_name' value='".$db_name."' /><br />
                <input type='submit' value='Set up database' />
            </form>
            ";
        return $setup_database_form;
    }

   /**
    * for making an admin user
    *
    * @param string $message screwed up?
    * @param string $username username!
    * @param string $user_email email!
    * @param string $user_email_confirm email again!
    * @return string html
    */
    public function user_add_form($message="", $username="", $user_email="", $user_email_confirm="") {
        $user_add_form = $message."<br />
            <form action='index.php?page=user&amp;action=confirm' method='POST'>
                Username: <input type='text' name='username' value='".$username."' /><br />
                Email: <input type='text' name='email' value='".$user_email."' /><br />
                Email Confirm: <input type='text' name='email_confirm' value='".$user_email_confirm."' /><br />
                Password: <input type='password' name='password' /><br />
                Password Confirm: <input type='password' name='password_confirm' /><br />
                <input type='submit' value='Set up database' />
            </form>
            ";
        return $user_add_form;
    }

   /**
    * done!
    *
    * @return string html
    */
    public function complete() {
        $complete = "Install complete! Click here to log in: <a href='index.php'>Continue.</a>";
        return $complete;
    }

   /**
    * shows the ezrpg db upgrade page
    *
    * @return string html
    */
    public function upgrade_page() {
        $upgrade_page = "Please backup your ezrpg database before proceeding.
            Due to the nature of ezrpg installs, databases vary greatly so it
            is not possible to guarentee the following procedure will work.<br />
            <br />
            <a href='index.php?page=upgrade_database&amp;action=upgrade'>Continue</a>.";
        return $upgrade_page;
    }

   /**
    * they did it!
    *
    * @return string html
    */
    public function setup_database_complete() {
        $setup_database_complete = "Database installation was successful. <a href='index.php?page=user'>Click here to continue.</a>";
        return $setup_database_complete;
    }

}
?>