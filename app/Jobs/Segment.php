<?php

namespace App\Jobs;

use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Segment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Segment $segment) {}

    //главная воронка
    private static int $pipleineId = 3342043;

    public function handle()
    {
        $this->amoApi = '';

        $isDouble = false;

        $lead = $this->amoApi
            ->service
            ->leads()
            ->find($this->segment->lead_id);

        if ($lead->contact !== null) {

            $leadsArray = [
                'count_active'  => 0,
                'count_lost'    => 0,
                'count_success' => 0,
                'sale' => 0,
            ];

            $contact = $lead->contact;

            foreach ($contact->leads->toArray() as $lead) {

                $leadsArray[$lead['pipeline_id']]['count_active']  += $lead['status_id'] != 142 && $lead['status_id'] != 143 ? 1 : 0;
                $leadsArray[$lead['pipeline_id']]['count_lost']    += $lead['status_id'] == 143 ? 1 : 0;
                $leadsArray[$lead['pipeline_id']]['count_success'] += $lead['status_id'] == 142 ? 1 : 0;

                $leadsArray['sale'] += $lead['status_id'] == 142 ? $lead['sale'] : 0;
            }
        }

        dd($leadsArray);

        $this->segment->fill([
            'contact_id'  => $contact->id ?? null,
            'count_leads' => !empty($contact) ? count($contact->leads->toArray()) : 1,
            'sale'        => $leadsArray[static::$investPipelineId]['sale'] + $leadsArray[static::$apartPipelineId]['sale'],
        ]);
        $this->segment->save();

//        $note = $lead->createNote(4);
//        $note->text = $doubleText;
//        $note->element_type = 2;
//        $note->element_id = $lead->id;
//        $note->save();
    }

    private static function buildTextApart(Segment $segment) : array
    {
        return [
            '',
            'Апартаменты : ',
            '---------------------------',
            'Куплено на сумму : '.$segment->sale_apart,
            'Количество сделок : '.$segment->count_leads_apart,
            'Сделок в работе : '.$segment->count_active_apart,
            'Сделок реализовано : '.$segment->count_success_apart,
            'Сделок не реализовано : '.$segment->count_lost_apart,
        ];
    }

    private static function getCountSuccess(array $arrayLeads, $pipelineId): int
    {
        $countSuccess = 0;

        if (!empty($arrayLeads[$pipelineId])) {

            foreach ($arrayLeads[$pipelineId] as $leadArray) {
                try {
                    $countSuccess =+ $leadArray['status_id'] == 142 ? 1 : 0;

                } catch (\Throwable $exception) {}
            }
        }

        return $countSuccess;
    }
}
