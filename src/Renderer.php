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
namespace ErickSkrauch\SkinRenderer2D;

class Renderer {
    protected $image = NULL;
    protected $is1_8 = NULL;

    /**
     * @var array|null
     */
    private $alphaColorIndex = null;

    /**
     * @var bool
     */
    private $isSlim = null;

    /**
     * Loads the skin image from a file path
     * ================================================
     * Загржает изображение скина из указанного пути
     *
     * @param string $filePath путь к png файлу скина
     * @return self
     */
    public static function assignSkinFromFile($filePath) {
        $image = imagecreatefrompng($filePath);
        return new static($image);
    }

    /**
     * Loads the skin image from a string
     * ================================================
     * Загружает изображение скина из переданной строки
     *
     * @param $data
     * @return self
     */
    public static function assignSkinFromString($data) {
        $image = imagecreatefromstring($data);
        return new static($image);
    }

    /**
     * @param resource $image дескриптор файла скина
     * @throws \Exception
     */
    public function __construct($image) {
        $this->image = $image;

        if(!$this->image)
            throw new \Exception("PNG image can't be readed.");

        if(!$this->isValid())
            throw new \Exception("Invalid skin image.");
    }

    /**
     * Returns the width of the skin.
     * ================================================
     * Возвращает ширину скина
     *
     * @return int
     */
    public function getWidth() {
        return imagesx($this->image);
    }

    /**
     * Returns the height of the skin.
     * ================================================
     * Возвращает высоту скина
     *
     * @return int
     */
    public function getHeight() {
        return imagesy($this->image);
    }

    /**
     * Returns true if the skin has valid dimensions, false otherwise.
     * Возвращает true, если скин имеет валидные соотношения сторон
     *
     * @return bool
     */
    public function isValid() {
        return $this->getWidth() == 64 && ($this->getHeight() == 32 || $this->getHeight() == 64);
    }

    /**
     * Return true if the skin has 1.8 format
     * Возвращает true, если скин имеет формат 1.8
     *
     * @return bool
     */
    public function is1_8() {
        if (!$this->is1_8)
            $this->is1_8 = $this->getHeight() == 64;

        return $this->is1_8;
    }

    /**
     * Manually sets skin's model type.
     * Устанавливает тип модели скина напрямую.
     *
     * @param $isSlim
     */
    public function setIsSlim($isSlim) {
        $this->isSlim = $isSlim;
    }

    /**
     * Return true if the skin has 1.8 format and slim arms
     * Возвращает true, если скин имеет формат 1.8 и узкие руки (slim)
     *
     * @return bool
     */
    public function isSlim() {
        if (!$this->isSlim) {
            $this->isSlim = $this->is1_8() && $this->isTransparent($this->image, 54, 20);
        }

        return $this->isSlim;
    }

    /**
     * Return an image handle consisting of an (optionally) scaled face view of the skin
     * ================================================
     * Возвращает дескриптор изображения, опционально увеличиенного, с лицом скина
     *
     * @param int $scale
     * @return resource
     */
    public function renderFace($scale = 1) {
        $newWidth = $newHeight = 8 * $scale;

        $newImage = $this->createEmptyImage(8, 8);

        //head | голова
        $this->copyNoAlpha($newImage, $this->image, 0, 0, 8, 8, 8, 8);
        //head mask | маска
        $this->copyAlpha($newImage, $this->image, 0, 0, 40, 8, 8, 8);

        if ($scale != 1) {
            return $this->scaleImage($newImage, $newWidth, $newHeight);
        }

        return $newImage;
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
    public function renderFront($scale = 1, $r = NULL, $g = NULL, $b = NULL) {
        $newWidth = 16 * $scale;
        $newHeight = 32 * $scale;

        $newImage = $this->createEmptyImage(16, 32, $r, $g, $b);

        // head with mask | голова с маской
        $this->copyNoAlpha($newImage, $this->renderFace(), 4, 0, 0, 0, 8, 8);
        //body | тело
        $this->copyNoAlpha($newImage, $this->image, 4, 8, 20, 20, 8, 12);
        //right leg | правая нога
        $this->copyNoAlpha($newImage, $this->image, 4, 20, 4, 20, 4, 12);

        //right arm | правая рука
        if (!$this->isSlim()) {
            $this->copyNoAlpha($newImage, $this->image, 0, 8, 44, 20, 4, 12);
        } else {
            $this->copyNoAlpha($newImage, $this->image, 1, 8, 44, 20, 3, 12);
        }

        // Рендерим элементы в зависимости от версии
        if ($this->is1_8()) {
            //left leg | левая нога
            $this->copyNoAlpha($newImage, $this->image, 8, 20, 20, 52, 4, 12);

            //left arm | левая рука
            if (!$this->isSlim()) {
                $this->copyNoAlpha($newImage, $this->image, 12, 8, 36, 52, 4, 12);
            } else {
                $this->copyNoAlpha($newImage, $this->image, 12, 8, 36, 52, 3, 12);
            }

            //body 2 | тело 2
            $this->copyAlpha($newImage, $this->image, 4, 8, 20, 36, 8, 12);
            //right leg 2 | правая нога 2
            $this->copyAlpha($newImage, $this->image, 4, 20, 4, 36, 4, 12);
            //left leg 2 | левая нога 2
            $this->copyAlpha($newImage, $this->image, 8, 20, 4, 52, 4, 12);

            //right arm 2 | правая рука 2
            if ($this->isSlim()) {
                $this->copyAlpha($newImage, $this->image, 1, 8, 44, 36, 3, 12);
            } else {
                $this->copyAlpha($newImage, $this->image, 0, 8, 44, 36, 4, 12);
            }

            //left arm 2 | левая рука 2
            if ($this->isSlim()) {
                $this->copyAlpha($newImage, $this->image, 12, 8, 52, 52, 3, 12);
            } else {
                $this->copyAlpha($newImage, $this->image, 12, 8, 52, 52, 4, 12);
            }
        } else {
            //left leg | левая нога
            $this->copyNoAlphaAndFlipVertically($newImage, $this->image, 8, 20, 4, 20, 4, 12);
            //left arm | левая рука
            $this->copyNoAlphaAndFlipVertically($newImage, $this->image, 12, 8, 44, 20, 4, 12);
        }

        // Scale the image
        // Изменяем размер изображения
        if ($scale != 1) {
            return $this->scaleImage($newImage, $newWidth, $newHeight, is_null($r));
        }

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
    public function renderBack($scale = 1, $r = NULL, $g = NULL, $b = NULL) {
        $newWidth = 16 * $scale;
        $newHeight = 32 * $scale;

        $newImage = $this->createEmptyImage(16, 32, $r, $g, $b);

        //head | голова
        $this->copyNoAlpha($newImage, $this->image, 4, 0, 24, 8, 8, 8);
        //head mask | маска
        $this->copyAlpha($newImage, $this->image, 4, 0, 56, 8, 8, 8);
        //body | тело
        $this->copyNoAlpha($newImage, $this->image, 4, 8, 32, 20, 8, 12);
        //right leg | правая нога
        $this->copyNoAlpha($newImage, $this->image, 8, 20, 12, 20, 4, 12);

        //right arm | правая рука
        if (!$this->isSlim()) {
            $this->copyNoAlpha($newImage, $this->image, 12, 8, 52, 20, 4, 12);
        } else {
            $this->copyNoAlpha($newImage, $this->image, 12, 8, 51, 20, 3, 12);
        }

        // Рендерим элементы в зависимости от версии
        if ($this->is1_8()) {
            //left leg | левая нога
            $this->copyNoAlpha($newImage, $this->image, 4, 20, 28, 52, 4, 12);

            //left arm | левая рука
            if (!$this->isSlim()) {
                $this->copyNoAlpha($newImage, $this->image, 0, 8, 44, 52, 4, 12);
            } else {
                $this->copyNoAlpha($newImage, $this->image, 1, 8, 43, 52, 3, 12);
            }

            //body 2 | тело 2
            $this->copyAlpha($newImage, $this->image, 4, 8, 32, 36, 8, 12);
            //right leg 2 | правая нога 2
            $this->copyAlpha($newImage, $this->image, 8, 20, 12, 36, 4, 12);
            //left leg 2 | левая нога 2
            $this->copyAlpha($newImage, $this->image, 4, 20, 12, 52, 4, 12);
            //right arm 2 | правая рука 2
            if ($this->isSlim()) {
                $this->copyAlpha($newImage, $this->image, 12, 8, 51, 36, 3, 12);
            } else {
                $this->copyAlpha($newImage, $this->image, 12, 8, 52, 36, 4, 12);
            }

            //left arm 2 | левая рука 2
            if ($this->isSlim()) {
                $this->copyAlpha($newImage, $this->image, 1, 8, 59, 52, 3, 12);
            } else {
                $this->copyAlpha($newImage, $this->image, 0, 8, 60, 52, 4, 12);
            }
        } else {
            //left leg | левая нога
            $this->copyNoAlphaAndFlipVertically($newImage, $this->image, 4, 20, 12, 20, 4, 12);
            //left arm | левая рука
            $this->copyNoAlphaAndFlipVertically($newImage, $this->image, 0, 8, 52, 20, 4, 12);
        }

        // Scale the image
        if ($scale != 1) {
            return $this->scaleImage($newImage, $newWidth, $newHeight, $r === null);
        }

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
    public function renderCombined($scale = 1, $r = NULL, $g = NULL, $b = NULL) {
        $newWidth = $newHeight = 32 * $scale;

        $newImage = $this->createEmptyImage(32, 32, $r, $g, $b);

        $front = $this->renderFront(1, $r, $g, $b);
        $back = $this->renderBack(1, $r, $g, $b);
        imagecopy($newImage, $front, 0, 0, 0, 0, 16, 32);
        imagecopy($newImage, $back, 16, 0, 0, 0, 16, 32);

        // Scale the image
        if($scale != 1)
            return $this->scaleImage($newImage, $newWidth, $newHeight, is_null($r));

        return $newImage;
    }

    /**
     * Degrades skin from the new to the old format
     * ================================================
     * Меняет формат скина с нового на старый
     *
     * @param bool $overlay Наложить ли новые части скина на старый шаблон (возможны артефакты на руках и ногах)
     * @throws \Exception
     * @return resource
     */
    public function degrade($overlay = false) {
        if (!$this->is1_8())
            throw new \Exception("Skin is not in 1.8 format!");

        if ($this->isSlim())
            throw new \Exception("Skin have slim format and can't be convert in old format.");

        $newImage = $this->createEmptyImage(64, 32);
        imagecopy($newImage, $this->image, 0, 0, 0, 0, 64, 32);

        if ($overlay) {
            //left arm 2 | левая рука 2
            imagecopy($newImage, $this->image, 40, 16, 40, 32, 16, 16);
            //left leg 2 | левая нога 2
            imagecopy($newImage, $this->image, 0, 16, 0, 48, 16, 16);
            //body 2 | тело 2
            imagecopy($newImage, $this->image, 16, 16, 16, 32, 24, 16);
        }

        return $newImage;
    }

    /**
     * Improve skin format from the old to the 1.8 non slim format
     * ================================================
     * Меняет формат скина со старого в 1.8 не slim формат
     *
     * @throws \Exception
     * @return resource
     */
    public function improve() {
        if ($this->is1_8())
            throw new \Exception("Skin is already in 1.8 format!");

        $newImage = $this->createEmptyImage(64, 64);
        imagecopy($newImage, $this->image, 0, 0, 0, 0, 64, 32);

        // right arm | правая рука
        imagecopy($newImage, $this->image, 32, 48, 40, 16, 16, 16);

        // right leg | правая нога
        imagecopy($newImage, $this->image, 16, 48, 0, 16, 16, 16);

        return $newImage;
    }

    public function __destruct() {
        if (!is_null($this->image)) {
            imagedestroy($this->image);
        }
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
     * @param int $r
     * @param int $g
     * @param int $b
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

    /**
     * Utility to scale the image given store the transparency
     * ================================================
     * Утилита для масштабирования изображения с учётом прозрачности
     *
     * @param $image
     * @param $newWidth
     * @param $newHeight
     * @param bool $saveAlpha
     * @return resource
     */
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

    private function copyNoAlpha($dst, $src, $dstX, $dstY, $srcX, $srcY, $width, $height) {
        imagefilledrectangle($dst, $dstX, $dstY, $dstX + $width - 1, $dstY + $height - 1, 0x000000);
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                if ($this->isTransparent($src, $srcX + $i, $srcY + $j)) {
                    continue;
                }

                imagecopymerge($dst, $src, $dstX + $i, $dstY + $j, $srcX + $i, $srcY + $j, 1, 1, 100);
            }
        }
    }

    private function copyAlpha($dst, $src, $dstX, $dstY, $srcX, $srcY, $width, $height) {
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                if ($this->isTransparent($src, $srcX + $i, $srcY + $j)) {
                    continue;
                }

                $alpha = imagecolorsforindex($src, imagecolorat($src, $srcX + $i, $srcY + $j))['alpha'];
                imagecopymerge($dst, $src, $dstX + $i, $dstY + $j, $srcX + $i, $srcY + $j, 1, 1, 100 - (($alpha / 127) * 100));
            }
        }
    }

    /**
     * In the old skins format left arm and leg should be reflected
     * ================================================
     * В старом формате скина левые рука и нога должны быть отражены
     */
    private function copyNoAlphaAndFlipVertically($dst, $src, $dstX, $dstY, $srcX, $srcY, $width, $height) {
        imagefilledrectangle($dst, $dstX, $dstY, $dstX + $width - 1, $dstY + $height - 1, 0x000000);
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                if ($this->isTransparent($src, $srcX + $i, $srcY + $j)) {
                    continue;
                }

                imagecopymerge($dst, $src, $dstX + $width - $i - 1, $dstY + $j, $srcX + $i, $srcY + $j, 1, 1, 100);
            }
        }
    }

    /**
     * @return array
     */
    private function getAlphaColor() {
        if ($this->alphaColorIndex === null) {
            $this->alphaColorIndex = imagecolorsforindex($this->image, imagecolorat($this->image, 1, 1));
        }

        return $this->alphaColorIndex;
    }

    /**
     * Test ($x, $y) for having any transparency
     * Проверяет наличие прозврачности в координатах ($x, $y)
     *
     * @param resource $resource
     * @param int $x
     * @param int $y
     * @return bool
     */
    private function isTransparent($resource, $x, $y) {
        $color = imagecolorsforindex($resource, imagecolorat($resource, $x, $y));
        $alphaColor = $this->getAlphaColor();
        if ($alphaColor['alpha'] === 127) {
            return $color['alpha'] === 127;
        }

        return $color['red'] === $alphaColor['red']
            && $color['green'] === $alphaColor['green']
            && $color['blue'] === $alphaColor['blue'];
    }

}
