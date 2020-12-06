<?php

//
// Includes
//

require_once(__DIR__ . '/include.php');

//
// Types
//

class AppModel
{
    //
    // Private data
    //

    /** @var TwigWrapper */ private $twigwrapper;
    /** @var AppConf */ private $appconf;
    /** @var PageData */ private $pagedata;

    //
    // Public routines
    //

    public function __construct()
    {
        $this->twigwrapper = new TwigWrapper();
        $this->appconf = new AppConf();
        $this->pagedata = new PageData();
    }

    /**
     * @return TwigWrapper
     */
    public function getTwigWrapper()
    {
        return $this->twigwrapper;
    }

    /**
     * @return AppConf
     */
    public function getAppConf()
    {
        return $this->appconf;
    }

    /**
     * @return PageData
     */
    public function getPageData()
    {
        return $this->pagedata;
    }

    // ...
}
