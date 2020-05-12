<?php

namespace citysites\yrl\offer;

/**
 * Class HouseOffer
 * @package citysites\yrl\offer
 */
class HouseOffer extends BaseOffer
{
    protected $alarm;

    /**
     * @return string
     */
    public function getAlarm()
    {
        return $this->alarm;
    }

    /**
     * @param string $alarm
     * @return $this
     */
    public function setAlarm($alarm)
    {
        $this->alarm = $alarm;
        return $this;
    }
}
