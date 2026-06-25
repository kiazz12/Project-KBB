<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --frequency=daily')->daily();
Schedule::command('backup:run --frequency=weekly')->weekly();
Schedule::command('backup:run --frequency=monthly')->monthly();
Schedule::command('backup:run --frequency=yearly')->yearly();
