<?php

namespace citysites\yrl;

/**
 * Class Price
 * @package citysites\yrl
 */
class VideoReview extends NestedObject
{
    protected $youtubeVideoReviewUrl;

    protected $onlineShow = false;

    /**
     * @return bool
     */
    public function getOnlineShow()
    {
        return $this->onlineShow;
    }

    /**
     * @param bool|int $value
     * @return $this
     */
    public function setOnlineShow($value)
    {
        $this->onlineShow = (bool)$value;
        return $this;
    }

    /**
     * @return string
     */
    public function getYoutubeVideoReviewUrl()
    {
        return $this->youtubeVideoReviewUrl;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setYoutubeVideoReviewUrl($value)
    {
        $this->youtubeVideoReviewUrl = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        parent::isValid();
        if (is_null($this->youtubeVideoReviewUrl)) {
            $this->addError('Required video-review field "youtube-video-review-url" is empty');
        }
        return $this->hasErrors();
    }

}
