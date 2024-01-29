<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Log;
use App\Models\Sla;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResultSLA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:result {sla}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $sla = Sla::query()->find($this->argument('sla'));

//        $sla = Sla::query()->updateOrCreate([
//            'lead_id' => $sla->lead_id,
//        ], [
//            'hook_2' => Carbon::now()
//                ->timezone('Europe/Moscow')
//                ->format('Y-m-d H:i:s')
//        ]);

        if ($sla->hook_1) {

            $hook1 = Carbon::parse($sla->hook_1);
            $hook2 = Carbon::parse($sla->hook_2);

//            dd($hook1->format('Y-m-d H:i:s'));

            //высчитываем разницу от рабочего времени снизу и сверху
            $start = Carbon::parse($hook1->format('Y-m-d').' 10:00:00');

            $end = Carbon::parse(
                Carbon::now()
                    ->timezone('Europe/Moscow')
                    ->format('Y-m-d').' 19:00:00'
            )->timezone('Europe/Moscow');

            if ($hook1->diffInHours($start)) {

                \Illuminate\Support\Facades\Log::info(__METHOD__,[
                    'lead_id' => $sla->lead_id,
                    'lead created before work'
                ]);
                //лид созданный ночью -> считаем время со старта рабочего дня
                $sla->fill([
                    'time_minutes' => $hook2->diffInMinutes($start),
                    'time_seconds' => $hook1->diffInSeconds($hook2),
                ]);
                $sla->save();
            } else {
//dd($hook1->format('Y-m-d H:i:s').' - '.$hook1->diffInHours($start).' - '.$start->format('Y-m-d H:i:s'));
//dd($hook1->diffInHours($start));
                \Illuminate\Support\Facades\Log::info(__METHOD__,[
                    'lead_id' => $sla->lead_id,
                    'lead created in work'
                ]);

                $sla->fill([
                    'time_minutes' => $hook1->diffInMinutes($hook2),
                    'time_seconds' => $hook1->diffInSeconds($hook2),
                ]);
                $sla->save();
            }
            $amoApi = (new Client(Account::query()->first()))
                ->init()
                ->initCache();

            $lead = $amoApi->service->leads()->find($sla->lead_id);

            $lead->cf('SLA')->setValue($sla->time_minutes);
            $lead->save();

            return Command::SUCCESS;
        }
    }
}
