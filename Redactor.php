<?php
/**
 * Redactorjs widget
 *
 * @author Griga Yura
 * v 1.0
 */
class Redactor extends CInputWidget
{
    public $model;
    public $attribute;

    public $externalCss = '';

    /**
     * Editor language
     * Supports: de, en, fr, lv, pl, pt_br, ru, ua
     */
    public $lang = 'en';
    /**
     * Editor toolbar
     * Supports: default, mini
     */
    public $toolbar = 'default';
    /**
     * Html options that will be assigned to the text area
     */
    public $htmlOptions = array();
    /**
     * Editor options that will be passed to the editor
     */
    public $editorOptions = array();
    /**
     * Debug mode
     * Used to publish full js file instead of min version
     */
    public $debugMode = false;
    /**
     * Editor width
     */
    public $width = '100%';
    /**
     * Editor height
     */
    public $height;

    public $assetsFolder;

    /**
     * Display editor
     */
    public function run()
    {
        // Resolve name and id
        list($name, $id) = $this->resolveNameID();

        $this->htmlOptions['id'] = $id;

        if (!array_key_exists('path', $this->editorOptions)) {
            $this->editorOptions['path'] = Yii::app()->baseUrl.$this->assetsFolder;
        }

        $this->editorOptions = CMap::mergeArray($this->getDefaults(), $this->editorOptions);
        $containerId = $this->getContainerId();
        $containerClass = $this->getContainerClass();
        $options = CJavaScript::encode($this->editorOptions);
        $js = <<<JS
		$('#{$id}').redactor({$options});
		 processIframeRedactor('#{$id}', function(body){
		 		body.addClass('$containerClass');
		 		body.closest('html').attr('id','$containerId');
				body.siblings('head').append('<link rel="stylesheet" href="{$this->getCssUrl()}" type="text/css" />');
			}
		 )
JS;
        // Register js code
        Yii::app()->getClientScript()->registerScript('Yii.' . get_class($this) . '#' . $id, $js, CClientScript::POS_READY);


        // Do we have a model
        if ($this->hasModel()) {
            $html = CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
        } else {
            $html = CHtml::textArea($name, $this->value, $this->htmlOptions);
        }

        echo $html;
    }

    /**
     * Returns css url relative to app base url
     * @return string $url
     */
    public function getCssUrl(){
        if (array_key_exists('externalCss', $this->editorOptions)){
            return Yii::app()->baseUrl . $this->editorOptions['externalCss'];
        }else {
            return '';
        }
    }

    /**
     * Returns container class if it exist in editor options
     * Use it to add css class to iframe body element of editor
     * @return string $class
     */
    public function getContainerClass(){
        if (array_key_exists('containerClass', $this->editorOptions)){
            $return = $this->editorOptions['containerClass'];
            unset($this->editorOptions['containerClass']);
            return $return;
        }else {
            return '';
        }
    }

    /**
     * Returns container id if it exist in editor options
     * Use it to add id attribute to iframe body element of editor
     * @return string $class
     */
    public function getContainerId(){
        if (array_key_exists('containerId', $this->editorOptions)){
            $return = $this->editorOptions['containerId'];
            unset($this->editorOptions['containerId']);
            return $return;
        }else {
            return '';
        }
    }

    public static $clipsRendered = false;
    public function init()
    {
        if (!self::$clipsRendered){
            require_once(__DIR__.'/clips.php');
            self::$clipsRendered = true;
        }
        parent::init();
        $assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets');
        $this->assetsFolder = $assets;

        $jsFile = $this->debugMode ? 'redactor.js' : 'redactor.min.js';
        cs()->registerScriptFile($assets . '/' . $jsFile);
        cs()->registerScriptFile('/js/redactor.mcmed.js');
        cs()->registerCssFile($assets . '/redactor.css');
        cs()->registerScriptFile('/js/redactor.clips.js');
        cs()->registerScriptFile($assets . '/lang/ru.js');
        cs()->registerCssFile($assets . '/plugins/clips/clips.css');
        $js = <<<JS
		function processIframeRedactor(selector, callback) {
			var body = $('body', $(selector).siblings('iframe').contents());
			if (body.length != 1) {
			  setTimeout(function(){
			  	processIframeRedactor(selector, callback)
			  }, 100);
			  return;
			}
			callback(body);
		  }
JS;
        cs()->registerScript('redactorJsStuff',$js, CClientScript::POS_READY);
    }



    public static function activeRedactorWidget($model, $attribute, $editorOptions = array(), $htmlOptions = array())
    {
        $model->$attribute = $model->$attribute ? $model->$attribute : '<div>&nbsp;<br><div>';
        return Yii::app()->getController()->widget(__CLASS__, array(
            'model' => $model,
            'attribute' => $attribute,
            'editorOptions' => $editorOptions,
            'htmlOptions' => $htmlOptions,
        ), true);
    }

    public static function activeTbRedactorWidget($model, $attribute, $editorOptions = array(), $htmlOptions = array())
    {
        $model->$attribute = $model->$attribute ? $model->$attribute : '<div>&nbsp;<br><div>';
        $out = CHtml::openTag('div', array('class'=>'control-group'));
        $out .= CHtml::activeLabelEx($model, $attribute, array('class'=>'control-label'));
        $out .= CHtml::openTag('div', array('class'=>'controls'));
        $out .= Yii::app()->getController()->widget(__CLASS__, array(
            'model' => $model,
            'attribute' => $attribute,
            'editorOptions' => $editorOptions,
            'htmlOptions' => $htmlOptions,
        ), true);
        $out .= CHtml::closeTag('div') . CHtml::closeTag('div');
        return $out;

    }

    public static function simpleRedactorWidget($value, $name, $editorOptions = array(), $htmlOptions = array()){
        return Yii::app()->getController()->widget(__CLASS__, array(
            'value' => $value,
            'name'=>$name,
            'editorOptions' => $editorOptions,
            'htmlOptions' => $htmlOptions,
        ), true);
    }

    public function getDefaults(){
        return array(
            'minHeight'=>200,
            'toolbarFixed'=>false,
            'pastePlainText' => false,
            'paragraphy' => false,
            'removeEmptyTags'=>false,
            'convertDivs' => false,
            'linebreaks'=>true,
            'externalCss' => '/css/layout.css?' . time(),
            'iframe'=>true,
            'italicTag'=>'i',
            'containerClass' => 'page-content',
            'containerId'=>'content',
            'imageGetJson' => Yii::app()->controller->createUrl('upload/getUploadedImages'),
            'imageUpload' => Yii::app()->controller->createUrl('upload/imageUpload'),
            'lang' => 'ru',
            'toolbar' => 'default',
            'plugins' =>array('mcmed'),
            'formattingTags' =>array('p', 'blockquote', 'h1', 'h2','h3'),
        );
    }

    public static function mergeWithDefaults($array){
        return CMap::mergeArray(self::getDefaults(), $array);
    }


}

?>