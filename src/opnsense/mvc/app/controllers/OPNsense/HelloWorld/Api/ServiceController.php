<?php

namespace OPNsense\HelloWorld\Api;

use OPNsense\Base\ApiControllerBase;
use OPNsense\Core\Backend;

class ServiceController extends ApiControllerBase
{
    public function reloadAction()
    {
        $status = "failed";
        if ($this->request->isPost()) {
            $status = strtolower(trim((new Backend())->configdRun('template reload OPNsense/HelloWorld')));
        }
        return ["status" => $status];
    }

    public function testAction()
    {
        if ($this->request->isPost()) {
            $bckresult = json_decode(trim((new Backend())->configdRun("helloworld test")), true);
            if ($bckresult !== null) {
                return $bckresult;
            }
        }
        return ["message" => "unable to run config action"];
    }
}