<?php

namespace App\Rules;

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

        'Ck9380795@gmail.com',
        'ck9380795@gmail.com',
    ];

    private static array $testPhones = [
        '+643634364',
        '+71111111',
        '+11111111',
        '81112223344',
        '71112223344',
        '91990537349',
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $value
     * @return bool
     */
    public static function validate(string $value): bool
    {
        foreach (static::$testEmails as $testEmail) {

            if ($testEmail == $value || strripos($value, $testEmail) !== false)

                return true;
        }

        foreach (static::$testPhones as $testPhone) {

            if ($testPhone == $value || strripos($value, $testPhone) !== false)

                return true;
        }

        return false;
    }

    public static function isTest(Request $request): bool
    {
        $isTest = [
            'phone' => static::validate($request->phone),
            'email' => static::validate($request->email)
        ];

        return $isTest['phone'] || $isTest['email'];
    }
}
