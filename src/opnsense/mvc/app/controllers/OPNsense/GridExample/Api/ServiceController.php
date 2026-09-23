<?php

namespace OPNsense\GridExample\Api;

use OPNsense\Base\ApiControllerBase;

class ServiceController extends ApiControllerBase
{
    public function reconfigureAction()
    {
        sleep(1);
        return ["status" => "ok"];
    }
}