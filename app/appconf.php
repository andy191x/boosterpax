<?php

//
// Includes
//

require_once(__DIR__ . '/include.php');

//
// Types
//

class AppConf extends Map
{
    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();
    }

    public function loadFromPHPFile()
    {
        // Load conf
        $conf_file = PROJECT_CONF_FOLDER . '/conf.php';
        if (file_exists_file($conf_file))
        {
            require_once($conf_file);
        }

        // Assign values
        $data = array();
        $data['twig_cache_folder'] = (string)defined_or_default('CONF_TWIG_CACHE_FOLDER', '');

        $this->setMap($data);
    }

    // ...
}
