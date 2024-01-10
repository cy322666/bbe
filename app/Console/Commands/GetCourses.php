<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $response = Http::get('https://bangbangeducation.ru/api/v4/courses');

        foreach ($response->json()['data'] as $course)
        {
            try {
                Course::query()->updateOrCreate([
                    'course_id' => $course['courseId']
                ], [
                    'name'  => $course['name']['default'],
                    'title' => $course['title']['default'],
                    'slug'  => $course['slug'],
                    'url'   => $course['url'],
                    'type_code' => $course['typeCode'],
                    'has_date'  => $course['hasDate'],
                    'opened_at' => !empty($course['openedAt']['date']) ? $course['openedAt']['date'] : null,
                    'is_new'    => $course['isNew'],
                    'price'     => preg_replace('/[^0-9]/', "", $course['priceOrDiscount']['priceFormatted'])
                ]);
            } catch (\Throwable $e) {

                throw new \Exception($e->getMessage(), $e->getLine());
            }
        }
    }
}
