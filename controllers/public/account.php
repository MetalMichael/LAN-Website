<?php

    class Account_Controller extends LanWebsite_Controller {
        
        public function getInputFilters($action) {
            switch ($action) {
                case "login": return array("username" => ""); break;
                case "authlogin": return array("username" => "notnull", "password" => "notnull"); break;
                case "editvandetails": return array("phone" => array("notnull", "int"), "address" => "notnull", "postcode" => "notnull", "collection" => "", "dropoff" => "", "availability" => "notnull"); break;
                case "editgamedetails": return array("steam" => "", "currently_playing" => "", "favourite_games" => "post"); break;
                case "suggestgame": return array("term" => "notnull"); break;
                case "assignticket": return array("name" => "notnull", "ticket_id" => "notnull"); break;
                case "autocomplete": return array("term" => "notnull"); break;
                case "claimticket": return array("code" => "notnull", "email" => array("notnull", "email")); break;
                case "editaccountdetails": return array("name" => "notnull", "emergency_contact_name" => "notnull", "emergency_contact_number" => "notnull"); break;
            }
        }
     
        public function get_Index() {
            LanWebsite_Main::getAuth()->requireLogin("index.php?page=account");
            $data["member"] = LanWebsite_Main::getUserManager()->getActiveUser()->isMember();
            $tmpl = new LanWebsite_Template();
			$tmpl->setSubTitle("Account Details");
            $tmpl->addScript('/js/pages/account.js');
            $tmpl->addStyle('/css/pages/account.css');
            $tmpl->addTemplate('public/account', $data);
            $tmpl->output();
        }
        
        public function get_Date() {
            echo LanWebsite_Main::getSettings()->getSetting("lan_start_date");
        }
        
        public function post_Deletevan() {
        
            LanWebsite_Main::getAuth()->requireLogin();
            
            //Check if van is enabled
            if (LanWebsite_Main::getSettings()->getSetting("disable_lan_van")) $this->errorJSON("LAN Van is currently disabled - if you need to change your request, contact a committee member ASAP");
            
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            
            //Get van
            $van = LanWebsite_Main::getDb()->query("SELECT * FROM `lan_van` WHERE user_id = '%s' AND lan = '%s'", $user->getUserId(), LanWebsite_Main::getSettings()->getSetting("lan_number"));
            if (!$van) $this->errorJSON("No van requests exist to delete");
            
            LanWebsite_Main::getDb()->query("DELETE FROM `lan_van` WHERE user_id = '%s' AND lan = '%s'", $user->getUserId(), LanWebsite_Main::getSettings()->getSetting("lan_number"));
        
        }
        
        public function post_Editvandetails($inputs) {
        
            LanWebsite_Main::getAuth()->requireLogin();
            
            //Check if van is enabled
            if (LanWebsite_Main::getSettings()->getSetting("disable_lan_van")) $this->errorJSON("LAN Van is currently disabled - if you need to change your request, contact a committee member ASAP");
            
            if ($inputs["collection"] == "false") $inputs["collection"] = 0;
            else $inputs["collection"] = 1;
            if ($inputs["dropoff"] == "false") $inputs["dropoff"] = 0;
            else $inputs["dropoff"] = 1;
            
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            $lan = LanWebsite_Main::getSettings()->getSetting("lan_number");
            
            //Validation
            if ($user->getFullName() == "") $this->errorJSON("Please make sure you set your Real Name at the top of this page before requesting the LAN Van!");
            if ($this->isInvalid('phone')) $this->errorJSON("Please supply a phone number");
            if ($this->isInvalid('address')) $this->errorJSON("Please supply an address");
            if ($this->isInvalid('postcode')) $this->errorJSON("Invalid postcode");
            if ($this->isInvalid('availability')) $this->errorJSON("Please specify a time you are available");
            if ($inputs["collection"] == 0 && $inputs["dropoff"] == 0) $this->errorJSON("You must select at least dropoff or collection, if not both");
            
            //Check for ticket
            $ticket = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE assigned_forum_id = '%s' AND lan_number = '%s'", $user->getUserId(), $lan)->fetch_assoc();
            if (!$ticket) $this->errorJSON("You may only request the LAN Van if you have a ticket assigned to your account");
            
            //Check if already exists
            $van = LanWebsite_Main::getDb()->query("SELECT * FROM `lan_van` WHERE user_id = '%s' AND lan = '%s'", $user->getUserId(), $lan)->fetch_assoc();
            
            //Update
            if ($van) LanWebsite_Main::getDb()->query("UPDATE `lan_van` SET phone_number = '%s', address = '%s', postcode = '%s', collection = '%s', dropoff = '%s', available = '%s' WHERE user_id = '%s' AND lan = '%s'", $inputs["phone"], $inputs["address"], $inputs["postcode"], $inputs["collection"], $inputs["dropoff"], $inputs["availability"], $user->getUserId(), $lan);
            
            //Insert
            else LanWebsite_Main::getDb()->query("INSERT INTO `lan_van` (user_id, lan, phone_number, address, postcode, collection, dropoff, available) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $user->getUserId(), $lan, $inputs["phone"], $inputs["address"], $inputs["postcode"], $inputs["collection"], $inputs["dropoff"], $inputs["availability"]);
                        
            echo true;
        }
        
        public function post_Editgamedetails($inputs) {
        
            LanWebsite_Main::getAuth()->requireLogin();
            
            //If steam name is supplied, validate it
            if ($inputs["steam"] != "") {
            
                //Get data from steam community
                $page = file_get_contents("http://steamcommunity.com/id/" . $inputs["steam"]. "/?xml=1");
                $steam = new SimpleXMLElement($page, LIBXML_NOCDATA);
                
                //Invalid?
                if ($steam->error) {
                    $this->errorJSON("Invalid Steam Community Name");
                }
            }
            
            //Save user details
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            $user->setSteam($inputs["steam"]);
            $user->setCurrentlyPlaying($inputs["currently_playing"]);
            $user->setFavouriteGames($inputs["favourite_games"]);
            LanWebsite_Main::getUserManager()->saveUser($user);
            
            echo true;
        
        }
        
        public function get_Suggestgame($inputs) {
            $data = file_get_contents("http://store.steampowered.com/search/suggest?term=" . urlencode($inputs["term"]));
            $return = array();
            if (strpos($data, "<li>") > -1) {
                $results = explode("<li>", substr(str_replace(array("</li>", "<ul>", "</ul>"), "", $data), 4));
                foreach ($results as $key => $result) {
                    if (stripos($result, "DLC") < 1) {
                        $return[] = $result;
                    }
                }
                echo json_encode($return);
            }
        }
        
        public function get_Checkdetails() {
            if (!LanWebsite_Main::getAuth()->isLoggedIn()) return;
            if (!LanWebsite_Main::isAtLan()) return;
            
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            $message = null;
            
            //Ticket checks
            $ticket = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE assigned_forum_id = '%s' AND lan_number = '%s'", $user->getUserId(), LanWebsite_Main::getSettings()->getSetting("lan_number"))->fetch_assoc();
            if (!$ticket) $message = "Your account does not have any tickets assigned to it. Please visit the registration desk";
            else if ($ticket["activated"] == 0) $message = "You may not enter the LAN until you have signed in and had your ticket activated. Please visit the registration desk";
            
            //Detail checks
            else if ($user->getFullName() == "") $message = "Full name is required";
            elseif ($user->getEmergencyContact() == "" || $user->getEmergencyNumber() == "") $message = "Emergency contact is required";
            
            if ($message != null) echo json_encode(array("incomplete" => true, "message" => $message));
        }
        
        public function post_Assignticket($inputs) {
        
            LanWebsite_Main::getAuth()->requireLogin();
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            
            //Validate
            if ($this->isInvalid("name", "notnull")) $this->errorJSON("Please supply a forum name to assign to");
            
            //Check ticket
            $ticket = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE ticket_id = '%s' AND assigned_forum_id = '' AND purchased_forum_id = '%s'", $inputs["ticket_id"], $user->getUserById())->fetch_assoc();
            if (!$ticket) $this->errorJSON("Invalid ticket - either you did not buy it or it doesn't exist");
            
            //Check name
            $assigneduser = LanWebsite_Main::getUserManager()->getUserByName($inputs["name"]);
            if (!$assigneduser) $this->errorJSON("Username not found");
            
            //Check valid account
            if ($assigneduser->getUserId() == $user->getUserId()) $this->errorJSON("You cannot assign multiple tickets to yourself");
            $memberGroup = LanWebsite_Main::getSettings()->getSetting("xenforo_member_group_id");
            if (!$assigneduser->isMember()) {
                if ($ticket["member_ticket"] == 1) $this->errorJSON("Cannot assign member ticket to non-member forum account");
                $assignedmember = false;
            } else {
                $assignedmember = true;
            }
            
            //Check if account already has assigned ticket
            $previousTicket = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE assigned_forum_id = '%s' AND lan_number = '%s'", LanWebsite_Main::getSettings()->getSetting("lan_number"), $assigneduser->getUserId())->fetch_assoc();
            if ($previousTicket && $previousTicket["member_ticket"] == 0 && $assignedmember) {
                LanWebsite_Main::getDb()->query("UPDATE `tickets` SET assigned_forum_id = '' WHERE ticket_id = '%s'", $previousTicket["ticket_id"]);
            }
            else if ($previousTicket) {
                $this->errorJSON("Account already has a ticket assigned to it");
            }
            
            //Change id
            LanWebsite_Main::getDb()->query("UPDATE `tickets` SET assigned_forum_id = '%s' WHERE ticket_id = '%s'", $assigneduser->getUserId(), $ticket["ticket_id"]);
            
        }
        
        public function get_Autocomplete($inputs) {
            
            LanWebsite_Main::getAuth()->requireLogin();
            $activeuser = LanWebsite_Main::getUserManager()->getActiveUser();
            $users = LanWebsite_Main::getUserManager()->getUsersByName($inputs["term"]);
            
            //Loop
            $return = array();
            if (!$users) die(json_encode($return));
            foreach ($users as $user) {
                if ($user->getUserId() != $activeuser->getUserId()) {
                    $return[] = $user->getUsername;
                }
            }
            echo json_encode($return);
            
        }
        
        public function post_Claimticket($inputs) {
        
            LanWebsite_Main::getAuth()->requireLogin();
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            
            //Check if valid code
            if ($this->isInvalid("code")) $this->errorJSON("Please provide a claim code");
            
            //Attempt to retrieve
            $unclaimed_ticket = LanWebsite_Main::getDb()->query("SELECT * FROM `unclaimed_tickets` WHERE `key`='%s'", $inputs["code"])->fetch_assoc();
            if (!$unclaimed_ticket) $this->errorJSON("Invalid code - if this is a mistake, please contact committee@lsucs.org.uk");
            
            //Check email
            if ($this->isInvalid("email")) $this->errorJSON("Email provided is not a valid address");
            if (strtolower($inputs["email"]) != strtolower($unclaimed_ticket["email"])) $this->errorJSON("Email address supplied does not match ticket");
            
            //Check member status
            if ($unclaimed_ticket["member_ticket"] == 1 && !$user->isMember()) $this->errorJSON("Cannot claim Member Ticket on a Non-Member account. If this is a mistake, please contact committee@lsucs.org.uk");
            
            //Work out who to assign ticket to, if anyone
            $assignID = "";
            $prevTicket = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE assigned_forum_id = '%s' AND lan_number = '%s'", $user->getUserId(), $unclaimed_ticket["lan_number"])->fetch_assoc();
            //If purchasing member ticket and user has non-member assigned, unassign that one and assign the new one
            if ($prevTicket && $user->isMember() && $prevTicket["member_ticket"] == 0 && $unclaimed_ticket["member_ticket"] == 1) {
                LanWebsite_Main::getDb()->query("UPDATE `tickets` SET assigned_forum_id = '' WHERE ticket_id = '%s'", $prevTicket["ticket_id"]);
                $assignID = $user->getUserId();
            }
            //No previous ticket so assign anyway
            else if (!$prevTicket) {
                $assignID = $user->getUserId();
            }
            
            //Insert new ticket
            LanWebsite_Main::getDb()->query("INSERT INTO `tickets` (purchase_id, lan_number, member_ticket, purchased_forum_id, assigned_forum_id) VALUES ('%s', '%s', '%s', '%s', '%s')", $unclaimed_ticket["purchase_id"], $unclaimed_ticket["lan_number"], $unclaimed_ticket["member_ticket"], $user->getUserId(), $assignID);
            
            //Delete claim
            LanWebsite_Main::getDb()->query("DELETE FROM `unclaimed_tickets` WHERE unclaimed_id = '%s'", $unclaimed_ticket["unclaimed_id"]);
        }
        
        public function post_Editaccountdetails($inputs) {
            LanWebsite_Main::getAuth()->requireLogin();
            
            //Validate
            if ($this->isInvalid("name")) $this->errorJSON("Please supply your full name");
            if ($this->isInvalid("emergency_contact_name")) $this->errorJSON("Invalid emergency contact name supplied");
            if ($this->isInvalid("emergency_contact_number")) $this->errorJSON("Invalid emergency contact number supplied");
            
            //Save user
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            $user->setFullName($inputs["name"]);
            $user->setEmergencyContact($inputs["emergency_contact_name"]);
            $user->setEmergencyNumber($inputs["emergency_contact_number"]);
            LanWebsite_Main::getUserManager()->saveUser($user);
            
            echo true;

        }
        
        public function get_Getdetails() {
        
            LanWebsite_Main::getAuth()->requireLogin();
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            $return = array("user_id" => $user->getUserId(), "real_name" => $user->getFullName(), "emergency_contact_name" => $user->getEmergencyContact(), "emergency_contact_number" => $user->getEmergencyNumber(), "steam_name" => $user->getSteam(), "currently_playing" => $user->getCurrentlyPlaying(), "games" => $user->getFavouriteGames());

            //Get van
            $van = LanWebsite_Main::getDb()->query("SELECT * FROM `lan_van` WHERE user_id = '%s' AND lan = '%s'", $user->getUserId(), LanWebsite_Main::getSettings()->getSetting("lan_number"))->fetch_assoc();
            if ($van) $return["van"] = $van;
            $return["van_enabled"] = !LanWebsite_Main::getSettings()->getSetting("disable_lan_van");
            
            echo json_encode($return);
        }
        
        public function get_Gettickets() {
        
            LanWebsite_Main::getAuth()->requireLogin();
            
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            
            $res = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE lan_number = '%s' AND (purchased_forum_id = '%s' OR assigned_forum_id = '%s')", LanWebsite_Main::getSettings()->getSetting("lan_number"), $user->getUserId(), $user->getUserId());
            $tickets = array();
            while ($ticket = $res->fetch_assoc()) {
                $purchaser = LanWebsite_Main::getUserManager()->getUserById($ticket["purchased_forum_id"]);
                $ticket["purchased_forum_name"] = $purchaser->getUsername();
                if ($ticket["assigned_forum_id"] != null && $ticket["assigned_forum_id"] != 0) {
                    $assigned  = LanWebsite_Main::getUserManager()->getUserById($ticket["assigned_forum_id"]);
                    $ticket["assigned_forum_name"] = $assigned->getUsername();
                } else $ticket["assigned_forum_name"] = "";
                $tickets[] = $ticket;
            }
            
            echo json_encode($tickets);
        }
        
        public function get_Login($inputs, $failed = false) {
        
            LanWebsite_Main::getAuth()->requireNotLoggedIn();
            
            //Set up data and output template
            $DataBag = array();
            $DataBag["username"] = $inputs["username"];
            $DataBag["invalid"] = $failed;
            
            $tmpl = new LanWebsite_Template();
			$tmpl->setSubTitle("Login");
            $tmpl->addScript('/js/pages/login.js');
            $tmpl->addStyle('/css/pages/login.css');
            $tmpl->addTemplate('public/login', $DataBag);
            $tmpl->output();
        }
        
        public function get_Logout() {
            LanWebsite_Main::getAuth()->requireLogin();
            LanWebsite_Main::getAuth()->logout();
            header("location: " . LanWebsite_Main::buildUrl(false));
        }
        
        public function post_Authlogin($inputs) {
            LanWebsite_Main::getAuth()->requireNotLoggedIn();
            if (LanWebsite_Main::getAuth()->login($inputs["username"], $inputs["password"])) {
                header("Location:" . LanWebsite_Main::buildUrl(false));
            } else {
                $this->get_Login($inputs, true);
            }
        }
    
    }

?>