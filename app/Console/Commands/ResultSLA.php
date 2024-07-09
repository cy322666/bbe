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
//        $startDate = $startDate != $endDate ? $endDate : $startDate;

        $hook1 = Carbon::parse($sla->hook_1);
        $hook2 = Carbon::parse($sla->hook_2);

        //рабочее время потупления заявки
        $startWork = Carbon::parse($hook1->format('Y-m-d').' 10:00:00');
        $endWork   = Carbon::parse($hook1->format('Y-m-d').' 19:00:00');
//dd($startWork, $hook1->format('Y-m-d H:i:s'));
//dd($hook1->diffInHours($startWork));

        //пришла до рабочего дня
        if ($hook1 < $startWork) {

            \Illuminate\Support\Facades\Log::info(__METHOD__, [
                'lead_id' => $sla->lead_id,
                'заявка пришла до рабочего дня'
            ]);

            if ($startWork < $hook2) {

                \Illuminate\Support\Facades\Log::info(__METHOD__, [
                    'lead_id' => $sla->lead_id,
                    'заявка принята в рабочее время'
                ]);

                $sla->fill([
                    'time_minutes' => $hook2->diffInMinutes($startWork),
                    'time_seconds' => $hook1->diffInSeconds($hook2),
                ]);
                $sla->save();
            } else {
                \Illuminate\Support\Facades\Log::info(__METHOD__, [
                    'lead_id' => $sla->lead_id,
                    'заявка принята в нерабочее время'
                ]);

                $sla->fill([
                    'time_minutes' => 1,
                    'time_seconds' => 1,
                ]);
                $sla->save();
            }
        } elseif($hook1 < $endWork) {

            \Illuminate\Support\Facades\Log::info(__METHOD__, [
                'lead_id' => $sla->lead_id,
                'заявка пришла в рабочее время'
            ]);

            if ($hook2 < $endWork) {

                \Illuminate\Support\Facades\Log::info(__METHOD__, [
                    'lead_id' => $sla->lead_id,
                    'заявка принята в рабочее время'
                ]);

                $sla->fill([
                    'time_minutes' => $hook2->diffInMinutes($hook1),
                    'time_seconds' => $hook1->diffInSeconds($hook2),
                ]);
                $sla->save();

            } else {

                \Illuminate\Support\Facades\Log::info(__METHOD__, [
                    'lead_id' => $sla->lead_id,
                    'заявка принята после рабочего дня'
                ]);

                //проверка на принятие на след день
                if ($startDate != $endDate) {

                    \Illuminate\Support\Facades\Log::info(__METHOD__, [
                        'lead_id' => $sla->lead_id,
                        'заявка принята и пришла в разные дни'
                    ]);

                    $startWork = Carbon::parse($hook2->format('Y-m-d').' 10:00:00');
                    $endWork   = Carbon::parse($hook2->format('Y-m-d').' 19:00:00');

                    if ($hook2 < $startWork) {

                        \Illuminate\Support\Facades\Log::info(__METHOD__, [
                            'lead_id' => $sla->lead_id,
                            'заявка взята до рабочего дня'
                        ]);

                        $sla->fill([
                            'time_minutes' => 1,
                            'time_seconds' => 1,
                        ]);
                        $sla->save();

                    } elseif ($hook2 < $endWork) {

                        \Illuminate\Support\Facades\Log::info(__METHOD__, [
                            'lead_id' => $sla->lead_id,
                            'заявка взята в рабочее время'
                        ]);

                        $sla->fill([
                            'time_minutes' => $hook2->diffInMinutes($startWork),
                            'time_seconds' => $hook2->diffInSeconds($startWork),
                        ]);
                        $sla->save();

                    } elseif ($hook2 > $endWork) {

                        \Illuminate\Support\Facades\Log::info(__METHOD__, [
                            'lead_id' => $sla->lead_id,
                            'заявка взята после рабочего дня'
                        ]);

                        $sla->fill([
                            'time_minutes' => $hook2->diffInMinutes($startWork),
                            'time_seconds' => $hook2->diffInSeconds($startWork),
                        ]);
                        $sla->save();
                    }

                } else {

                    $sla->fill([
                        'time_minutes' => $hook1->diffInMinutes($hook2),
                        'time_seconds' => $hook1->diffInSeconds($hook2),
                    ]);
                    $sla->save();
                }
            }
        } else {

            \Illuminate\Support\Facades\Log::info(__METHOD__, [
                'lead_id' => $sla->lead_id,
                'заявка пришла после рабочего дня'
            ]);

            if ($startDate != $endDate) {

                \Illuminate\Support\Facades\Log::info(__METHOD__, [
                    'lead_id' => $sla->lead_id,
                    'заявка пришла и взята в разные дни'
                ]);

                $startWork = Carbon::parse($hook2->format('Y-m-d').' 10:00:00');
                $endWork   = Carbon::parse($hook2->format('Y-m-d').' 19:00:00');

                if ($hook2 < $startWork) {

                    \Illuminate\Support\Facades\Log::info(__METHOD__, [
                        'lead_id' => $sla->lead_id,
                        'заявка взята до рабочего дня'
                    ]);

                    $sla->fill([
                        'time_minutes' => 1,
                        'time_seconds' => 1,
                    ]);
                    $sla->save();

                } elseif ($hook2 < $endWork) {

                    \Illuminate\Support\Facades\Log::info(__METHOD__, [
                        'lead_id' => $sla->lead_id,
                        'заявка взята в рабочее время'
                    ]);

                    $sla->fill([
                        'time_minutes' => $hook2->diffInMinutes($startWork),
                        'time_seconds' => $hook2->diffInSeconds($startWork),
                    ]);
                    $sla->save();

                } elseif ($hook2 > $endWork) {

                    \Illuminate\Support\Facades\Log::info(__METHOD__, [
                        'lead_id' => $sla->lead_id,
                        'заявка взята после рабочего дня'
                    ]);

                    $sla->fill([
                        'time_minutes' => $hook2->diffInMinutes($startWork),
                        'time_seconds' => $hook2->diffInSeconds($startWork),
                    ]);
                    $sla->save();
                }

            } else {

                if ($hook2 > $endWork) {

                    \Illuminate\Support\Facades\Log::info(__METHOD__, [
                        'lead_id' => $sla->lead_id,
                        'заявка принята в нерабочее время'
                    ]);

                    $sla->fill([
                        'time_minutes' => $hook1->diffInMinutes($hook2),
                        'time_seconds' => $hook1->diffInSeconds($hook2),
                    ]);
                    $sla->save();
                }
            }
        }

        //TODO кусками даты
        //TODO перевод во взятие не на тот этап

        $amoApi = (new Client(Account::query()->first()))
            ->init()
            ->initCache();

        $lead = $amoApi->service->leads()->find($sla->lead_id);

        $lead->cf('SLA')->setValue($sla->time_minutes);
        $lead->save();

        return Command::SUCCESS;
    }
}
