<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class SiteCheckTest implements Rule
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

    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach (static::$testEmails as $testEmail) {

            return $testEmail == $value;
        }

        foreach (static::$testPhones as $testPhone) {

            return $testPhone == $value;
        }
    }

    public function message()
    {
        return [];
    }

    public static function isTest(Request $request): bool
    {
        $isTest = $request->validate([
            'phone' => new SiteCheckTest(),
            'email' => new SiteCheckTest(),
        ]);

        return $isTest['phone'] || $isTest['email'];
    }
}
