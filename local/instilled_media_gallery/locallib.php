<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Description.
 *
 * @since Moodle 3.7
 * @package local_instilled_media_gallery
 * @copyright  2020 Instilled <support@instilled.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_instilled_media_gallery;

defined('MOODLE_INTERNAL') || die();

class instilled {

    /**
     * Calls the Instilled API with PHP Curl
     */
    public static function call_api($method, $url, $data = false, $user = null, $key = null) {
        $curl = curl_init();
        $username = isset($user) ? $user : get_config('local_instilled_media_gallery', 'username');
        $apikey = isset($key) ? $key : get_config('local_instilled_media_gallery', 'apikey');

        switch ($method)
        {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'X-KZO-Auth-AccessKey: ' . $apikey,
            'X-KZO-Auth-Username: ' . $username,
            'X-KZO-Accept-API-Versions: 1',
            'Content-Type: application/vnd.api+json',
            'X-KZO-Pipeline-Action: Process'
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error = curl_errno($curl);
        }

        curl_close($curl);

        return $response;
    }

    /**
     * Authenticates the user with Instilled by checking if the user exists,
     * updating the user with the correct role if necessary, and creating a
     * short-lived access key.
     * @return string
     */
    public function authenticate_user($context = null, $user = null) {
        global $USER;
        $username = isset($user) ? $user : $USER->username;

        $userexists = $this->check_user_exists($username);
        if (!$userexists) {
            $instilleduser = $this->create_instilled_user();
        }

        if (isset($context) && has_capability('mod/instilledvideo:addinstance', $context)) {
            $this->update_instilled_role();
        }

        $accesskey = $this->create_access_key($username);
        return $accesskey;
    }

    /**
     * Checks if the Moodle user already exists in the Instilled database
     * @return boolean
     */
    protected function check_user_exists($username) {
        $method = 'GET';
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $url = $tenanturl . '/api/users/'. $username;

        $user = self::call_api($method, $url);
        $user = json_decode($user);

        if ($user && property_exists($user, 'users') && property_exists($user->users, 'id')) {
            return true;
        }
        return false;
    }

    /**
     * If the user had the capability to add an Instilled activity module,
     * make sure he/she has the permissions to upload videos in Instilled.
     * @return array
     */
    protected function update_instilled_role() {
        global $USER;
        $method = 'POST';
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $teachergroup = get_config('local_instilled_media_gallery', 'teachergroup');
        $url = $tenanturl . '/api/groups/'. $teachergroup .'/users';
        $postdata = '{"users": ["'.$USER->username.'"]}';

        self::call_api($method, $url, $postdata);
    }

    /**
     * Create an Instilled user with the same username as the Moodle user
     * @return object
     */
    protected function create_instilled_user() {
        global $USER;
        $method = 'POST';
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $url = $tenanturl . '/api/users';
        $postdata = json_encode(array(
            'users' => array(
                'username' => $USER->username,
                'email' => $USER->email,
                'password' => md5(rand()),
                'authentication_type' => 'PASSWORD',
                'first_name' => $USER->firstname,
                'last_name' => $USER->lastname,
            )
        ), JSON_FORCE_OBJECT);

        $newuser = self::call_api($method, $url, $postdata);
        $newuser = json_decode($newuser);
        return $newuser;
    }

    /**
     * Created a short-lived access key to view and manage Instilled content
     * @return string
     */
    protected function create_access_key($username) {
        $method = 'POST';
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $url = $tenanturl . '/api/access_keys/platform';
        $expires = new \DateTime();
        $expires->modify('+6 hours');
        $expires->setTimezone(new \DateTimeZone('UTC'));

        $postdata = json_encode(array('access_keys' => array(
            'username' => $username,
            'expires_at' => $expires->format('Y-m-d\TH:i:s') . '.000Z',
        )), JSON_FORCE_OBJECT);

        $accesskey = self::call_api($method, $url, $postdata);
        $accesskey = json_decode($accesskey);
        return $accesskey->access_keys->key;
    }
}
