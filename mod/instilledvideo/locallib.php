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

namespace mod_instilledvideo;

/**
 * Event observers
 *
 * @package   instilledvideo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class instilledvideo {

  public static function call_api($method, $url, $data = false) {
    $curl = curl_init();
    $username = get_config('local_instilled_media_gallery', 'username');
    $api_key = get_config('local_instilled_media_gallery', 'apikey');

    switch ($method)
    {
      case 'POST':
        curl_setopt($curl, CURLOPT_POST, 1);

        if ($data)
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
      case 'PUT':
        curl_setopt($curl, CURLOPT_PUT, 1);
        break;
      default:
        if ($data)
          $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'X-KZO-Auth-AccessKey: ' . $api_key,
      'X-KZO-Auth-Username: ' . $username,
      'X-KZO-Accept-API-Versions: 1',
      'Content-Type: application/vnd.api+json',
      'X-KZO-Pipeline-Action: Process'
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }

  public function authenticate_user() {
    global $USER;
    $username = $USER->username;

    $userExists = $this->check_user_exists($username);
    if (!$userExists) {
      $instilled_user = $this->create_instilled_user();
    }

    $access_key = $this->create_access_key($username);
    $USER->instilledaccesskey = $access_key;
  }

  protected function check_user_exists($username) {
    $method = 'GET';
    $tenant_url = get_config('instilledvideo', 'tenanturl');
    $url = $tenant_url . '/api/users/'. $username;

    $user = \mod_instilledvideo\instilledvideo::call_api($method, $url);
    $user = json_decode($user);

    if (property_exists($user, 'users') && property_exists($user->users, 'id')) {
      return true;
    }
    return false;
  }

  protected function create_instilled_user() {
    global $USER;
    $method = 'POST';
    $tenant_url = get_config('instilledvideo', 'tenanturl');
    $url = $tenant_url . '/api/users';
    $post_data = json_encode(array('users'=>array(
      'username' => $USER->username,
      'email' => $USER->email,
      'password' => md5(rand()),
      'authentication_type' => 'PASSWORD',
      'first_name' => $USER->firstname,
      'last_name' => $USER->lastname,
    )), JSON_FORCE_OBJECT);

    $new_user = \mod_instilledvideo\instilledvideo::call_api($method, $url, $post_data);
    $new_user = json_decode($new_user);
    return $new_user;
  }

  protected function create_access_key($username) {
    $method = 'POST';
    $tenant_url = get_config('instilledvideo', 'tenanturl');
    $url = $tenant_url . '/api/access_keys/platform';
    $expires = new \DateTime();
    $expires->modify('+1 day');
    $expires->setTimezone(new \DateTimeZone('UTC'));

    $post_data = json_encode(array('access_keys'=>array(
      'username' => $username,
      'expires_at' => $expires->format('Y-m-d\TH:i:s') . '.000Z',
    )), JSON_FORCE_OBJECT);

    $access_key = \mod_instilledvideo\instilledvideo::call_api($method, $url, $post_data);
    $access_key = json_decode($access_key);
    return $access_key->access_keys->key;
  }
}