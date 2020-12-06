<?php

//
// Includes
//

require_once(__DIR__ . '/include.php');

//
// Types
//

class PageData extends Map
{
    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();

        $data = array();
        $data['method'] = ''; // HTTP method in lowercase, such as 'get'
        $data['client_ip'] = ''; // IPv4 or IPv6 address of the client
        $data['route'] = '/'; // The current route

        $this->setMap($data);
    }

    // ...
}
