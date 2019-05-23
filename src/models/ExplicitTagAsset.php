<?php
/**
 * @author La Haute Société
 * @date   26/04/2019 11:15
 */

namespace lhs\craftpageexporter\models;


class ExplicitTagAsset extends Asset
{

    /**
     * Init
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @return null|void
     */
    public function populateChildren()
    {
        $assetsFromJson = json_decode($this->fromString, true);

        if (!is_array($assetsFromJson)) {
            $assetsFromJson = [];
        }

        foreach ($assetsFromJson as $rawAsset) {
            $asset = new ExplicitAsset([
                'initiator'  => $this,
                'sourcePath' => $rawAsset['sourcePath'],
                'exportPath' => $rawAsset['exportPath'],
            ]);
            $this->addChild($asset);
        }

        $this->updateInitiatorContent();
    }

    /**
     * Remove explicit tag in html
     */
    public function updateInitiatorContent()
    {
        if ($this->initiator instanceof HtmlAsset) {
            $this->initiator->removeDomElement($this->fromDomElement);
        }
    }

}