<?php

namespace Database\Seeders;

use App\Models\AcademicEvent;
use Illuminate\Database\Seeder;

class AcademicCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            // ──────────────────────────────────────────
            // 2025 PUBLIC HOLIDAYS & ACADEMIC DATES
            // ──────────────────────────────────────────
            [
                'title'       => "New Year's Day",
                'description' => "Official public holiday celebrating the first day of the Gregorian year.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-01-01',
                'end_date'    => '2025-01-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Chinese New Year",
                'description' => "Spring Festival national holiday.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-01-29',
                'end_date'    => '2025-01-30',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Thaipusam",
                'description' => "Celebrated during the full moon in the Tamil month of Thai.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-02-11',
                'end_date'    => '2025-02-11',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Hari Raya Aidilfitri",
                'description' => "Eid al-Fitr marks the end of Ramadan.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-03-31',
                'end_date'    => '2025-04-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Labour Day",
                'description' => "National Worker's Holiday.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-05-01',
                'end_date'    => '2025-05-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Wesak Day",
                'description' => "Commemorating the birth, enlightenment, and death of Buddha.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-05-12',
                'end_date'    => '2025-05-12',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "King's Birthday (Yang di-Pertuan Agong)",
                'description' => "Official birthday of His Majesty the Yang di-Pertuan Agong.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-06-02',
                'end_date'    => '2025-06-02',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Hari Raya Haji",
                'description' => "Feast of Sacrifice (Eid al-Adha).",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-06-07',
                'end_date'    => '2025-06-07',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Awal Muharram (Maal Hijrah)",
                'description' => "Islamic New Year 1447H.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-06-27',
                'end_date'    => '2025-06-27',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "National Day (Hari Merdeka)",
                'description' => "Commemoration of the Malayan Declaration of Independence 1957.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-08-31',
                'end_date'    => '2025-08-31',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Malaysia Day",
                'description' => "Commemorates the establishment of the Malaysian federation.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-09-16',
                'end_date'    => '2025-09-16',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Deepavali",
                'description' => "Festival of Lights.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-10-20',
                'end_date'    => '2025-10-20',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Christmas Day",
                'description' => "National Christmas Holiday.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2025-12-25',
                'end_date'    => '2025-12-25',
                'color'       => '#ef4444',
            ],

            // ──────────────────────────────────────────
            // 2026 PUBLIC HOLIDAYS & ACADEMIC DATES
            // ──────────────────────────────────────────
            [
                'title'       => "New Year's Day",
                'description' => "First day of the Gregorian year 2026.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-01-01',
                'end_date'    => '2026-01-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Chinese New Year",
                'description' => "Year of the Horse - National Holiday.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-02-17',
                'end_date'    => '2026-02-18',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Hari Raya Aidilfitri",
                'description' => "Celebration concluding Ramadan 1447H.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-03-20',
                'end_date'    => '2026-03-21',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Labour Day",
                'description' => "National Workers' Day.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-05-01',
                'end_date'    => '2026-05-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Wesak Day",
                'description' => "Buddha Day celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-05-31',
                'end_date'    => '2026-05-31',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Agong's Official Birthday",
                'description' => "Official birthday of the Yang di-Pertuan Agong.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-06-01',
                'end_date'    => '2026-06-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "National Day (Hari Merdeka)",
                'description' => "69th National Day of Malaysia.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-08-31',
                'end_date'    => '2026-08-31',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Malaysia Day",
                'description' => "Federation of Malaysia celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-09-16',
                'end_date'    => '2026-09-16',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Mid-Term Examination Week",
                'description' => "Institution-wide mid-term revision and mock examination window.",
                'event_type'  => 'exam_period',
                'start_date'  => '2026-09-21',
                'end_date'    => '2026-09-25',
                'color'       => '#8b5cf6',
            ],
            [
                'title'       => "Deepavali",
                'description' => "Festival of Lights celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-11-08',
                'end_date'    => '2026-11-08',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Annual Final Exams Window",
                'description' => "Semester final assessments, Cambridge IGCSE & SPM mock sprints.",
                'event_type'  => 'exam_period',
                'start_date'  => '2026-11-23',
                'end_date'    => '2026-12-04',
                'color'       => '#8b5cf6',
            ],
            [
                'title'       => "Year-End School Holidays",
                'description' => "MOE Official Term 3 Year-End School Break.",
                'event_type'  => 'school_break',
                'start_date'  => '2026-12-19',
                'end_date'    => '2027-01-10',
                'color'       => '#f59e0b',
            ],
            [
                'title'       => "Christmas Day",
                'description' => "Christmas celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2026-12-25',
                'end_date'    => '2026-12-25',
                'color'       => '#ef4444',
            ],

            // ──────────────────────────────────────────
            // 2027 PUBLIC HOLIDAYS & ACADEMIC DATES
            // ──────────────────────────────────────────
            [
                'title'       => "New Year's Day 2027",
                'description' => "New Year celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-01-01',
                'end_date'    => '2027-01-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Academic Term 1 Begins",
                'description' => "Commencement of 2027 academic year and new masterclasses.",
                'event_type'  => 'academic_term',
                'start_date'  => '2027-01-11',
                'end_date'    => '2027-01-11',
                'color'       => '#3b82f6',
            ],
            [
                'title'       => "Chinese New Year",
                'description' => "Lunar New Year 2027.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-02-06',
                'end_date'    => '2027-02-07',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Hari Raya Aidilfitri",
                'description' => "Hari Raya celebration 1448H.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-03-10',
                'end_date'    => '2027-03-11',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Labour Day",
                'description' => "Workers' Day.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-05-01',
                'end_date'    => '2027-05-01',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "National Day (Hari Merdeka)",
                'description' => "70th National Day.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-08-31',
                'end_date'    => '2027-08-31',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Malaysia Day",
                'description' => "Malaysia Day celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-09-16',
                'end_date'    => '2027-09-16',
                'color'       => '#ef4444',
            ],
            [
                'title'       => "Christmas Day",
                'description' => "Christmas celebration.",
                'event_type'  => 'public_holiday',
                'start_date'  => '2027-12-25',
                'end_date'    => '2027-12-25',
                'color'       => '#ef4444',
            ],
        ];

        foreach ($events as $evt) {
            AcademicEvent::firstOrCreate(
                [
                    'title'      => $evt['title'],
                    'start_date' => $evt['start_date'],
                ],
                $evt
            );
        }
    }
}
