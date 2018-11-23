<?php

namespace Controller;

use System\PublicController;
use System\Request;
use System\Response;

class Index extends PublicController
{


    public function index(Request $request, Response $response)
    {
        $response->display('Index.index');

    }

}
