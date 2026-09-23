<?php

namespace OPNsense\GridExample;

class IndexController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->pick('OPNsense/GridExample/index');
        $this->view->formDialogAddress = $this->getForm("dialogAddress");
        $this->view->formGridAddress = $this->getFormGrid("dialogAddress");
    }
}