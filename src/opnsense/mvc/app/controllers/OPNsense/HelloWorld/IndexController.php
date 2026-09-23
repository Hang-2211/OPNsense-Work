<?php
namespace OPNsense\HelloWorld;
use OPNsense\Base\ControllerBase;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pick('OPNsense/HelloWorld/index');
        $this->view->generalForm = $this->getForm("general");
    }
}