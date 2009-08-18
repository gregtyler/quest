<?php
/**
 * Description of skin_profile
 *
 * @author josh04
 * @package skin_public
 */
class skin_profile extends skin_common {
   
  /**
   * makes the profile page
   *
   * @param player $profile array of profile links
   * @param string $friendlist the pre-formatted friend list
   * @param string $message any errors that have been drawn
   * @return string html
   */
   function make_profile($profile, $friendlist, $message='') {
        $make_profile = "
            ".$message."
            <img src=\"".$profile->avatar."\" alt=\"[user avatar]\" style=\"max-width:50px;max-height:50px;float:left;border:1px solid #DDD;padding:4px;margin:8px;\" />
            <h2 style='line-height:20px;'>".$profile->username."
            <a title='User is ".$profile->is_online."'><img src='../icons/status_".$profile->is_online.".png' /></a><br />
            <span style='font-size:10px;font-weight:normal;'>( <a href='index.php?page=mail&amp;action=compose&amp;name=".$profile->username."'>Mail</a>".($profile->edit?' '.$profile->edit:'')." )</span></h2>
            <br style='clear:both;' />
            <div style='font-size:11px;'>
            <p><i>".$profile->description."</i></p>
            <strong>Registered:</strong> ".$profile->registered."<br />
            <strong>Character age:</strong> ".$profile->age." days<br />
            <strong>Level:</strong> ".$profile->level."<br />
            <strong>Kills/deaths:</strong> ".$profile->kills."/".$profile->deaths." (".$profile->ratio.")<br />
            ".$profile->im_links."
            <br />".$profile->extras."

            <strong>Friends (".$profile->friendcount.")</strong><br />
            ".$friendlist."<br /><br />
            </div>
            <form action='index.php?page=profile&amp;action=donate&amp;id=".$profile->id."' method='POST'>
            <input type='text' name='donate' />
            <input type='submit' accesskey='S' value='Donate to ".$profile->username."' />
            </form>";
         return $make_profile;
   }

  /**
   * edit profile link
   * 
   * @return string html
   */
   function edit_profile_link() {
       $edit_profile_link = "| <a href='index.php?page=profile_edit'>Edit Profile</a>";
       return $edit_profile_link;
   }

  /**
   * add to friends link
   * 
   * @return string html
   */
   function add_friend_link($id) {
       $add_friend_link = "| <a href='index.php?page=profile&friends=add&id=".$id."'>Add to friends</a>";
       return $add_friend_link;
   }

   /**
    * generates html for instant messenger link
    * 
    * @return string html
    */
    function im_link($im, $address) {
        $im_link = "<strong>".$im."</strong>: ".$address."<br />";
        return $im_link;
    }

   /**
    * generates html for avatar
    *
    * @param string $avatar_url url of avatar
    * @return string html
    */
    function player_avatar($avatar_url = "images/avatar.png") {
        $player_avatar = "<img src='".$avatar_url."' alt='User Avatar'><br />";
        return $player_avatar;
    }


   /**
    * makes the edit profile page
    *
    * @param player $profile profile to display
    * @param string $gender_list gender html
    * @param string $show_email show email?
    * @param string $section if this is the admin use of the form.
    * @param string $message error message?
    * @return string html
    */
    function edit_profile($profile, $gender_list, $show_email, $section, $message="") {
        $edit_profile = "
                            <h2>Edit profile</h2>
                            ".$message."
                                <a href='#' style='color:transparent;' onClick='showHide(\"showhide-1\",\"showhide-1-icon\");'><div class='edit-profile-header'><img id='showhide-1-icon' src='images/dropdown_open.png' style='float:right;' alt='&laquo;' />Edit Profile</div></a>
                                <div class='edit-profile-body' id='showhide-1'><form action='index.php?".$section."page=profile_edit&amp;action=update_profile' method='POST'>
                                <input type='hidden' name='id' value='".$profile->id."' />
                                <table>
                                    <tr><td style='width:50%;'><label style='margin:0;'>Username</label></td>
                                    <td style='width:50%;'><strong>".$profile->username."</strong></td></tr>

                                    <tr><td><label for='edit-email'>Email</label></td>
                                    <td><input type='text' id='edit-email' name='email' value='".$profile->email."' /></td></tr>

                                    <tr><td><label for='edit-show-email' style='margin:0;'>Show email</label></td>
                                    <td><input type='checkbox' id='edit-show-email' name='show_email' ".$show_email." /></td></tr>

                                    <tr><td><label for='edit-description'>Description</label></td>
                                    <td><input type='text' id='edit-description' name='description' value='".$profile->description."'/></td></tr>

                                    <tr><td><label for='edit-avatar'>Avatar URL</label></td>
                                    <td><input type='text' id='edit-avatar' name='avatar' value='".$profile->avatar."'/></td></tr>

                                    <tr><td><label for='edit-gender'>Gender</label></td>
                                    <td><select id='edit-gender' name='gender'>".$gender_list."</select></td></tr>

                                    <tr><td><label for='edit-msn'>MSN address</label></td>
                                    <td><input type='text' id='edit-msn' name='msn' value='".$profile->msn."'/></td></tr>

                                    <tr><td><label for='edit-aim'>AIM address</label></td>
                                    <td><input type='text' id='edit-aim' name='aim' value='".$profile->aim."'/></td></tr>

                                    <tr><td><label for='edit-skype'>Skype ID</label></td>
                                    <td><input type='text' name='skype' value='".$profile->skype."'/></td></tr>

                                    <tr><td colspan='2'><input type='submit' name='submit' value='Submit' /></td></tr>
                                </table>

                                </form></div>

                                ";

        return $edit_profile;
    }

   /**
    * Edit password code, seperated.
    *
    * @return string html
    */
    public function edit_password() {
        $edit_password = "<a href='#' style='color:transparent;' onClick='showHide(\"showhide-2\",\"showhide-2-icon\");'><div class='edit-profile-header'><img id='showhide-2-icon' src='images/dropdown_open.png' style='float:right;' alt='&laquo;' />Change Password</div></a>
            <div class='edit-profile-body' id='showhide-2'><form action='index.php?page=profile_edit&amp;action=update_password' method='POST'>
            <table>
                <tr><td style='width:50%;'><label for='edit-p1'>Current password</label></td>
                <td style='width:50%;'><input type='password' id='edit-p1' name='current_password' /></td></tr>

                <tr><td><label for='edit-p2'>New password</label></td>
                <td><input type='password' id='edit-p2' name='new_password' /></td></tr>

                <tr><td><label for='edit-p3'>Confirm new password</label></td>
                <td><input type='password' id='edit-p3' name='confirm_password' /></td></tr>

                <tr><td colspan='2'><input type='submit' name='submit' value='Submit'/></td></tr>
            </table></form>
            </div>";
        return $edit_password;
    }

   /**
    * fer makin people admins
    *
    * @param string $permission_options list of options
    * @param int $id player id
    * @return string html
    */
    public function edit_permissions($permission_options, $id) {
        $edit_permissions = "<a href='#' style='color:transparent;' onClick='showHide(\"showhide-3\",\"showhide-3-icon\");'><div class='edit-profile-header'><img id='showhide-3-icon' src='images/dropdown_open.png' style='float:right;' alt='&laquo;' />Edit Permissions</div></a>
            <div class='edit-profile-body' id='showhide-3'><form action='index.php?section=admin&amp;page=profile_edit&amp;action=update_permissions' method='POST'>
            <input type='hidden' name='id' value='".$id."' />
            <table>
                <tr><td style='width:50%;'><label for='edit-p1'>Permission Level</label></td>
                <td style='width:50%;'><select id='edit-p1' name='rank' />
                    ".$permission_options."
                </td></tr>
                <tr><td colspan='2'><input type='submit' name='submit' value='Submit'/></td></tr>
            </table></form>
            </div>";
        return $edit_permissions;
    }

   /**
    * options!
    *
    * @param string $current_permission
    * @return string html
    */
    public function permissions_list($current_permission) {
        $current_permission = "permission_".$current_permission;
        $permission_Member .= "<option value='Member'";
        $permission_Admin .= "<option value='Admin'";
        $$current_permission .= " selected='selected'";
        $permission_Member .= ">Member</option>";
        $permission_Admin .= ">Admin</option>";
        $permission_list = $permission_Member.$permission_Admin;
        return $permission_list;
    }

   /**
    * Sneakily placed here. I'm such a cheat.
    *
    * @param int $id member id
    * @return string html
    */
    public function edit_password_admin($id) {
        $edit_password = "<a href='#' style='color:transparent;' onClick='showHide(\"showhide-2\",\"showhide-2-icon\");'><div class='edit-profile-header'><img id='showhide-2-icon' src='images/dropdown_open.png' style='float:right;' alt='&laquo;' />Change Password</div></a>
            <div class='edit-profile-body' id='showhide-2'><form action='index.php?section=admin&amp;page=profile_edit&amp;action=update_password' method='POST'>
            <input type='hidden' name='id' value='".$id."' />
            <table>
                <tr><td><label for='edit-p2'>New password</label></td>
                <td><input type='password' id='edit-p2' name='new_password' /></td></tr>

                <tr><td><label for='edit-p3'>Confirm new password</label></td>
                <td><input type='password' id='edit-p3' name='confirm_password' /></td></tr>

                <tr><td colspan='2'><input type='submit' name='submit' value='Submit'/></td></tr>
            </table></form>
            </div>";
        return $edit_password;
    }

   /**
    * list of gender choices
    *
    * @param int $current_gender current gender
    * @return string html
    */
    public function gender_list($current_gender) {
        $current_gender = "gender_".$current_gender;
        $gender_0 .= "<option value='0'";
        $gender_1 .= "<option value='1'";
        $gender_2 .=" <option value='2'";
        $$current_gender .= " selected='selected'";
        $gender_0 .= ">Undisclosed</option>";
        $gender_1 .= ">Female</option>";
        $gender_2 .= ">Male</option>";
        $gender_list = $gender_0.$gender_2.$gender_1;
        return $gender_list;
    }

   /**
    * displays gender
    *
    * (TODO) need a proper place for images. in skin, i guess?
    *
    * @param int $gender gender
    * @return string html
    */
    public function gender($gender) {
        $gender_array = array(array("Undisclosed","_gray"),array("Female","_female"),array("Male",""));
        $gender = "<img src=\"../icons/user".$g[$gender][1].".png\" alt=\"".$g[$gender][0]."\" title=\"".$g[$gender][0]."\" />";
        return $gender;
    }

   /**
    * converts an array of the users' friends into a list
    *
    * @param array $friends friends to listify
    * @return string html
    */
    public function friendlist($friends) {
        $ret = "<ul class='friendlist'>";
        foreach($friends as $id => $username) {
            $ret .= "<li><a href='?page=profile&id=".$id."'>".$username."</a></li>";
            }
        $ret .= "</ul>";
        return $ret;
    }

   /**
    * Donate to charity?
    *
    * @param int $amount amount donated
    * @param string $name name of donatee
    * @return string html
    */
    public function donated($amount, $name) {
        $donated = "<div class='success'>You donated £".$amount." to ".$name.".</div>";
        return $donated;
    }

}
?>
