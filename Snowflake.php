<?php


namespace app\common\lib;

/**
 * File: Snowflake
 * Desc: This is a file description
 * Author: wsr
 * Date: 2021/1/19 23:03
 */
class Snowflake
{
    /**
     * ID 生成策略
     * 毫秒级时间41位+机器ID 10位+毫秒内序列12位。
     * 0           41     51     64
     * +-----------+------+------+
     * |time       |pc    |inc   |
     * +-----------+------+------+
     *  前41bits是以微秒为单位的timestamp。
     *  接着10bits是事先配置好的机器ID。
     *  最后12bits是累加计数器。
     *  macheine id(10bits)标明最多只能有1024台机器同时产生ID，sequence number(12bits)也标明1台机器1ms中最多产生4096个ID，
     */

    private static $workerId;
    private static $maxWorkerId = 1023; // 2^10 - 1

    private static $twepoch = 1288834574657;

    private static $sequence = 0;
    private static $sequenceMask = 4095; // 最大的序列节点 2^12 - 1

    private static $workerIdShift = 12; // 机器ID左移位数, 63 - 41
    private static $timestampLeftShift = 22; // 毫秒时间戳左移位数 63 - 41
    private static $lastTimestamp = -1;

    private static $self = NULL;

    /**
     * @static
     * @return
     */
    public static function getInstance()
    {
        if (self::$self == NULL) {
            self::$self = new self();
        }
        return self::$self;
    }

    public function setWorkId($workId = 1)
    {
        if ($workId > self::$maxWorkerId || $workId < 0) {
            throw new \Exception("worker Id can't be greater than 15 or less than 0");
        }
        self::$workerId = $workId;
        return self::$self;
    }

    function timeGen()
    {
        //获得当前时间戳
        $time = explode(' ', microtime());
        $time2 = substr($time[0], 2, 3);
        return $time[1] . $time2;
    }

    private function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }

        return $timestamp;
    }

    function id()
    {
        $timestamp = $this->timeGen();
        if (self::$lastTimestamp == $timestamp) {
            self::$sequence = (self::$sequence + 1) & self::$sequenceMask;
            if (self::$sequence == 0) {
                $timestamp = $this->tilNextMillis(self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }
        if ($timestamp < self::$lastTimestamp) {
            throw new \Exception("Clock moved backwards.  Refusing to generate id for " . (self::$lastTimestamp - $timestamp) . " milliseconds");
        }
        self::$lastTimestamp = $timestamp;
        return ((sprintf('%.0f', $timestamp) - sprintf('%.0f', self::$twepoch)) << self::$timestampLeftShift) | (self::$workerId << self::$workerIdShift) | self::$sequence;
    }

}