<?php

namespace App\Rules;

use App\Services\amoCRM\Models\Contacts;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class SiteCheckTest
{
    private static array $testEmails = [
        'edokovdmitrii@gmail.com',
        'edokov98@mail.ru',
        'e_dmtr@mail.ru',
        'test@test.ru',
        'test@test.com',
        'd-zhigulin@bangbangeducation.ru',
        'igorevna.vasilisa@gmail.com',
        'v.karelova@bangbangeducation.ru',
        'testov@mail.ru',
        'testov@test.ru',
        'd-zhigulin@mail.ru',
        'tolstoy@yandex.ru',
        'test8@advcake.com',
        'test@mail.ru',
        'michail.vladimirsky@yandex.ru',
        '6rustavelli6@mail.ru',
        'ir@bangbangeducation.ru',
        'test@mIl.ru',
        'v.karelova+test998@bangbangeducation.ru',
        'anna@resonatehq.com',
        'testadmitad@gmail.com',

        'Ck9380795@gmail.com',
        'ck9380795@gmail.com',

        'obidcho@mail.ru',
        'obidcho123@mail.ru',
        'ametov.erfan@mail.ru',
        '3aebalsyauzhe@gmail.com',
        '15798i96@gmail.com',
        'mumu@mail.ru',
        'mumka@mail.ru',
        '15798i@gmail.com',
        '15961@gmail.com',
    ];

    private static array $testPhones = [
        '643634364',
        '1111111',
        '1112223344',
        '1990537349',

        '9269432154',
        '9661407001',
        '9855307490',
        '9218621746',
        '7494877208',
        '7475245052',
        '7053916006',
    ];

    private static array $testNames = [
        'тест',
        'Тест',
        'test',
        'Test',
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     * @return bool
     */
    public static function validate(?string $value): bool
    {
        foreach (static::$testEmails as $testEmail) {

            if (strripos($value, $testEmail) !== false)

                return true;
        }

        foreach (static::$testPhones as $testPhone) {

            $value = Contacts::clearPhone($value);

            if (strripos($value, $testPhone) !== false)

                return true;
        }

        foreach (static::$testNames as $testName) {

            if (strripos($value, $testName) !== false)

                return true;
        }

        return false;
    }

    public static function isTest($request): bool
    {
        $isTest = [
            'phone' => static::validate($request->phone),
            'email' => static::validate($request->email),
            'name'  => static::validate($request->firstname),
        ];

        return $isTest['phone'] || $isTest['email'] || $isTest['name'];
    }
}
