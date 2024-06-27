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

        //даты 1 и 2 хука
        $startDate = explode(' ', $sla->hook_1)[0];
        $endDate   = explode(' ', $sla->hook_2)[0];

        //если произошло не в один день
        $startDate = $startDate != $endDate ? $endDate : $startDate;

        $hook1 = Carbon::parse($sla->hook_1);
        $hook2 = Carbon::parse($sla->hook_2);

        $startWork = $hook1->format('Y-m-d').' 09:00:00';
        $endWork   = $hook2->format('Y-m-d').' 19:00:00';
//dd($startWork, $hook1->format('Y-m-d H:i:s'));
//dd($hook1->diffInHours($startWork));

        //
        if ($hook1->diffInHours($startWork) < 0) {

            \Illuminate\Support\Facades\Log::info(__METHOD__,[
                'lead_id' => $sla->lead_id,
                'lead created before work'
            ]);

            //лид созданный ночью -> считаем время со старта рабочего дня
            $sla->fill([
                'time_minutes' => $hook2->diffInMinutes($startWork),
                'time_seconds' => $hook1->diffInSeconds($hook2),
            ]);
            $sla->save();


            } else {

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
//            $amoApi = (new Client(Account::query()->first()))
//                ->init()
//                ->initCache();

//            $lead = $amoApi->service->leads()->find($sla->lead_id);
//
//            $lead->cf('SLA')->setValue($sla->time_minutes);
//            $lead->save();

        return Command::SUCCESS;
    }
}
