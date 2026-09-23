<?php

namespace OPNsense\GridExample\Api;

use OPNsense\Base\ApiMutableModelControllerBase;

class SettingsController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'gridexample';
    protected static $internalModelClass = 'OPNsense\GridExample\GridExample';

    public function searchItemAction()
    {
        return $this->searchBase("addresses.address", null, "email");
    }

    public function setItemAction($uuid)
    {
        return $this->setBase("address", "addresses.address", $uuid);
    }

    public function addItemAction()
    {
        return $this->addBase("address", "addresses.address");
    }

    public function getItemAction($uuid = null)
    {
        return $this->getBase("address", "addresses.address", $uuid);
    }

    public function delItemAction($uuid)
    {
        return $this->delBase("addresses.address", $uuid);
    }

    public function toggleItemAction($uuid, $enabled = null)
    {
        return $this->toggleBase("addresses.address", $uuid, $enabled);
    }
}