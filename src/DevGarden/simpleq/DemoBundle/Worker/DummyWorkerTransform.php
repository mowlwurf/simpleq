<?php

namespace DevGarden\simpleq\DemoBundle\Worker;

use DevGarden\simpleq\WorkerBundle\Service\BaseWorker;

class DummyWorkerTransform extends BaseWorker
{
    public function execute()
    {
        $data = json_decode($this->data);
        $percent = 0.5;

        list($width, $height) = getimagesize($data->file);

        $newWidth = $width * $percent;
        $newHeight = $height * $percent;
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        $source = imagecreatefromjpeg($data->file);

        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $thumbFile = str_replace('/images/', '/images/thumbs/', $data->file);
        imagejpeg($thumb, $thumbFile);

        $this->data = $thumbFile;
    }
}