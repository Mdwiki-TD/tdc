<?php

namespace Controllers\Admin;

class ReportsController
{
    /**
     * Get the statistics for the reports filter dropdowns.
     * In a real application, this data would be fetched from the database.
     * For now, we are returning mocked data.
     */
    public static function getReportsStats()
    {
        // --- MOCKED DATA ---
        return [
            ['year' => '2023', 'month' => '12', 'user' => 'User1', 'lang' => 'en', 'result' => 'success'],
            ['year' => '2023', 'month' => '11', 'user' => 'User2', 'lang' => 'fr', 'result' => 'failure'],
            ['year' => '2024', 'month' => '01', 'user' => 'User1', 'lang' => 'es', 'result' => 'success'],
        ];
    }

    /**
     * Get the reports data based on the provided filters.
     * In a real application, this data would be fetched from the database
     * and filtered based on the $params.
     * For now, we are returning mocked data.
     */
    public static function getReportsData($params = [])
    {
        // The $params array would contain the filter values, e.g., ['year' => '2023', 'lang' => 'en']
        // We are ignoring the params for now and returning a fixed set of mocked data.

        // --- MOCKED DATA ---
        $data = [
            [
                'id' => 1,
                'date' => '2023-12-01 10:00:00',
                'lang' => 'en',
                'title' => 'Example Page 1',
                'user' => 'User1',
                'sourcetitle' => 'Source Page 1',
                'result' => 'success.json',
                'data' => json_encode(['key' => 'value1']),
            ],
            [
                'id' => 2,
                'date' => '2023-11-15 12:30:00',
                'lang' => 'fr',
                'title' => 'Example Page 2',
                'user' => 'User2',
                'sourcetitle' => 'Source Page 2',
                'result' => 'failure.json',
                'data' => json_encode(['key' => 'value2']),
            ],
            [
                'id' => 3,
                'date' => '2024-01-20 15:00:00',
                'lang' => 'es',
                'title' => 'Example Page 3',
                'user' => 'User1',
                'sourcetitle' => 'Source Page 3',
                'result' => 'success.json',
                'data' => json_encode(['key' => 'value3']),
            ],
        ];

        return [
            'results' => $data,
        ];
    }
}
