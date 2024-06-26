<?php


namespace App\Services\amoCRM\Models;


use App\Models\OneC\Pay;
use App\Services\amoCRM\Client;
use Illuminate\Support\Facades\Log;
use Ufee\Amo\Models\Lead;

abstract class Leads
{
    public static function searchByStatus($contact, $client, int $pipeline_id, int $status_id) : ?array
    {
        $leads = [];

        if($contact->leads) {

            foreach ($contact->leads as $lead) {

                if ($lead->status_id == $status_id && $lead->pipeline_id == $pipeline_id) {

                    $lead = $client->service
                        ->leads()
                        ->find($lead->id);

                    $leads = array_merge($leads, $lead);
                }
            }
        }
        return $leads;
    }

    public static function searchSuccess($contact, $client, int|array $pipelines) : Lead|false
    {
        return $contact->leads->filter(function($lead) use ($client, $pipelines) {

            if ($lead->status_id == 142) {

                if (is_array($pipelines)) {

                    if (in_array($lead->pipeline_id, $pipelines))

                        return $lead;

                } elseif ($lead->pipeline_id == $pipelines)

                    return $lead;

                return $lead;
            }
        })->sortBy('created_at', 'DESC')?->first();
    }

    public static function searchSuccessPay($contact, $client, int|array $pipelines, Pay $pay) : Lead|false
    {
        return $contact->leads->filter(function($lead) use ($client, $pipelines) {

            if ($lead->status_id == 142) {

                if (is_array($pipelines)) {

                    if (in_array($lead->pipeline_id, $pipelines)) {

                        if (!Pay::query()->where('lead_id', $lead->id)->exists())

                            return $lead;
                    }


                } elseif ($lead->pipeline_id == $pipelines) {

                    if (!Pay::query()->where('lead_id', $lead->id)->exists())

                        return $lead;
                }

                if (!Pay::query()->where('lead_id', $lead->id)->exists())

                    return $lead;
            }
        })->sortBy('created_at', 'DESC')?->first();
    }

    public static function search($contact, $client, int|array $pipelines = null)
    {
        return $contact->leads->filter(function($lead) use ($client, $pipelines) {

            if ($lead->status_id != 143 &&
                $lead->status_id != 142) {

                if($pipelines != null) {

                    if (is_array($pipelines)) {

                        if (in_array($lead->pipeline_id, $pipelines))

                            return true;

                    } elseif ($lead->pipeline_id == $pipelines)

                        return true;
                } else
                    return true;
            }
        })->sortBy('created_at', 'DESC')?->first();
    }

    public static function searchPay($contact, $client, int|array $pipelines = null, Pay $pay)
    {
        return $contact->leads->filter(function($lead) use ($client, $pipelines) {

            if ($lead->status_id != 143 &&
                $lead->status_id != 142) {

                if($pipelines != null) {

                    if (is_array($pipelines)) {

                        if (in_array($lead->pipeline_id, $pipelines)) {

                            if (!Pay::query()->where('lead_id', $lead->id)->exists())

                                return true;
                        }

                    } elseif ($lead->pipeline_id == $pipelines) {

                        if (!Pay::query()->where('lead_id', $lead->id)->exists())

                            return true;
                    }
                } else
                    if (!Pay::query()->where('lead_id', $lead->id)->exists())

                        return true;
            }
        })->sortBy('created_at', 'DESC')?->first();
    }

    public static function create($contact, array $params, string $leadname)
    {
        $lead = $contact->createLead();

        $lead->name = $leadname;

        if(!empty($params['sale']))
            $lead->sale = $params['sale'];

        if(!empty($params['responsible_user_id']))
            $lead->responsible_user_id = $params['responsible_user_id'];

        if(!empty($params['status_id']))
            $lead->status_id = $params['status_id'];

        $lead->contacts_id = $contact->id;
        $lead->save();

        return $lead;
    }

    public static function update($lead, array $params, array $fields, $amoApi = null)
    {
        try {

            if($lead !== null) {

                if($fields) {

                    foreach ($fields as $key => $field) {

                        $lead->cf($key)->setValue($field);
                    }
                }

                if(!empty($params['responsible_user_id']))
                    $lead->responsible_user_id = $params['responsible_user_id'];

                if(!empty($params['status_id']))
                    $lead->status_id = $params['status_id'];

                $lead->updated_at = time();

                try {

                    $lead->save();

                } catch (\Throwable $e) {

                    if ($amoApi) {

                        $lead = $amoApi->services->leads()->find($lead->id);

                        return static::update($lead, $params, $fields);
                    }
                }

                return $lead;
            }

        } catch (\Exception $exception) {

            Log::error(__METHOD__. ' : ошибка обновления '.$exception->getMessage(). ' , сделка : '.$lead->id);
        }
    }

    public static function get($client, $id) : ?Lead
    {
        try {

            return $client->service->leads()->find($id);

        } catch (\Exception $exception) {

            sleep(2);

            Log::error(__METHOD__. ' : '.$exception->getMessage(). ' , сделка : '.$id);
        }
    }
}
