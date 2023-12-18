<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Course;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;

class UpdateFieldCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:field-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private static array $matchFields = [
        700295 => 'field_base_id',
        700383 => 'field_return_id', //возвраты
        700385 => 'field_agreement_id', //договор
    ];

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (new Client(Account::query()->first()))->init();

        $courses = Course::query()->get();

        foreach (static::$matchFields as $matchFieldId => $matchFieldKey) {

            $enums = $amoApi
                ->service
                ->ajax()
                ->get("/api/v4/leads/custom_fields/$matchFieldId") //TODO fields id
                ->enums;

            $bodyCourses = [];

            foreach ($courses as $course) {

                foreach ($enums as $enum) {

                    if ($course->{$matchFieldKey} == $enum->id ||
                        $course->name == $enum->value) {

                        $bodyCourses[] = [
                            'id'    => $enum->id,
                            'value' => $course->name,
                            'sort'  => $enum->sort,
                        ];

                        $course->{$matchFieldKey} = $enum->id;
                        $course->save();

                        continue 2;
                    }
                }
                $bodyCourses[] = ['value' => $course->name];
            }

            $amoApi
                ->service
                ->ajax()->patch("/api/v4/leads/custom_fields/$matchFieldId", [
                    'enums' => $bodyCourses,
                ], []);
        }
    }
}
