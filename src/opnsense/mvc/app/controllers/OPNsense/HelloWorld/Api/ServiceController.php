<?php

namespace OPNsense\HelloWorld\Api;

use OPNsense\Base\ApiMutableServiceControllerBase;

class ServiceController extends ApiMutableServiceControllerBase
{
    protected static $internalServiceClass = '\OPNsense\HelloWorld\HelloWorld';
    protected static $internalServiceTemplate = 'OPNsense/HelloWorld';
    protected static $internalServiceEnabled = 'general.enabled';
    protected static $internalServiceName = 'helloworld';

    protected function reconfigureForceRestart()
    {
        return 0;
    }
}