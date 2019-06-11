<?php

namespace lhs\craftpageexporter\models;


class MiscAsset extends Asset
{
    public function init()
    {
        $this->url = $this->fromString;
        parent::init();
    }

    public function updateInitiatorContent()
    {
        parent::updateInitiatorContent();

        $this->replaceUrlWithExportUrlInInitiator();
    }
}