<?php

    /**
     * Database Settings
     */
    define("DB_HOST", "localhost");
    define("DB_USER", "lanwebsite");
    define("DB_PASS", "dbp4ssw0rd");
    define("DB_DB", "lanwebsite");
    
    /**
     * Site name. Used for LOGO and in title
     */
    define("SITE_NAME", "MMTest");
    
    /**
     * Website host. Used for redirects and things
     */
    define("SITE_HOST", "lanwebsite.michaelfiford.me");
    /**
     * Protocol to use for default URL.
     * http or https almost all of the time
     */
    define("DEFAULT_PROTOCOL", "http");
    
    //Don't change this
    define("SITE_URL", DEFAULT_PROTOCOL . "://" . SITE_HOST);
    
    /**
     * Google Analytics ID
     * Comment if unwanted
     */
    //define("GA_ID", "UA-XXXXXXXX-1");
    
    /**
     * Base LanWebsite Library Directory
     */
    define("LIB_DIR", "/home/lanwebsite/htdocs/lib/");
    
    /**
     * Default Controller location
     */
    define("CONTROLLER_DIR", "/public/controllers/");
    
    /**
     * Auth Mechanism
     */
    define("AUTH_SYS", "LanWebsite_Auth_Default");

    /**
     * UserManager
     */
    define("USER_SYS", "LanWebsite_UserManager_Default");
    
    /**
     * SEO-friendly URLs
     */
    define("USE_SEO", true);
    
    /**
     * Cookie Config
     * Note: Use leading '.' to signify multiple subdomains
     */
    define("COOKIE_HOST", "lanwebsite.michaelfiford.me");
    
    /**
     * Site Salt
     * DO NOT CHANGE after accounts have been created
     */
    define("SITE_SALT", "*Sh._s3jllp!X\"sd\\1");
    
    
    /**
     * SITE FEATURES
     */
    // A browser based chat similar to facebook chat. Requires daemon running to work.
    define("ENABLE_CHAT", true);
    // A live map. Again requires the appropriate daemon running to work.
    define("ENABLE_MAP", true);
    // Game hub is where users can create lobbies and find players wanting to play a certain game. Requires daemon again.
    define("ENABLE_GAMEHUB", true);
    // Seat booking allows users to create groups, with a preference of where they sit. Users enter their shared code to join a group.
    define("ENABLE_SEATBOOKING", true);
    // LAN Van is where users can request for their equipment to be collected and returned by the society.
    define("ENABLE_LANVAN", true);
    
    /**
     * VISUAL THINGS
     */
    //A link to the forums. You can comment this if you don't want a forum button
    define("URL_FORUMS", "http://lsucs.org.uk");
    
    define("DEFAULT_AVATAR", 'http://lsucs.org.uk/styles/default/xenforo/avatars/avatar_l.png');
?>
