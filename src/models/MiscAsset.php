<?php

namespace lhs\craftpageexporter\models;


class MiscAsset extends Asset
{
    public function init() {
        $this->url = $this->fromString;
        parent::init();
    }

    public function updateInitiatorContent()
    {
        parent::updateInitiatorContent();

        if ($this->isInBaseUrl() && $this->url && $this->initiator) {
            $this->initiator->replaceInContent($this->url, $this->getExportUrl(), $this);
        }
    }
}