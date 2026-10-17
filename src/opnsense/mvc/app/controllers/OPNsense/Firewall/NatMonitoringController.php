<?php

namespace OPNsense\Firewall;

class NatMonitoringController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->title = gettext("NAT Monitoring");
        $this->view->pick('OPNsense/Firewall/nat_monitoring');
    }
}