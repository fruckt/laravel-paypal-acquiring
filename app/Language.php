<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public static function siteLanguages()
    {
        return [
            'ru',
            'en',
        ];
    }

    /**Get languages array
     *
     * @return array
     */
    public static function getArray(){
        $languages = [];
        foreach(self::siteLanguages() as $lang){
            $languages[$lang] = [
                'label' => $lang
                , 'crypt' => \Illuminate\Support\Facades\Crypt::encrypt($lang)
                , 'displayed' => trans('acquiring.languages.'.$lang)
            ];
        }

        return $languages;
    }
}
