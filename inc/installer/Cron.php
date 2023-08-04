<?php namespace Inc\installer;

class Cron
{
    public $hour;
    public $minute;
    public $day_of_month;
    public $month;
    public $day_of_week;
    public $command;

    /**
     * Cron constructor.
     * @param $hour
     * @param $minute
     * @param $day_of_month
     * @param $month
     * @param $day_of_week
     * @param $command
     */
    public function __construct($hour, $minute, $day_of_month, $month, $day_of_week, $command)
    {
        $this->hour = $hour;
        $this->minute = $minute;
        $this->day_of_month = $day_of_month;
        $this->month = $month;
        $this->day_of_week = $day_of_week;
        $this->command = $command;
    }

    public function build()
    {
        return "$this->hour $this->minute $this->day_of_month $this->month $this->day_of_week $this->command";
    }

    # predefinidos
    static function daily($hour, $command)
    {
        return new Cron('0', $hour, '*', '*', '*', $command);
    }

}