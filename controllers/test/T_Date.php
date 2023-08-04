<?php namespace Controllers\test;

class Date extends _controller
{

    public function humanDate($date = '2020-01-10 20:35:00')
    {
        return \Inc\Date::ins($date)->humanDate();
    }

    public function humanTime($date = '2020-01-10 20:35:00')
    {
        return \Inc\Date::ins($date)->humanTime();
    }

    public function humanDatetime($date = '2020-01-10 20:35:00')
    {
        return \Inc\Date::ins($date)->humanDatetime();
    }

    public function verboseDatetime($date = '2020-01-10 20:35:00')
    {
        return \Inc\Date::ins($date)->verboseDatetime();
    }

    public function verboseDate($date = '2020-01-10 20:35:00')
    {
        return \Inc\Date::ins($date)->verboseDate();
    }

    public function ago($date = '2020-01-10 20:35:00', bool $short = false)
    {
        return \Inc\Date::ins($date)->ago($short);
    }

    public function age($date = '1992-10-25')
    {
        return \Inc\Date::ins($date)->age();
    }

    public function sAge($date = '1992-10-25')
    {
        return \Inc\Date::ins($date)->sAge();
    }

}