<?php

namespace lhs\craftpageexporter\models;

class MiscAsset extends Asset
{
    public function init(): void
    {
        $this->url = $this->fromString;
        parent::init();
    }

    public function updateInitiatorContent(): void
    {
        parent::updateInitiatorContent();

        $this->replaceUrlWithExportUrlInInitiator();
    }
}
