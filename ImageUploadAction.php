<?php
/**
 * Redactorjs widget
 *
 * @author Griga Yura
 * v 1.0
 */
class ImageUploadAction extends CAction
{
    public function run()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        Yii::import('ext.redactor.RjFileUploader');
        $uploader = new RjFileUploader(array('jpeg', 'jpg', 'gif', 'png'));
        $dir = Yii::app()->redactor->originalPath;
        $result = $uploader->handleUpload($dir);
        if ($result['success'] && Yii::app()->image) {
            $image = Yii::app()->image->load($dir . $result['filename']);
            $image->resize(100, 100)->quality(80);
            $image->save(Yii::app()->redactor->thumbsPath . $result['filename']);
            echo CJavaScript::jsonEncode(array(
                'filelink' => Yii::app()->redactor->originalUrl . $result['filename']
            ));
        } else {
            echo CJavaScript::jsonEncode(array(
                'error' => $result
            ));
        }


        Yii::app()->end();
    }


}

?>
