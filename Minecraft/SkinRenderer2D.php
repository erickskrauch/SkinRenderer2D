<?php
/*
The MIT License (MIT)

Copyright (c) 2014 by ErickSkrauch <erickskrauch@ely.by>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
namespace ErickSkrauch\Minecraft;

class SkinRenderer2D {
    private $image = NULL;
    private $isAlpha = NULL;
    private $is1_8;

    /**
     * Loads the skin image from a file path
     * ================================================
     * Загржает изображение скина из указанного пути
     *
     * @param $file
     * @throws \Exception
     */
    public function assignSkinFromFile ($file) {
        if (!is_null($this->image)) {
            imagedestroy($this->image);
            $this->clearCache();
        }

        $this->image = imagecreatefrompng($file);

        if(!$this->image)
            throw new \Exception("Could not open PNG file.");

        if(!$this->isValid())
            throw new \Exception("Invalid skin image.");
    }

    /**
     * Loads the skin image from a string
     * ================================================
     * Загружает изображение скина из переданной строки
     *
     * @param $data
     * @throws \Exception
     */
    public function assignSkinFromString ($data) {
        if (!is_null($this->image)) {
            imagedestroy($this->image);
            $this->clearCache();
        }

        $this->image = imagecreatefromstring($data);

        if(!$this->image)
            throw new \Exception("Could not load image data from string.");

        if(!$this->isValid())
            throw new \Exception("Invalid skin image.");
    }

    /**
     * Returns the width of the skin.
     * ================================================
     * Возвращает ширину скина
     *
     * @return int
     * @throws \Exception
     */
    public function getWidth () {
        if(!is_null($this->image))
            return imagesx($this->image);

         throw new \Exception("No skin loaded.");
    }

    /**
     * Returns the height of the skin.
     * ================================================
     * Возвращает высоту скина
     *
     * @return int
     * @throws \Exception
     */
    public function getHeight () {
        if(!is_null($this->image))
            return imagesy($this->image);

        throw new \Exception("No skin loaded.");
    }

    // TODO: phpdoc блок
    public function isAlpha() {
        if(is_null($this->image))
            throw new \Exception("No skin loaded.");

        if (is_null($this->isAlpha))
            $this->isAlpha = imagecolorsforindex($this->image, imagecolorat($this->image, 1, 1))['alpha'] == 127;

        return $this->isAlpha;
    }

    /**
     * Returns true if the skin has valid dimensions, false otherwise.
     * Возвращает true, если скин имеет валидные соотношения сторон
     *
     * @return bool
     */
    public function isValid () {
        if ($this->getWidth() != 64 && ($this->getHeight() != 32 || $this->getHeight() != 64))
            return false;

        $this->is1_8 = $this->getHeight() == 64;
        return true;
    }

    // TODO: phpdoc блок
    private function clearCache() {
        $this->isAlpha = NULL;
        $this->is1_8 = NULL;
    }

    /**
     * Returns prepared resource for rendering skin
     * If $r is NULL, then generates a transparent background
     * ================================================
     * Генерирует подготовленный ресурс для рендеринга скина
     * Если $r NULL, то создаёт прозрачный фон
     *
     *
     * @param int $width
     * @param int $height
     * @param null $r
     * @param null $g
     * @param null $b
     * @return resource
     */
    private function createEmptyImage($width = 16, $height = 32, $r = NULL, $g = NULL, $b = NULL) {
        $newImage = imagecreatetruecolor($width, $height);

        if (!is_null($r))
            $background = imagecolorallocate($newImage, $r, $g, $b);
        else {
            $background = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagesavealpha($newImage, true);
            imagealphablending($newImage, true);
        }

        imagefill($newImage, 0, 0, $background);

        return $newImage;
    }

    // TODO: оформить phpdoc блок
    private function scaleImage($image, $newWidth, $newHeight, $saveAlpha = false) {
        $resize = imagecreatetruecolor($newWidth, $newHeight);

        if ($saveAlpha) {
            imagesavealpha($resize, true);
            imagealphablending($resize, false);
        }

        imagecopyresized($resize, $image, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($image), imagesy($image));
        imagedestroy($image);

        return $resize;
    }

    /**
     * Returns an image handle consisting of an (optionally) scaled front view of the skin.
     * $r, $g, $b are used to construct the background color.
     * ================================================
     * Возвращает дескриптор изображение, опционально увеличенного, с передней частью скина
     * $r, $g, $b используются для задания фонового цвета
     *
     * @param int $scale
     * @param int $r
     * @param int $g
     * @param int $b
     * @return resource
     */
    public function frontImage ($scale = 1, $r = NULL, $g = NULL, $b = NULL) {
        $newWidth = 16 * $scale;
        $newHeight = 32 * $scale;
        $colorAt = imagecolorat($this->image, 62, 1);

        $newImage = $this->createEmptyImage(16, 32, $r, $g, $b);

        //head | голова
        imagecopy($newImage, $this->image, 4, 0, 8, 8, 8, 8);
        //head mask | маска
        $this->imageСopyAlpha($newImage, $this->image, 4, 0, 40, 8, 8, 8, $colorAt);
        //body | тело
        imagecopy($newImage, $this->image, 4, 8, 20, 20, 8, 12);
        //right leg | правая нога
        imagecopy($newImage, $this->image, 4, 20, 4, 20, 4, 12);
        //right arm | правая рука
        imagecopy($newImage, $this->image, 0, 8, 44, 20, 4, 12);

        // Рендерим элементы в зависимости от версии
        if ($this->is1_8) {
            //left leg | левая нога
            imagecopy($newImage, $this->image, 8, 20, 20, 52, 4, 12);
            //left arm | левая рука
            imagecopy($newImage, $this->image, 12, 8, 36, 52, 4, 12);

            //body 2 | тело 2
            $this->imageСopyAlpha($newImage, $this->image, 4, 8, 20, 36, 8, 12, $colorAt);
            //right leg 2 | правая нога 2
            $this->imageСopyAlpha($newImage, $this->image, 4, 20, 4, 36, 4, 12, $colorAt);
            //left leg 2 | левая нога 2
            $this->imageСopyAlpha($newImage, $this->image, 8, 20, 4, 52, 4, 12, $colorAt);
            //right arm 2 | правая рука 2
            $this->imageСopyAlpha($newImage, $this->image, 0, 8, 44, 36, 4, 12, $colorAt);
            //left arm 2 | левая рука 2
            $this->imageСopyAlpha($newImage, $this->image, 12, 8, 52, 52, 4, 12, $colorAt);
        } else {
            //left leg | левая нога
            $this->imageFlip($newImage, $this->image, 8, 20, 4, 20, 4, 12);
            //left arm | левая рука
            $this->imageFlip($newImage, $this->image, 12, 8, 44, 20, 4, 12);
        }

        // Scale the image
        // Изменяем размер изображения
        if($scale != 1)
            return $this->scaleImage($newImage, $newWidth, $newHeight, is_null($r));

        return $newImage;
    }

    /**
     * Returns an image handle consisting of an (optionally) scaled back view of the skin.
     * $r, $g, $b are used to construct the background color.
     * ================================================
     * Возвращает дескриптор изображение, опционально увеличенного, с задней частью скина
     * $r, $g, $b используются для задания фонового цвета
     *
     * @param int $scale
     * @param int $r
     * @param int $g
     * @param int $b
     * @return resource
     */
    public function backImage ($scale = 1, $r = NULL, $g = NULL, $b = NULL) {
        $newWidth = 16 * $scale;
        $newHeight = 32 * $scale;
        $colorAt = imagecolorat($this->image, 62, 1);

        $newImage = $this->createEmptyImage(16, 32, $r, $g, $b);

        //head | голова
        imagecopy($newImage, $this->image, 4, 0, 24, 8, 8, 8);
        //head mask | маска
        $this->imageСopyAlpha($newImage, $this->image, 4, 0, 56, 8, 8, 8, imagecolorat($this->image, 63, 0));
        //body | тело
        imagecopy($newImage, $this->image, 4, 8, 32, 20, 8, 12);
        //right leg | правая нога
        imagecopy($newImage, $this->image, 8, 20, 12, 20, 4, 12);
        //right arm | правая рука
        imagecopy($newImage, $this->image, 12, 8, 52, 20, 4, 12);

        // Рендерим элементы в зависимости от версии
        if ($this->is1_8) {
            //left leg | левая нога
            imagecopy($newImage, $this->image, 4, 20, 28, 52, 4, 12);
            //left arm | левая рука
            imagecopy($newImage, $this->image, 0, 8, 44, 52, 4, 12);

            //body 2 | тело 2
            $this->imageСopyAlpha($newImage, $this->image, 4, 8, 32, 36, 8, 12, $colorAt);
            //right leg 2 | правая нога 2
            $this->imageСopyAlpha($newImage, $this->image, 8, 20, 12, 36, 4, 12, $colorAt);
            //left leg 2 | левая нога 2
            $this->imageСopyAlpha($newImage, $this->image, 4, 20, 12, 52, 4, 12, $colorAt);
            //right arm 2 | правая рука 2
            $this->imageСopyAlpha($newImage, $this->image, 12, 8, 52, 36, 4, 12, $colorAt);
            //left arm 2 | левая рука 2
            $this->imageСopyAlpha($newImage, $this->image, 0, 8, 60, 52, 4, 12, $colorAt);
        } else {
            //left leg | левая нога
            $this->imageFlip($newImage, $this->image, 4, 20, 12, 20, 4, 12);
            //left arm | левая рука
            $this->imageFlip($newImage, $this->image, 0, 8, 52, 20, 4, 12);
        }

        // Scale the image
        if($scale != 1)
            return $this->scaleImage($newImage, $newWidth, $newHeight, is_null($r));

        return $newImage;
    }

    /**
     * Returns an image handle consisting of an (optionally) scaled combined view of the skin.
     * $r, $g, $b are used to construct the background color.
     * ================================================
     * Возвращает дескриптор изображение, опционально увеличенного, с соединёнными передом и задом скина
     * $r, $g, $b используются для задания фонового цвета
     *
     * @param int $scale
     * @param int $r
     * @param int $g
     * @param int $b
     * @return resource
     */
    public function combinedImage ($scale = 1, $r = NULL, $g = NULL, $b = NULL) {
        $newWidth = 32 * $scale;
        $newHeight = 32 * $scale;

        $newImage = $this->createEmptyImage(32, 32, $r, $g, $b);

        $front = $this->frontImage(1, $r, $g, $b);
        $back = $this->backImage(1, $r, $g, $b);
        imagecopy($newImage, $front, 0, 0, 0, 0, 16, 32);
        imagecopy($newImage, $back, 16, 0, 0, 0, 16, 32);

        // Scale the image
        if($scale != 1)
            return $this->scaleImage($newImage, $newWidth, $newHeight, is_null($r));

        return $newImage;
    }

    /**
     * Attempts to compensate for people (incorrectly) filling the head layers with random solid colors
     * Instead of leaving them 100% Alpha.
     * ================================================
     * Пытается скопировать участок, который по ошибке был полностью закрашен в один из цветов,
     * вместо того, чтобы оставить его прозрачным
     *
     * (прим. пер.) скин Нотча имеет чёрную подкладку и если этого не сделать, то голова будет полностью чёрная
     *
     * @param $dst
     * @param $src
     * @param $dst_x
     * @param $dst_y
     * @param $src_x
     * @param $src_y
     * @param $w
     * @param $h
     * @param $bg
     */
    private function imageСopyAlpha($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $w, $h, $bg) {
        if (!$this->isAlpha()) {
            for($i = 0; $i < $w; $i++) {
                for($j = 0; $j < $h; $j++) {

                    $rgb = imagecolorat($src, $src_x + $i, $src_y + $j);

                    if(($rgb & 0xFFFFFF) == ($bg & 0xFFFFFF)) {
                        $alpha = 127;
                    } else {
                        $colors = imagecolorsforindex($src, $rgb);
                        $alpha = $colors["alpha"];
                    }
                    imagecopymerge($dst, $src, $dst_x + $i, $dst_y + $j, $src_x + $i, $src_y + $j, 1, 1, 100 - (($alpha / 127) * 100));
                }
            }
        } else {
            imagecopy($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $w, $h);
        }
    }

    // TODO: phpdoc блок
    private function imageFlip(&$result, &$img, $rx = 0, $ry = 0, $x = 0, $y = 0, $size_x = null, $size_y = null) {
        if ($size_x < 1)
            $size_x = imagesx($img);

        if ($size_y < 1)
            $size_y = imagesy($img);

        imagecopyresampled($result, $img, $rx, $ry, ($x + $size_x - 1), $y, $size_x, $size_y, 0 - $size_x, $size_y);
    }

    public function __destruct() {
        if (!is_null($this->image))
            imagedestroy($this->image);
    }
}
?>