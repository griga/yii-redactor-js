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
	public $containerClass = '';

	/**
	 * Editor language
	 * Supports: de, en, fr, lv, pl, pt_br, ru, ua
	 */
	public $lang = 'ru';
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
	public $height = '400px';

	public $assetsFolder;

	/**
	 * Display editor
	 */
	public function run()
	{
		// Resolve name and id
		list($name, $id) = $this->resolveNameID();

		$this->htmlOptions['id'] = $id;

		if (!array_key_exists('style', $this->htmlOptions)) {
			$this->htmlOptions['style'] = "width:{$this->width};height:{$this->height};";
		}

		if (!array_key_exists('path', $this->editorOptions)) {
			$this->editorOptions['path'] = Yii::app()->baseUrl.$this->assetsFolder;
		}


		$options = array_merge($this->editorOptions, array('lang' => $this->lang, 'toolbar' => $this->toolbar));
		if(array_key_exists('externalCss', $this->editorOptions)){
			$options = array_merge($options, array('iframe'=>true));
		}

		$options = CJSON::encode($options);
		$js = <<<JS
		$('#{$id}').redactor({$options});
		 processIframeRedactor('#{$id}', function(body){
		 		body.addClass('{$this->getContainerClass()}');
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
	 * @return string $class
	 */
	public function getContainerClass(){
		if (array_key_exists('containerClass', $this->editorOptions)){
			return $this->editorOptions['containerClass'];
		}else {
			return '';
		}
	}

	public function init()
	{
		parent::init();
		// Get assets dir
		$baseDir = dirname(__FILE__);
		$assets = Yii::app()->getAssetManager()->publish($baseDir . DIRECTORY_SEPARATOR . 'assets');

		$this->assetsFolder = $assets;
		// Publish required assets
		$cs = Yii::app()->getClientScript();
		/**@var CClientScript $cs */

		$jsFile = $this->debugMode ? 'redactor.js' : 'redactor.min.js';
		$cs->registerScriptFile($assets . '/' . $jsFile);
		$cs->registerCssFile($assets . '/css/redactor.css');
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
		$cs->registerScript('redactorJsStuff',$js, CClientScript::POS_READY);
	}



	public static function activeRedactorWidget($model, $attribute, $editorOptions = array(), $htmlOptions = array())
	{
		return Yii::app()->getController()->widget(__CLASS__, array(
			'model' => $model,
			'attribute' => $attribute,
			'editorOptions' => $editorOptions,
			'htmlOptions' => $htmlOptions,
		), true);
	}

	public static function activeTbRedactorWidget($model, $attribute, $editorOptions = array(), $htmlOptions = array())
	{
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


}

?>
