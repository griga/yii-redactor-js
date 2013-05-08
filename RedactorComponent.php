<?php
/** Created by griga at 08.05.13 | 20:17.
 * 
 */

class RedactorComponent extends CApplicationComponent{
	private $_uploadsDir = 'uploads';
	private $_originalPath;
	private $_thumbsPath;

	public function setUploadsDir($uploadsDir)
	{
		$this->_uploadsDir = $uploadsDir;
	}

	public function getUploadsDir()
	{
		return $this->_uploadsDir;
	}



	public function setOriginalPath($originalPath)
	{
		$this->_originalPath = $originalPath;
	}

	public function getOriginalPath(){
		return Yii::app()->getBasePath() . '/..' . $this->_originalPath;
	}

	public function setThumbsPath($thumbsPath)
	{
		$this->_thumbsPath = $thumbsPath;
	}

	public function getThumbsPath(){
		return Yii::app()->getBasePath() . '/..' . $this->_thumbsPath;
	}

	public function getOriginalUrl(){
		return Yii::app()->baseUrl . $this->_originalPath;
	}

	public function getThumbsUrl(){
		return Yii::app()->baseUrl . $this->_thumbsPath;
	}

}