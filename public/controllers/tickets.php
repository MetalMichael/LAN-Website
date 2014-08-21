<?php

    class Tickets_Controller extends LanWebsite_Controller {
        
        public function getInputFilters($action) {
            switch ($action) {
                case "complete": return array("custom" => "notnull"); break;
                case "checkcomplete": return array("pending_id" => "notnull", "attempts" => "notnull"); break;
                case "checkout": return array("member_amount" => array("notnull", "int"), "nonmember_amount" => array("notnull", "int"), "member_price" => "notnull", "nonmember_price" => "notnull"); break;
            }
        }
        
        public function get_Index() {
                        
            //Prepare data
            
            //User
            $data["is_member"] = LanWebsite_Main::getUserManager()->getActiveUser()->isMember();
            $data["is_signed_in"] = LanWebsite_Main::getAuth()->isLoggedIn();
            //Member Tickets
            $data["member_sold_out"] = LanWebsite_Main::getSettings()->getSetting("member_ticket_sold_out");
            $data["member_available"] = LanWebsite_Main::getSettings()->getSetting("member_ticket_available");
            $data["member_price"] = LanWebsite_Main::getSettings()->getSetting("member_ticket_price");
            $data["member_date"] = date("D jS F", strtotime(LanWebsite_Main::getSettings()->getSetting("member_ticket_available_date")));
            $data["member_deposit"] = LanWebsite_Main::getSettings()->getSetting("member_ticket_deposit");
            $data["member_free"] = LanWebsite_Main::getSettings()->getSetting("member_ticket_free");
            //Non-Member Tickets
            $data["nonmember_exists"] = LanWebsite_Main::getSettings()->getSetting("nonmember_ticket_exists");
            $data["nonmember_sold_out"] = LanWebsite_Main::getSettings()->getSetting("nonmember_ticket_sold_out");
            $data["nonmember_available"] = LanWebsite_Main::getSettings()->getSetting("nonmember_ticket_available");
            $data["nonmember_price"] = LanWebsite_Main::getSettings()->getSetting("nonmember_ticket_price");
            $data["nonmember_date"] = date("D jS F", strtotime(LanWebsite_Main::getSettings()->getSetting("nonmember_ticket_available_date")));
            //Monies
            $data["paypal_email"] = LanWebsite_Main::getSettings()->getSetting("paypal_email");
            $data["paypal_return_url"] = LanWebsite_Main::getSettings()->getSetting("paypal_return_url");
            $data["paypal_ipn_url"] = LanWebsite_Main::getSettings()->getSetting("paypal_ipn_url");
            $data["paypal_url"] = LanWebsite_Main::getSettings()->getSetting("paypal_url");
            //Charity
            $data["charity"] = LanWebsite_Main::getSettings()->getSetting("ticket_charity_donation");
            
            //If free tickets are available, check if one has been claimed already
            if($data["member_free"] && LanWebsite_Main::getUserManager()->getActiveUser()->isMember()) {
                list($data["freeTickBought"]) = LanWebsite_Main::getDb()->query(
                    "SELECT COUNT(*) FROM tickets WHERE lan_number = '%s' AND assigned_forum_id = '%s'",
                    LanWebsite_Main::getSettings()->getSetting("lan_number"),
                    LanWebsite_Main::getAuth()->getActiveUserId())->fetch_row();
            }
        
            $tmpl = LanWebsite_Main::getTemplateManager();
			$tmpl->setSubTitle("Tickets");
            $tmpl->enablePlugin('spinner');
            $tmpl->addTemplate('tickets2', $data);
			$tmpl->output();
        }
        
        public function post_Complete($inputs) {
            LanWebsite_Main::getAuth()->requireLogin();
            $data["pending_id"] = $inputs["custom"];
            $tmpl = LanWebsite_Main::getTemplateManager();
			$tmpl->setSubTitle("Order Progress");
            $tmpl->addTemplate('order-progress', $data);
			$tmpl->output();
        }
        
        public function post_Free($inputs) {
            if(!LanWebsite_Main::getAuth()->isMember()) $this->errorJSON("Non-Member cannot claim member ticket");
            
            list($boughtTicket) = LanWebsite_Main::getDb()->query(
                    "SELECT COUNT(*) FROM tickets WHERE lan_number = '%s' AND assigned_forum_id = '%s'",
                    LanWebsite_Main::getSettings()->getSetting("lan_number"),
                    LanWebsite_Main::getAuth()->getActiveUserId())->fetch_row();
                    
            if($boughtTicket) {
                $this->errorJSON("You have already claimed your free ticket");
            }
            
            $this->checkAvailability();
            
            $this->issueReceipt(LanWebsite_Main::getAuth()->getActiveUserId(), 1, 0);
            
            echo json_encode(array("successful" => true));
        }
        
        public function post_Checkcomplete($inputs) {
        
            LanWebsite_Main::getAuth()->requireLogin();
            
            //Order success
            $purchase = LanWebsite_Main::getDb()->query("SELECT * FROM `pending_purchases` WHERE pending_purchase_id = '%s'", $inputs["pending_id"])->fetch_assoc();
            $purchase_error = LanWebsite_Main::getDb()->query("SELECT * FROM `purchase_errors` WHERE pending_purchase_id = '%s'", $inputs["pending_id"])->fetch_assoc();
            if(!$purchase) {
                echo json_encode(array("status" => "complete"));
            }
            //Retry
            elseif($purchase && !$purchase_error && $inputs["attempts"] <= LanWebsite_Main::getSettings()->getSetting("max_order_lookup_attempts")) {
                echo json_encode(array("status" => "retry"));
            }
            //Order failed
            else {
                $title = "Order Failed";
                echo json_encode(array("status" => "failed"));
            }
            
        }
        
        public function post_Checkout($inputs) {
            
            LanWebsite_Main::getAuth()->requireLogin();
            $user = LanWebsite_Main::getUserManager()->getActiveUser();
            
            //Validate
            if(($inputs["member_amount"] != "" && !is_numeric($inputs["member_amount"])) || ($inputs["nonmember_amount"] != "" && !is_numeric($inputs["nonmember_amount"])) || $inputs["member_amount"] + $inputs["nonmember_amount"] == 0) $this->errorJSON("You must select at least one product");
            if($inputs["member_amount"] > 0 && !$user->isMember()) $this->errorJSON("Non-members cannot buy member tickets");
            if($user->getFullName() == "") $this->errorJSON('You need to fill in your real name in your <a href="index.php?page=account">Account Details</a> before you can buy a ticket');
            
            //Check member tickets are asked for before checking price. Due to "free" tickets having no price.
            if($inputs["member_amount"] != "" && is_numeric($inputs["member_amount"]) && $inputs["member_amount"] > 0) {
                if($inputs["member_price"] < LanWebsite_Main::getSettings()->getSetting("member_ticket_price")) $this->errorJSON("Invalid Member Ticket Price");
            }
            if($inputs["nonmember_price"] < LanWebsite_Main::getSettings()->getSetting("nonmember_ticket_price")) $this->errorJSON("Invalid Non-Member Ticket Price");

            $this->checkAvailability($inputs["member_amount"], $inputs["nonmember_amount"]);
                
            //Calculate total
            $total = $inputs["member_amount"] * $inputs["member_price"] + $inputs["nonmember_amount"] * $inputs["nonmember_price"];
            
            //Insert and return purchase id
            LanWebsite_Main::getDb()->query("INSERT INTO `pending_purchases` (num_member_tickets, num_nonmember_tickets, user_id, total) VALUES ('%s', '%s', '%s', '%s')", $inputs["member_amount"], $inputs["nonmember_amount"], $user->getUserId(), $total);
            echo json_encode(array("pending_id" => LanWebsite_Main::getDb()->getLink()->insert_id));
            
        }
        
        //Ensures there are as many tickets as required. Errors if not
        private function ensureAvailability($memberTickets = 1, $nonMemberTickets = 0) {
            $tickets = $this->checkAvailablility();
            
            //If no tickets available, set tickets available to false and abort
            if($tickets < $memberTickets + $nonMemberTickets) {
                if($tickets == 0) {
                    LanWebsite_Main::getSettings()->changeSetting("member_ticket_available", false);
                    LanWebsite_Main::getSettings()->changeSetting("nonmember_ticket_available", false);
                    $this->errorJSON("Tickets are now sold out for LAN" . LanWebsite_Main::getSettings()->getSetting("lan_number"));
                } else {
                    $this->errorJSON("Only " . $result["availability"] . " ticket(s) available for LAN" .  LanWebsite_Main::getSettings()->getSetting("lan_number"));
                }
            }
        }
        
        //Returns number of tickets available
        private function checkAvailablility() {
            $capacity = LanWebsite_Main::getSettings()->getSetting("lan_capacity");
            
            list($totalSold) = LanWebsite_Main::getDb()->query("SELECT COUNT(*) FROM tickets WHERE lan_number = '%s'", LanWebsite_Main::getSettings()->getSetting("lan_number"))->fetch_row();
            
            return $capacity - $totalSold;
        }
        
        private function issueReceipt($userID, $memberAmount, $nonmemberAmount, $purchaseID) {
            /*
                Optional code to issue receipt goes here
                (was used to interface with an external website)
            */
            $lanNumber = LanWebsite_Main::getSettings("lan_number");
            $customer = LanWebsite_Main::getAuth()->getActiveUserId();
            $customerID = $customer->getUserId();
            for($i = 1; $i <= $memberAmount + $nonmemberAmount; $i++) {
            
                $memberTicket = !($i > $memberAmount);
        
                //Check if user is member
                $member = $customer->isMember();

                //Work out who to assign ticket to, if anyone
                $assignID = "";
                $prevTicket = LanWebsite_Main::getDb()->query("SELECT * FROM `tickets` WHERE assigned_forum_id = '%s' AND lan_number = '%s'", $customerID, $lanNumber)->fetch_assoc();
                //If purchasing member ticket and user has non-member assigned, unassign that one and assign the new one
                if ($prevTicket && $member && $prevTicket["member_ticket"] == 0 && $memberTicket) {
                    LanWebsite_Main::getDb()->query("UPDATE `tickets` SET assigned_forum_id = '' WHERE ticket_id = '%s'", $prevTicket["ticket_id"]);
                    $assignID = $customerID;
                }
                //No previous ticket so assign anyway
                else if (!$prevTicket) {
                    $assignID = $customerID;
                }
                
                //Insert time
                LanWebsite_Main::getDb()->query("INSERT INTO `tickets` (purchase_id, lan_number, member_ticket, purchased_forum_id, purchased_name, assigned_forum_id) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
                    $purchaseID, $lanNumber, $memberTicket, $customerID, $customer->getFullName(), $assignID);
            
            }
        
            
        }
        
        public function post_Ipn() {
        
            //Instantiate the IPN listener
            require_once 'lib/ipnlistener.php';
            $listener = new IpnListener();

            //Tell the IPN listener to use the PayPal test sandbox
            $listener->use_sandbox = false;

            //Try to process the IPN POST
            try {
                $listener->requirePostMethod();
                $verified = $listener->processIpn();
            } catch (Exception $e) {
                $this->error_log($e->getMessage());
                return;
            }
            
            //Verified?
            if($verified) {
            
                //If payment has expired or failed, remove the pending purchase
                if(in_array($_POST['payment_status'], array("Expired", "Failed", "Voided"))) {
                    LanWebsite_Main::getDb()->query("DELETE * FROM `pending_purchases` WHERE pending_purchase_id = '%s'", $_POST["custom"]);
                }
                
                //If the request is for anything but completed, don't care
                if($_POST["payment_status"] != "Completed") {
                    return;
                }
                
                /********************/
                // FRAUD CHECKS
                /********************/
                $errmsg = "";
                $pending_purchase = LanWebsite_Main::getDb()->query("SELECT * FROM `pending_purchases` WHERE pending_purchase_id = '%s'", $_POST["custom"])->fetch_assoc();
                if(!$pending_purchase) {
                    $errmsg .= "Purchase doesn't exist\n";
                }
                if($pending_purchase && $_POST["mc_gross"] != $pending_purchase["total"]) {
                    $errmsg .= "Received total does not match stored total\n";
                }
                if($_POST["mc_currency"] != "GBP") {
                    $errmsg .= "Invalid current code\n";
                }
                if(LanWebsite_Main::getDb()->query("SELECT * FROM `paypal_purchases` WHERE txn_id = '%s'", $_POST["txn_id"])->fetch_assoc()) {
                    $errmsg .= "Transaction ID has already been processed and used\n";
                }
                
                //Calculate member amounts
                $memberAmount = 0;
                $nonmemberAmount = 0;
                if($_POST["num_cart_items"] == 2) {
                    $memberAmount = $_POST["quantity1"];
                    $nonmemberAmount = $_POST["quantity2"];
                } else if($_POST["item_number1"] == "member") {
                    $memberAmount = $_POST["quantity1"];
                } else {
                    $nonmemberAmount = $_POST["quantity1"];
                }
                
                //Check products match
                if($pending_purchase && $pending_purchase["num_member_tickets"] != $memberAmount) {
                    $errmsg .= "Number of member tickets does not match\n";
                }
                if($pending_purchase && $pending_purchase["num_nonmember_tickets"] != $nonmemberAmount) {
                    $errmsg .= "Number of non-member tickets does not match\n";
                }
                
                //Check ifnon-member buying member tickets
                $user = LanWebsite_Main::getUserManager()->getUserById($pending_purchase["user_id"]);
                $group = LanWebsite_Main::getSettings()->getSetting("xenforo_member_group_id");
                if(!$user->isMember() && $memberAmount > 0) {
                    $errmsg .= "Non-member attempting to purchase member tickets";
                }
                
                //If no tickets available, set tickets available to false and abort
                $tickets = $this->checkAvailability($memberAmount, $nonmemberAmount);
                if($tickets < $memberAmount + $memberAmount) {
                    if($tickets == 0) {
                        LanWebsite_Main::getSettings()->changeSetting("member_ticket_available", false);
                        LanWebsite_Main::getSettings()->changeSetting("nonmember_ticket_available", false);
                        $errmsg = "Tickets sold out for LAN" . LanWebsite_Main::getSettings()->getSetting("lan_number");
                    } else {
                        $errmsg = "Only " . $tickets . " ticket(s) available for LAN" . LanWebsite_Main::getSettings()->getSetting("lan_number");
                    }
                }
                
                if(empty($errmsg)) {
                    $this->issueReceipt($pending_purchase["user_id"], $memberAmount, $nonmemberAmount, $pending_purchase["pending_purchase_id"]);

                    /********************/
                    // GOOD TO GO
                    /********************/
                    //If we get here, we are good to say the purchase was completely valid
                    LanWebsite_Main::getDb()->query("INSERT INTO `paypal_purchases` (old_pending_purchase_id, txn_id, payer_email, user_id) VALUES ('%s', '%s', '%s', '%s')", $pending_purchase["pending_purchase_id"], $_POST["txn_id"], $_POST["payer_email"], $pending_purchase["user_id"]);
                    LanWebsite_Main::getDb()->query("DELETE FROM `pending_purchases` WHERE pending_purchase_id = '%s'", $pending_purchase["pending_purchase_id"]);
                }
                
                /********************/
                // ERROR
                /********************/
                else {
                
                    //Log error to file
                    $this->error_log("IPN FAILED FRAUD CHECKS: \n" . $errmsg . "\n\n" . $listener->getTextReport());
                    
                    //Send fraud email to committee
                    $email = new LanWebsite_EmailWrapper();
                    $email->setTo(LanWebsite_Main::getSettings()->getSetting("site-email-error"));
                    $email->setSubject("IPN Fraud Warning");
                    $email->setBody("IPN FAILED FRAUD CHECKS: \n" . $errmsg . "\n\n\n" . $listener->getTextReport());
                    $email->getMessage()->setContentType("text/plain");
                    $email->send();
                    
                    //Add to database as error
                    LanWebsite_Main::getDb()->query("INSERT INTO `purchase_errors` (pending_purchase_id, error_message, text_report) VALUES ('%s', '%s', '%s')", (isset($_POST["custom"])?$_POST["custom"]:""), $errmsg, $listener->getTextReport());
                    
                    return;
                }
                
            } else {
                $this->error_log("INVALID IPN:: " . $listener->getTextReport());
            }
            
        }
        
        private function error_log($message) {
            file_put_contents(".error.log", $message . "\n", FILE_APPEND);
        }
    
    }

?>