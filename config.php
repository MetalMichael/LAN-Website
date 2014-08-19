<?php

    /**
     * Database Settings
     */
    define("DB_HOST", "localhost");
    define("DB_USER", "lanwebsite");
    define("DB_PASS", "dbp4ssw0rd");
    define("DB_DB", "lanwebsite");
    
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
    

?>
