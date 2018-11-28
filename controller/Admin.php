<?php

namespace Controller;

use System\PublicController;
use System\Request;
use System\Response;

class Admin extends PublicController
{


    public function index(Request $request, Response $response)
    {
        $response->display('Admin.Index');

    }

}
