<?php
/**
 * Redactorjs widget
 *
 * @author Griga Yura
 * v 1.0
 */
class UploadedImagesAction  extends CAction
{
	public function run(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$dir = Yii::app()->redactor->thumbsPath;
		$files = array();

		$it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
		$thumbsUrl = Yii::app()->redactor->thumbsUrl;
		$originalUrl = Yii::app()->redactor->originalUrl;

		foreach(new RecursiveIteratorIterator($it) as $file => $splFileObject) {
			/** @var $splFileObject SplFileObject*/


				$files[] = array(
				'thumb'=> $thumbsUrl . $splFileObject->getFilename(),
				'image'=> $originalUrl . $splFileObject->getFilename(),
				'title'=> $splFileObject->getFilename(),
				'folder'=>Yii::app()->redactor->uploadsDir,
			);
		}

		echo CJavaScript::jsonEncode($files);
		Yii::app()->end();
	}




}

?>
