<?php

namespace police\yii2;

use yii\web\AssetBundle;

/**
 * Class ModalAjaxAsset
 * @package police\widgets\modal
 * @author Lukyanov Andrey <loveorigami@mail.ru>
 */
class ModalAjaxAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/kb-modal-ajax.js'
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/modal-colors.css',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . "/assets";
        parent::init();
    }
}
