<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run')->daily()->withoutOverlapping();
