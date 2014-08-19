-- phpMyAdmin SQL Dump
-- version 4.2.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 20, 2014 at 12:57 AM
-- Server version: 5.5.38-0+wheezy1
-- PHP Version: 5.4.4-14+deb7u12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lanwebsite`
--
CREATE DATABASE IF NOT EXISTS `lanwebsite` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `lanwebsite`;

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE IF NOT EXISTS `blog` (
`blog_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `food_options`
--

CREATE TABLE IF NOT EXISTS `food_options` (
`option_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

--
-- Table structure for table `food_orders`
--

CREATE TABLE IF NOT EXISTS `food_orders` (
`order_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lan_number` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `option_name` varchar(80) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `paid` tinyint(1) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1141 ;

-- --------------------------------------------------------

--
-- Table structure for table `food_shops`
--

CREATE TABLE IF NOT EXISTS `food_shops` (
`shop_id` int(11) NOT NULL,
  `shop_name` varchar(80) NOT NULL,
  `order_by` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `lan_van`
--

CREATE TABLE IF NOT EXISTS `lan_van` (
  `user_id` int(11) NOT NULL,
  `lan` float NOT NULL,
  `phone_number` varchar(25) NOT NULL,
  `address` text NOT NULL,
  `postcode` text NOT NULL,
  `collection` tinyint(1) NOT NULL,
  `dropoff` tinyint(1) NOT NULL,
  `available` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logger_entries`
--

CREATE TABLE IF NOT EXISTS `logger_entries` (
`log_id` int(11) NOT NULL,
  `logger_session_id` int(11) NOT NULL,
  `type` text NOT NULL,
  `time` decimal(20,4) NOT NULL,
  `data` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16989 ;

-- --------------------------------------------------------

--
-- Table structure for table `logger_sessions`
--

CREATE TABLE IF NOT EXISTS `logger_sessions` (
`logger_session_id` int(11) NOT NULL,
  `lan_number` decimal(10,2) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `url` text NOT NULL,
  `start_time` decimal(20,4) NOT NULL,
  `end_time` decimal(20,4) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13790 ;

-- --------------------------------------------------------

--
-- Table structure for table `map_cache`
--

CREATE TABLE IF NOT EXISTS `map_cache` (
  `seat` varchar(5) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` text NOT NULL,
  `name` text NOT NULL,
  `steam` text NOT NULL,
  `avatar` text NOT NULL,
  `ingame` tinyint(1) NOT NULL,
  `game` text NOT NULL,
  `mostplayed` text NOT NULL,
  `favourite` text NOT NULL,
  `game_icon` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `paypal_purchases`
--

CREATE TABLE IF NOT EXISTS `paypal_purchases` (
`purchase_id` int(11) NOT NULL,
  `old_pending_purchase_id` int(11) NOT NULL,
  `txn_id` varchar(19) NOT NULL,
  `payer_email` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1081 ;

-- --------------------------------------------------------

--
-- Table structure for table `pending_purchases`
--

CREATE TABLE IF NOT EXISTS `pending_purchases` (
`pending_purchase_id` int(11) NOT NULL,
  `num_member_tickets` int(11) NOT NULL,
  `num_nonmember_tickets` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1296 ;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_errors`
--

CREATE TABLE IF NOT EXISTS `purchase_errors` (
`purchase_error_id` int(11) NOT NULL,
  `pending_purchase_id` int(11) DEFAULT NULL,
  `error_message` text NOT NULL,
  `text_report` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `raffle_tickets`
--

CREATE TABLE IF NOT EXISTS `raffle_tickets` (
  `lan_number` int(11) NOT NULL,
  `raffle_ticket_number` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `seatbooking_groups`
--

CREATE TABLE IF NOT EXISTS `seatbooking_groups` (
  `ID` varchar(50) NOT NULL,
  `seatPreference` text,
  `groupOwner` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
`ticket_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `lan_number` float NOT NULL,
  `member_ticket` tinyint(1) NOT NULL,
  `purchased_forum_id` int(11) NOT NULL,
  `purchased_name` text NOT NULL,
  `assigned_forum_id` int(11) DEFAULT NULL,
  `activated` tinyint(1) NOT NULL,
  `seat` varchar(5) NOT NULL,
  `seatbooking_group` varchar(50) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1555 ;

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE IF NOT EXISTS `timetable` (
`timetable_id` int(11) NOT NULL,
  `day` varchar(20) NOT NULL,
  `start_time` varchar(5) NOT NULL,
  `end_time` varchar(5) NOT NULL,
  `url` text NOT NULL,
  `title` varchar(200) NOT NULL,
  `colour` varchar(20) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=217 ;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_alerts`
--

CREATE TABLE IF NOT EXISTS `tournament_alerts` (
`ID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `level` varchar(30) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_matches`
--

CREATE TABLE IF NOT EXISTS `tournament_matches` (
`id` int(10) NOT NULL,
  `tournament_id` int(10) NOT NULL,
  `player1` varchar(50) DEFAULT '0',
  `player2` varchar(50) DEFAULT '0',
  `played_bool` tinyint(1) DEFAULT '0',
  `score1` varchar(100) DEFAULT '0',
  `score2` varchar(100) DEFAULT '0',
  `winner` tinyint(1) DEFAULT NULL,
  `teams_bool` tinyint(1) DEFAULT NULL,
  `round` int(3) DEFAULT '0',
  `game` int(3) DEFAULT '0'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=785 ;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_signups`
--

CREATE TABLE IF NOT EXISTS `tournament_signups` (
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL DEFAULT '0',
  `team_temporary` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_teams`
--

CREATE TABLE IF NOT EXISTS `tournament_teams` (
`ID` int(10) NOT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `Icon` varchar(255) DEFAULT NULL,
  `Description` text,
  `Temporary` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_teams_invites`
--

CREATE TABLE IF NOT EXISTS `tournament_teams_invites` (
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_teams_members`
--

CREATE TABLE IF NOT EXISTS `tournament_teams_members` (
  `team_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `permission` int(2) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tournament_tournaments`
--

CREATE TABLE IF NOT EXISTS `tournament_tournaments` (
`id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `lan` float NOT NULL,
  `game` tinyint(4) NOT NULL,
  `team_size` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `signups` tinyint(1) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `signup_close` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `description` text NOT NULL,
  `started` tinyint(1) NOT NULL DEFAULT '0',
  `collated` tinyint(1) NOT NULL DEFAULT '0',
  `static_link` varchar(255) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `unclaimed_tickets`
--

CREATE TABLE IF NOT EXISTS `unclaimed_tickets` (
`unclaimed_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `lan_number` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `member_ticket` tinyint(1) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `passhash` varchar(100) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `member` tinyint(1) NOT NULL,
  `admin` tinyint(1) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_data`
--

CREATE TABLE IF NOT EXISTS `user_data` (
  `user_id` int(11) NOT NULL,
  `real_name` varchar(100) NOT NULL,
  `emergency_contact_name` text NOT NULL,
  `emergency_contact_number` varchar(50) NOT NULL,
  `steam_name` varchar(100) NOT NULL,
  `currently_playing` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_games`
--

CREATE TABLE IF NOT EXISTS `user_games` (
  `user_id` int(11) NOT NULL,
  `game` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
 ADD PRIMARY KEY (`blog_id`);

--
-- Indexes for table `food_options`
--
ALTER TABLE `food_options`
 ADD PRIMARY KEY (`option_id`), ADD UNIQUE KEY `option_name` (`option_name`);

--
-- Indexes for table `food_orders`
--
ALTER TABLE `food_orders`
 ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `food_shops`
--
ALTER TABLE `food_shops`
 ADD PRIMARY KEY (`shop_id`);

--
-- Indexes for table `lan_van`
--
ALTER TABLE `lan_van`
 ADD PRIMARY KEY (`user_id`,`lan`);

--
-- Indexes for table `logger_entries`
--
ALTER TABLE `logger_entries`
 ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `logger_sessions`
--
ALTER TABLE `logger_sessions`
 ADD PRIMARY KEY (`logger_session_id`);

--
-- Indexes for table `map_cache`
--
ALTER TABLE `map_cache`
 ADD UNIQUE KEY `seat` (`seat`,`user_id`);

--
-- Indexes for table `paypal_purchases`
--
ALTER TABLE `paypal_purchases`
 ADD PRIMARY KEY (`purchase_id`);

--
-- Indexes for table `pending_purchases`
--
ALTER TABLE `pending_purchases`
 ADD PRIMARY KEY (`pending_purchase_id`);

--
-- Indexes for table `purchase_errors`
--
ALTER TABLE `purchase_errors`
 ADD PRIMARY KEY (`purchase_error_id`);

--
-- Indexes for table `raffle_tickets`
--
ALTER TABLE `raffle_tickets`
 ADD PRIMARY KEY (`lan_number`,`raffle_ticket_number`);

--
-- Indexes for table `seatbooking_groups`
--
ALTER TABLE `seatbooking_groups`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
 ADD UNIQUE KEY `session_id` (`session_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
 ADD PRIMARY KEY (`setting_name`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
 ADD PRIMARY KEY (`ticket_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
 ADD PRIMARY KEY (`timetable_id`);

--
-- Indexes for table `tournament_alerts`
--
ALTER TABLE `tournament_alerts`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tournament_matches`
--
ALTER TABLE `tournament_matches`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournament_signups`
--
ALTER TABLE `tournament_signups`
 ADD PRIMARY KEY (`tournament_id`,`user_id`);

--
-- Indexes for table `tournament_teams`
--
ALTER TABLE `tournament_teams`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tournament_teams_invites`
--
ALTER TABLE `tournament_teams_invites`
 ADD PRIMARY KEY (`team_id`,`user_id`);

--
-- Indexes for table `tournament_teams_members`
--
ALTER TABLE `tournament_teams_members`
 ADD PRIMARY KEY (`team_id`,`user_id`);

--
-- Indexes for table `tournament_tournaments`
--
ALTER TABLE `tournament_tournaments`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unclaimed_tickets`
--
ALTER TABLE `unclaimed_tickets`
 ADD PRIMARY KEY (`unclaimed_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_data`
--
ALTER TABLE `user_data`
 ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_games`
--
ALTER TABLE `user_games`
 ADD PRIMARY KEY (`user_id`,`game`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `food_options`
--
ALTER TABLE `food_options`
MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `food_orders`
--
ALTER TABLE `food_orders`
MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1141;
--
-- AUTO_INCREMENT for table `food_shops`
--
ALTER TABLE `food_shops`
MODIFY `shop_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `logger_entries`
--
ALTER TABLE `logger_entries`
MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16989;
--
-- AUTO_INCREMENT for table `logger_sessions`
--
ALTER TABLE `logger_sessions`
MODIFY `logger_session_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13790;
--
-- AUTO_INCREMENT for table `paypal_purchases`
--
ALTER TABLE `paypal_purchases`
MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1081;
--
-- AUTO_INCREMENT for table `pending_purchases`
--
ALTER TABLE `pending_purchases`
MODIFY `pending_purchase_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1296;
--
-- AUTO_INCREMENT for table `purchase_errors`
--
ALTER TABLE `purchase_errors`
MODIFY `purchase_error_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1555;
--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=217;
--
-- AUTO_INCREMENT for table `tournament_alerts`
--
ALTER TABLE `tournament_alerts`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `tournament_matches`
--
ALTER TABLE `tournament_matches`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=785;
--
-- AUTO_INCREMENT for table `tournament_teams`
--
ALTER TABLE `tournament_teams`
MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `tournament_tournaments`
--
ALTER TABLE `tournament_tournaments`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `unclaimed_tickets`
--
ALTER TABLE `unclaimed_tickets`
MODIFY `unclaimed_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
