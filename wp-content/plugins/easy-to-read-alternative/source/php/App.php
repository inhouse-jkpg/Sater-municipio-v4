<?php

namespace EasyReading;

class App
{
    public function __construct()
    {
        new Admin\Options();
        new Posts\Content();
    }
}
