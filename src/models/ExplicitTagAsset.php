<?php
/**
 * @author La Haute Société
 * @date   26/04/2019 11:15
 */

namespace lhs\craftpageexporter\models;

use Exception;
use JsonException;

class ExplicitTagAsset extends Asset
{
    /**
     * Init
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();
    }

    /**
     * @throws JsonException
     */
    public function populateChildren(): void
    {
        $assetsFromJson = json_decode($this->fromString, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($assetsFromJson)) {
            $assetsFromJson = [];
        }

        foreach ($assetsFromJson as $rawAsset) {
            $asset = new ExplicitAsset([
                'initiator'  => $this,
                'sourcePath' => $rawAsset['sourcePath'],
                'exportPath' => $rawAsset['exportPath'],
                'rootAsset'  => $this->getRootAsset(),
            ]);
            $this->addChild($asset);
        }

        $this->updateInitiatorContent();
    }

    /**
     * Remove explicit tag in html
     */
    public function updateInitiatorContent(): void
    {
        if ($this->initiator instanceof HtmlAsset) {
            $this->initiator->removeDomElement($this->fromDomElement);
        }
    }
}
