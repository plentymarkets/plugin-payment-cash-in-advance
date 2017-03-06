<?php
/**
 * Created by IntelliJ IDEA.
 * User: ckunze
 * Date: 23/2/17
 * Time: 12:10
 */

namespace PrePayment\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class Settings
 *
 * @property int $id
 * @property int $plentyId
 * @property string $lang
 * @property string $name
 * @property string $value
 * @property string $updatedAt
 */
class Settings extends Model
{
    const AVAILABLE_SETTINGS = array(        "plentyId"            => "int"     ,
                                             "lang"                => "string"  ,
                                             "name"                => "string"  ,
                                             "infoPageType"        => "int"     ,
                                             "infoPageIntern"      => "int"     ,
                                             "infoPageExtern"      => "string"  ,
                                             "shippingCountries"   => ['int']   ,
                                             "logo"                => "int"     ,
                                             "logoUrl"             => "string"  ,
                                             "feeDomestic"         => "float"   ,
                                             "feeForeign"          => "float"   ,
                                             "showBankData"        => "bool"    ,
                                             "designatedUse"       => "string"  ,
                                             "showDesignatedUse"   => "bool"    );

    const SETTINGS_DEFAULT_VALUES = array(   "name"                => "Vorkasse"         ,
                                             "infoPageType"        => "0"                ,
                                             "infoPageIntern"      => ""                 ,
                                             "infoPageExtern"      => ""                 ,
                                             "shippingCountries"   => ""                 ,
                                             "logo"                => "0"                ,
                                             "logoUrl"             => ""                 ,
                                             "feeDomestic"         => "0.00"             ,
                                             "feeForeign"          => "0.00"             ,
                                             "showBankData"        => "0"                ,
                                             "designatedUse"       => "Verwendungszweck" ,
                                             "showDesignatedUse"   => "0"                );

    const AVAILABLE_LANGUAGES = array( "de",
                                       "en",
                                       "fr",
                                       "es" );

    const DEFAULT_LANGUAGE = "de";

    const MODEL_NAMESPACE = 'PrePayment\Models\Settings';


    public $id;
    public $plentyId;
    public $lang        = '';
    public $name        = '';
    public $value       = '';
    public $updatedAt   = '';


    /**
     * @return string
     */
    public function getTableName():string
    {
        return 'PrePayment::settings';
    }
}