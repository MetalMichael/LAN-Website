<?php

    class LanWebsite_UserManager_Default implements LanWebsite_UserManager {
    
        private $userCache = array();
    
		public function getActiveUser() {
            $userid = LanWebsite_Main::getAuth()->getActiveUserId();
            if ($userid == null) return new LanWebsite_User();
            return $this->getUserById($userid);
        }
		
		public function getUserById($userId) {
            //Get user data
            if($userId == 0) return false;
            $data = LanWebsite_Main::getDb()->query("SELECT * FROM users WHERE id = '%s'", $userId)->fetch_assoc();
            if(!$data) return false;                        
            
            //Return user obj from fill function
            return $this->fillUserObj($data);
        }
        
        public function authenticate($username, $password) {
            if(empty($username) || empty($password)) return false;
            if(strpos($username, '@') === false) {
                list($hash, $secret) = LanWebsite_Main::getDb()->query(
                    "SELECT passhash, secret FROM users WHERE username = '%s'", $username)->fetch_row();
            } else {
                list($hash, $secret) = LanWebsite_Main::getDb()->query(
                    "SELECT passhash, secret FROM users WHERE email = '%s'", $username)->fetch_row();
            }
            return (LanWebsite_Crypt::hashPassword($password, $secret) == $hash);
        }
		
		public function getUserByName($name) {
            //Get user data
            if(empty($name)) return false;
            $data = LanWebsite_Main::getDb()->query("SELECT * FROM users WHERE username = '%s'", $name)->fetch_assoc();
            if(!$data) return false;
            
            //Return user obj from fill function
            return $this->fillUserObj($data);
        }
		
		public function getUsersByName($names) {
            //Get user data
            if(empty($names)) return false;
            $output = array();
            foreach ($names as $name) {
                $data = $this->getUserByName($name);
                //Return user obj from fill function
                $output[] = $this->fillUserObj($data);
            }
            
            //Return
            return $output;
        }
        
        public function createAccount($username, $password, $email) {
            if(empty($username) || empty($password) || empty($email)) return false;
            
            $r = $db->query("SELECT * FROM users WHERE username LIKE '%s'", $username);
            if($r->num_rows) $this->errorJson("Sorry, this username is taken!");
            
            $secret = LanWebsite_Crypt::randomHash();
            $hash = LanWebsite_Crypt::hashPassword($password, $secret);
            
            LanWebsite_Main::getDb()->query("
                INSERT INTO `users` (username, email, passhash, secret) VALUES ('%s', '%s', '%s', '%s')",
                $username, $email, $hash, $secret);
        }
        
        public function saveUser(LanWebsite_User $user) {
            LanWebsite_Main::getDb()->query("
                UPDATE `user_data` SET 
                    real_name = '%s',
                    emergency_contact_name = '%s',
                    emergency_contact_number = '%s',
                    steam_name = '%s',
                    currently_playing = '%s'
                WHERE user_id = '%s'",
                $user->getFullName(),
                $user->getEmergencyContact(),
                $user->getEmergencyNumber(),
                $user->getSteam(),
                $user->getCurrentlyPlaying(),
                $user->getUserId()
            );
            LanWebsite_Main::getDb()->query("
                DELETE FROM `user_games`
                WHERE user_id = '%s'",
                $user->getUserId()
            );
            foreach ($user->getFavouriteGames() as $game) {
                LanWebsite_Main::getDb()->query("INSERT INTO `user_games` (user_id, game) VALUES ('%s', '%s')", $user->getUserId(), $game);
            }
        }
        
        private function getLanData($userid) {
            //Get data from LAN website
            $data = LanWebsite_Main::getDb()->query("SELECT * FROM `user_data` WHERE user_id = '%s'", $userid)->fetch_assoc();
            if (!$data) {
                LanWebsite_Main::getDb()->query("INSERT INTO `user_data` (user_id) VALUES (%s)", $userid);
                $data = LanWebsite_Main::getDb()->query("SELECT * FROM `user_data` WHERE user_id = '%s'", $userid)->fetch_assoc();
            }
            $res = LanWebsite_Main::getDb()->query("SELECT * FROM `user_games` WHERE user_id = '%s'", $userid);
            $data["favourite_games"] = array();
            while ($row = $res->fetch_assoc()) $data["favourite_games"][] = $row["game"];
            return $data;
        }
        
        private function fillUserObj($userData) {

            $data = $this->getLanData($userData['id']);
            
            //Fill new user object
            $user = new LanWebsite_User();
            $user->setUserId($userData['id']);
            $user->setUsername($userData['username']);
            $user->setFullName($data['real_name']);
            $user->setEmail($userData['email']);
            $user->setAvatar($userData['avatar']);
            $user->setSteam($data['steam_name']);
            $user->setFavouriteGames($data["favourite_games"]);
            $user->setCurrentlyPlaying($data["currently_playing"]);
            $user->setEmergencyContact($data["emergency_contact_name"]);
            $user->setEmergencyNumber($data["emergency_contact_number"]);
            
            //Work out user level
            $membergroup = LanWebsite_Main::getSettings()->getSetting("xenforo_member_group_id");
            if ($userData['admin'] == true) $user->setUserLevel(UserLevel::Admin);
            else if ($userData['member']) $user->setUserLevel(UserLevel::Member);
            else $user->setUserLevel(UserLevel::Regular);
            
            //Return object
            return $user;
        }
    
    }

?>