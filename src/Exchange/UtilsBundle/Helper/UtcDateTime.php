<?php
namespace Exchange\UtilsBundle\Helper;

class UtcDateTime extends \DateTime
{
    /**
     * @param string $timestamp
     * @return \DateTime
     */
    public static function getDateTime($timestamp = 'now')
    {
        $timeZone = new \DateTimeZone('utc');
        return new \DateTime($timestamp, $timeZone);
    }
}
