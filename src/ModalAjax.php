<?php

namespace police\widgets\modal;

use yii\base\InvalidConfigException;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Class ModalAjax
 *
 * @package police\widgets\modal
 * @author  Lukyanov Andrey <loveorigami@mail.ru>
 */
class ModalAjax extends Modal
{
    const MODE_SINGLE = 'id';
    const MODE_MULTI = 'multi';

    /**
     * events
     */
    const EVENT_BEFORE_SHOW = 'kbModalBeforeShow';
    const EVENT_MODAL_SHOW = 'kbModalShow';
    const EVENT_BEFORE_SUBMIT = 'kbModalBeforeSubmit';
    const EVENT_MODAL_SUBMIT = 'kbModalSubmit';

    /**
     * @var array
     */
    public $events = [];


    /**
     * The selector to get url request when modal is opened for multy mode
     *
     * @var string
     */
    public $selector;

    /**
     * The url to request when modal is opened for single mode
     *
     * @var string
     */
    public $url;

    /**
     * reload pjax container after ajaxSubmit
     *
     * @var string
     */
    public $pjaxContainer;

    /**
     * Submit the form via ajax
     *
     * @var boolean
     */
    public $ajaxSubmit = true;

    /**
     * Submit the form via ajax
     *
     * @var boolean
     */
    public $autoClose = false;

    /**
     * @var string
     */
    protected $mode = self::MODE_SINGLE;

    /**
     * Renders the header HTML markup of the modal
     *
     * @return string the rendering result
     */
    protected function renderHeader()
    {
        $button = $this->renderCloseButton();
        if ($this->title !== null) {
            Html::addCssClass($this->titleOptions, ['widget' => 'modal-title']);
            $header = Html::tag('h5', $this->title, $this->titleOptions);
            if ($button !== null) {
                $header .= $button;
            }
            Html::addCssClass($this->headerOptions, ['widget' => 'modal-header']);
            return Html::tag('div', "\n" . $header . "\n", $this->headerOptions);
        } else {
            return null;
        }
    }

    /**
     * @inheritdocs
     */
    public function init()
    {
        parent::init();

        if ($this->selector) {
            $this->mode = self::MODE_MULTI;
        }
    }

    /**
     * @inheritdocs
     */
    public function run()
    {
        parent::run();

        /** @var View */
        $view = $this->getView();
        $id = $this->options['id'];

        ModalAjaxAsset::register($view);

        if (!$this->url && !$this->selector) {
            return;
        }

        switch ($this->mode) {
            case self::MODE_SINGLE:
                $this->registerSingleModal($id, $view);
                break;

            case self::MODE_MULTI:
                $this->registerMultyModal($id, $view);
                break;
        }

        if (!isset($this->events[self::EVENT_MODAL_SUBMIT])) {
            $this->defaultSubmitEvent();
        }

        $this->registerEvents($id, $view);
    }

    /**
     * @param      $id
     * @param View $view
     */
    protected function registerSingleModal($id, $view)
    {
        $url = is_array($this->url) ? Url::to($this->url) : $this->url;

        $view->registerJs("
            jQuery('#$id').kbModalAjax({
                url: '$url',
                size:'sm',
                ajaxSubmit: ".($this->ajaxSubmit ? "true" : "false")."
            });
        ");
    }

    /**
     * @param      $id
     * @param View $view
     */
    protected function registerMultyModal($id, $view)
    {
        $view->registerJs("
            jQuery('body').on('click', '$this->selector', function(e) {
                e.preventDefault();
                $(this).attr('data-toggle', 'modal');
                $(this).attr('data-target', '#$id');
                
                var bs_url = $(this).attr('href');
                var title = $(this).attr('title');
                
                if (!title) title = ' ';
                
                jQuery('#$id').find('.modal-header span').html(title);
                
                jQuery('#$id').kbModalAjax({
                    selector: $(this),
                    url: bs_url,
                    ajaxSubmit: $this->ajaxSubmit
                });
            });
        ");
    }

    /**
     * register pjax event
     */
    protected function defaultSubmitEvent()
    {
        $expression = [];

        if ($this->autoClose) {
            $expression[] = "$(this).modal('toggle');";
        }

        if ($this->pjaxContainer) {
            $expression[] = "$.pjax.reload({container : '$this->pjaxContainer'});";
        }

        $script = implode("\r\n", $expression);

        $this->events[self::EVENT_MODAL_SUBMIT] = new JsExpression("
                function(event, data, status, xhr) {
                    if(status){
                        $script
                    }
                }
            ");
    }

    /**
     * @param      $id
     * @param View $view
     */
    protected function registerEvents($id, $view)
    {
        $js = [];
        foreach ($this->events as $event => $expression) {
            $js[] = ".on('$event', $expression)";
        }

        if ($js) {
            $script = "jQuery('#$id')" . implode("\r\n", $js);
            $view->registerJs($script);
        }
    }
}
