<?php

use Phinx\Seed\AbstractSeed;

class LibraryHoursSeeder extends AbstractSeed
{
    public function run(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $data = [];

        foreach ($days as $day) {
            $isSunday = ($day === 'Sunday');
            $isSaturday = ($day === 'Saturday');

            $data[] = [
                'dayOfWeek' => $day,
                'openingTime' => $isSunday ? NULL : ($isSaturday ? '10:00:00' : '08:00:00'),
                'closingTime' => $isSunday ? NULL : ($isSaturday ? '16:00:00' : '20:00:00'),
                'isClosed' => $isSunday ? 1 : 0,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            ];
        }

        $this->table('library_hours')->insert($data)->saveData();
    }
}
