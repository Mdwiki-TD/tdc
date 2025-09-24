<?php

namespace Controllers;

use function SQLorAPI\Process\get_process_all_new;

class ProcessController
{
    public static function getProcessData()
    {
        $process_data = get_process_all_new();

        // We can add any additional processing here if needed.
        // For now, we just return the raw data from the database function.

        return $process_data;
    }
}
