<?php
/**
 * Description of code_player_profile,php
 *
 * @author josh04
 * @package code_player
 */
class code_player_profile extends code_player {

   /**
    * accesses the "profiles" table.
    *
    */
    public function make_player() {
        $return_value = parent::make_player("profiles");
        if ($this->is_player) {
            if (!isset($this->player_id)) {
                foreach (json_decode($this->settings['custom_fields'], true) as $field => $default) {
                    $profile_string[$field] = $default;
                }
                $profile_data['player_id'] = $player_db['id'];
                $profile_data['profile_string'] = json_encode($profile_string);
                $this->db->AutoExecute('profiles', $profile_data, 'INSERT');

                foreach ($profile_data as $name => $value) {
                    $this->$name = $value;
                }
            }

            $profile_array = json_decode($this->profile_string, true);
            foreach (json_decode($this->settings['custom_fields'], true) as $field => $default) {
                $this->$field = $profile_array[$field];
            }
        }
        return $return_value;
    }

   /**
    * extra db insert stuff
    */
    public function update_player() {
        foreach (json_decode($this->settings['custom_fields']) as $field => $default) {
            $profile_array[$field] = $this->$field;
        }
                
        $profile_update_query['profile_string'] = json_encode($profile_array);

        $this->db->AutoExecute('profiles', $profile_update_query, 'UPDATE', '`player_id`='.$this->id);
        
        parent::update_player();
    }

}
?>