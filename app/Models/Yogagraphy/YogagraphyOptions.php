<?php


namespace App\Models\Yogagraphy;


class YogagraphyOptions
{
    private $type;
    private $backgroundWidth = 300;
    private $backgroundHeight = 131;
    private $yogaImgWidth = 50;
    private $yogaImgHeight = 50;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }


    /**
     * @return int
     */
    public function getBackgroundWidth(): int
    {
        return $this->backgroundWidth;
    }

    /**
     * @param int $backgroundWidth
     */
    public function setBackgroundWidth(int $backgroundWidth): void
    {
        $this->backgroundWidth = $backgroundWidth;
    }

    /**
     * @return int
     */
    public function getBackgroundHeight(): int
    {
        return $this->backgroundHeight;
    }

    /**
     * @param int $backgroundHeight
     */
    public function setBackgroundHeight(int $backgroundHeight): void
    {
        $this->backgroundHeight = $backgroundHeight;
    }

    /**
     * @return int
     */
    public function getYogaImgWidth(): int
    {
        return $this->yogaImgWidth;
    }

    /**
     * @param int $yogaImgWidth
     */
    public function setYogaImgWidth(int $yogaImgWidth): void
    {
        $this->yogaImgWidth = $yogaImgWidth;
    }

    /**
     * @return int
     */
    public function getYogaImgHeight(): int
    {
        return $this->yogaImgHeight;
    }

    /**
     * @param int $yogaImgHeight
     */
    public function setYogaImgHeight(int $yogaImgHeight): void
    {
        $this->yogaImgHeight = $yogaImgHeight;
    }




}
