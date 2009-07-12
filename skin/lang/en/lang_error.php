<?php
/**
 * error codes
 *
 * @author josh04
 * @package skin_lang
 */
class lang_error {

    // common
    public $page_not_exist = "The page you have tried to access does not exist.";
    public $no_password = "You did not enter a password.";
    public $passwords_do_not_match = "The entered passwords do not match.";

    // db
    public $failed_to_connect = "Failed to connect to database.";
    public $no_database_username = "You did not enter a username.";
    public $no_database_name = "You did not enter a database name.";
    public $no_database_server = "You did not enter a database server.";
    public $database_server_error = "The database could not be contacted.";
    public $database_create_error = "There was an error creating a database table. Ensure your database user has the permissions to create tables.";
    public $upgrade_db_no_object = "Your installation of ezrpg is not suitable to be upgraded.";
    public $db_query_failed = "There was a problem accessing the database";

    // battle
    public $player_not_found = "Player not found.";
    public $no_player_selected = "No player selected.";
    public $no_players_found = "No players found.";
    public $cannot_attack_self = "You cannot attack yourself.";
    public $player_no_energy = "You do not have enough energy to attack";
    public $player_currently_incapacitated = "You are currently incapacitated.";
    public $enemy_currently_incapacitated = "Your opponent is currently incapacitated.";

    // login
    public $password_wrong = "The password entered is incorrect.";
    public $password_updated = "Password successfully updated.";
    public $profile_updated = "Profile sucessfully updated.";

    // register
    public $username_not_long_enough = "Your username must be longer than 3 characters.";
    public $username_banned_characters = "Your username may contain only alphanumerical characters.";
    public $password_not_long_enough = "Your password must be longer than 3 characters.";
    public $no_email = "You did not enter an email address.";
    public $emails_do_not_match = "The entered emails do not match.";
    public $email_wrong_format = "You did not enter a valid email address.";
    public $error_registering = "Error registering new user: ";

    // ticketing
    public $ticket_not_exist = "The ticket you tried to access does not exist.";

    // blueprints
    public $no_blueprints = "There are currently no blueprints.";
    public $no_blueprint_selected = "You did not select a blueprint.";
    public $blueprint_removed = "The selected blueprint has been removed.";
    public $blueprint_remove_failed = "No blueprint was selected to delete.";
    public $blueprint_added = "Blueprint added.";
    public $add = "Add";
    public $modify = "Modify";
    public $remove = "Remove";

    // mail
    public $messages_deleted = "Messages deleted.";

}
?>
