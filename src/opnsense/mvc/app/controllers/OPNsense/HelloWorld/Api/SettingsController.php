<?php

namespace OPNsense\HelloWorld\Api;

use OPNsense\Base\ApiMutableModelControllerBase;

class SettingsController extends ApiMutableModelControllerBase
{
    protected static $internalModelClass = 'OPNsense\HelloWorld\HelloWorld';
    protected static $internalModelName = 'helloworld';

    public function getAction()
    {
        $data = parent::getAction();
        $data[self::$internalModelName]['general']['%ToEmail'] = gettext('Enter recipient here');

        return $data;
    }
}