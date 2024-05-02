<?php

namespace App\Http\Controllers;

use App\Models\Hubspot\Site;
use Illuminate\Support\Facades\Artisan;

class HubspotController extends Controller
{
    public function cron1()
    {
        $uuids = [
            'd63fd44c-af4a-4a0e-bb35-63eb79a542ef',

            '4c10b4fa-7fac-4c43-88e7-b074180b43dc',//+
//            '6925f713-ef2f-4d81-a0fa-7b605ac605d1',//+
            'eda7bd55-145f-4a27-92c3-e3b992397674',//+
            '24da75d3-e794-47b7-bb45-06c81d8a9597',//+
            'e56687c0-1141-49c7-a706-2fa2941322c8',//+
            '0af9faf3-c056-4023-8919-e654a384e21f',//+
            '77c0f622-3bb6-4412-b0b2-c46d424f7171',//+
//            'b77eff0e-3661-4d48-b2d9-0757b35adb1d',
        ];

        foreach ($uuids as $uuid) {

            Artisan::call('hubspot:get', [
                'form' => $uuid,
                'type' => 'cron1'
            ]);
        }
    }

    public function cron2()
    {
        $uuids = [
            '9944e7b6-1db4-422b-a839-ed9d020126fc',
            'b77eff0e-3661-4d48-b2d9-0757b35adb1d',
            'd08571c8-0c65-473e-ace7-597ad70321f8',
            'fabc184d-eff7-4d4e-b370-63ce9f5f1b99',
            '0fa8c3bc-6411-4efb-b21f-a8598ae2096c',
            '09658544-d246-47f3-bea2-3d9f0bcb266d',
            '72eb7983-5a90-4061-a92f-19304a9450ca',
            '7938618a-f89c-49db-83f4-18823db33259',
            '2821cfc4-d327-4ea4-b3b0-accedbbbc551',
            'b170c128-18fd-4157-a714-77977f377b70',
            '7f14eef7-c46b-4051-ad1d-557ce3d280e4',//+
            '5a21b267-6531-44c3-a476-7cd4aae9260f',//+
            '9be94507-6b84-441f-9648-9238fe26ff93',//+
        ];

        foreach ($uuids as $uuid) {

            Artisan::call('hubspot:get', [
                'form' => $uuid,
                'type' => 'cron2'
            ]);
        }
    }

    public function cron3()
    {
        //крайние формы от алекса
        $uuids = [
            '3f4cc224-7bd6-4dbe-a52b-7a145c436d9e',
            'a226fb88-ccbf-49ed-a617-8f7e4fc02412',
            '36920db7-a258-4f06-bd36-c9385147e956',
            '6edfaf7d-39ff-4857-941e-e98b30fc3828',
            '4e034ae3-22e8-4212-9e03-9de547c97ad1',
            '9bd28df2-7a96-464d-b300-45d8a68d60ce',
            'bc127ef2-14df-4955-bb8c-900ad1e5dc10',
            '172fdf59-fdee-4a6f-aed2-9cec7f8bc4e6',

            '2f693ff5-0cb2-4ef6-981f-b1ebe544590f',
            'ec00fc1c-2c9f-4bcb-ad49-7695a786b624',
        ];

        foreach ($uuids as $uuid) {

            Artisan::call('hubspot:get', [
                'form' => $uuid,
                'type' => 'cron3'
            ]);
        }
    }

    public function cron4()
    {
        $uuids = [];

        foreach ($uuids as $uuid) {

            Artisan::call('hubspot:get', ['form' => $uuid]);
        }
    }

    public function send()
    {
        $uuids = [
            'd63fd44c-af4a-4a0e-bb35-63eb79a542ef',

            '9944e7b6-1db4-422b-a839-ed9d020126fc',
            'b77eff0e-3661-4d48-b2d9-0757b35adb1d',
            'd08571c8-0c65-473e-ace7-597ad70321f8',
            'fabc184d-eff7-4d4e-b370-63ce9f5f1b99',//+
            '0fa8c3bc-6411-4efb-b21f-a8598ae2096c',
            '09658544-d246-47f3-bea2-3d9f0bcb266d',
            '72eb7983-5a90-4061-a92f-19304a9450ca',
            '7938618a-f89c-49db-83f4-18823db33259',
            '2821cfc4-d327-4ea4-b3b0-accedbbbc551',
            'b170c128-18fd-4157-a714-77977f377b70',

            '5a21b267-6531-44c3-a476-7cd4aae9260f',//+
            '77c0f622-3bb6-4412-b0b2-c46d424f7171',//+
            'eda7bd55-145f-4a27-92c3-e3b992397674',//+
            '24da75d3-e794-47b7-bb45-06c81d8a9597',//+
            'e56687c0-1141-49c7-a706-2fa2941322c8',//+
            '0af9faf3-c056-4023-8919-e654a384e21f',//+

            '4c10b4fa-7fac-4c43-88e7-b074180b43dc',//+
            '7f14eef7-c46b-4051-ad1d-557ce3d280e4',//+
            '9be94507-6b84-441f-9648-9238fe26ff93',//+
//            '6925f713-ef2f-4d81-a0fa-7b605ac605d1',//+
            '3f4cc224-7bd6-4dbe-a52b-7a145c436d9e',
            'a226fb88-ccbf-49ed-a617-8f7e4fc02412',
            '36920db7-a258-4f06-bd36-c9385147e956',
            '6edfaf7d-39ff-4857-941e-e98b30fc3828',
            '4e034ae3-22e8-4212-9e03-9de547c97ad1',
            '9bd28df2-7a96-464d-b300-45d8a68d60ce',
            'bc127ef2-14df-4955-bb8c-900ad1e5dc10',
            '172fdf59-fdee-4a6f-aed2-9cec7f8bc4e6',
        ];

        foreach ($uuids as $uuid) {

            $sites = Site::query()
                ->where('status', 0)
                ->where('form', $uuid)
                ->limit(5)
                ->get();

            foreach ($sites as $site) {

                Artisan::call('hubspot:send', ['site' => $site->id]);
            }
        }
    }

    public function getBroken()
    {
        Artisan::call('hubspot:get-broken', ['form' => 'c8970cb9-5052-437e-939b-94f388a92312']);
    }

    public function pushBroken()
    {
        Artisan::call('hubspot:send-broken');
    }

    public function getSegmentPython()
    {
        Artisan::call('hubspot:get-segment-python');
    }

    public function sendSegmentPython()
    {
        Artisan::call('hubspot:send-segment-python');
    }
}
