<?php

namespace CashInAdvance\Services;

use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Query;
use Plenty\Modules\System\Contracts\WebstoreRepositoryContract;
use Plenty\Modules\System\Models\Webstore;
use Plenty\Plugin\Application;

use CashInAdvance\Models\Settings;
use CashInAdvance\Models\ShippingCountrySettings;

class SettingsService
{
    /** @var Application  */
    private $app;

    /** @var  DataBase */
    private $db;

    /** @var  array */
    private $loadedSettings;

    /**
     * SettingsService constructor.
     * @param Application $app
     * @param DataBase $db
     */
    public function __construct(Application $app, DataBase $db)
    {
        $this->app = $app;
        $this->db  = $db;
    }

    /**
     * Load a specific setting for system client by plentyId
     *
     * @param string $name
     * @param string $lang
     *
     * @return mixed|Settings
     * @throws ValidationException
     */
    public function getSetting(string $name, $lang = 'de')
    {
        $plentyId = $this->app->getPlentyId();

        if(empty($this->loadedSettings))
        {
            $this->loadedSettings = $this->getSettingsForPlentyId($plentyId, $lang);
        }

        if(array_key_exists($name, $this->loadedSettings))
        {
            return $this->loadedSettings[$name];
        }

        throw new ValidationException('No such setting found: ' . $name);
    }

    /**
     * Get client settings by specific plentyId, indicating the array conversion
     *
     * @param $plentyId
     * @param $lang
     * @param bool $convertToArray
     *
     * @return array|Settings[]
     */
    public function getSettingsForPlentyId($plentyId, $lang, bool $convertToArray = true)
    {
        $lang = $this->checkLanguage($lang);

        /** @var Settings $settings */
        $settings = $this->loadClientSettings($plentyId, $lang);

        $shippingSettings = $this->getShippingCountriesByPlentyId($plentyId);

        if($convertToArray && (count($settings) || count($shippingSettings)))
        {
            $outputArray = array();

            $availableSettings = Settings::AVAILABLE_SETTINGS;

            /** @var Settings $setting */
            foreach ($settings as $setting)
            {
                if (array_key_exists($setting->name, $availableSettings))
                {
                    $outputArray[$setting->name] = $setting->value;
                }
            }

            $outputArray['plentyId']    = $settings[0]->plentyId;
            $outputArray['lang']        = $settings[count($settings) - 1]->lang;

            $outputArray = $this->convertSettingsToCorrectFormat($outputArray,$availableSettings);

            $outputArray['shippingCountries'] = $shippingSettings;

            return $outputArray;
        }

        return $settings;
    }

    /**
     * Update Settings
     *
     * @param $data
     *
     * @return int
     */
    public function saveSettings($data)
    {
        $pid    = $data['plentyId'];
        $lang   = $data['lang'];
        unset( $data['lang']);
        unset( $data['plentyId']);

        if(count($data) > 0 && !empty($pid))
        {
            $settingsToSave = $this->convertSettingsToCorrectFormat($data, Settings::AVAILABLE_SETTINGS);

            /** @var Settings[] $settings */
            $settings = $this->loadAllClientSettings($pid);

            $newLang = true;

            /** @var Settings $setting */
            foreach ($settings as $setting)
            {
                if (array_key_exists($setting->name, $settingsToSave))
                {
                    $setting->value     = (string)$settingsToSave[$setting->name];
                    $setting->updatedAt = date('Y-m-d H:i:s');

                    $this->db->save($setting);

                    if($setting->name == 'name'){
                        $newLang = false;
                    }
                }
            }
            if($newLang){
                foreach ($settingsToSave as $name => $value) {
                    if(!in_array($name,['feeDomestic','feeForeign','showBankData','plentyId','lang'])){
                        $newSetting = pluginApp(Settings::class);
                        $newSetting->plentyId = $pid;
                        $newSetting->lang = $lang;
                        $newSetting->name = $name;
                        $newSetting->value = $value;
                        $newSetting->updatedAt = date('Y-m-d H:i:s');
                        $this->db->save($newSetting);
                    }
                }
            }

            if(isset($data['shippingCountries'])) {
                /** @var ShippingCountrySettings[] $currentShippingCountriesArray */
                $currentShippingCountriesArray = $this->getShippingCountriesByPlentyId($pid);
                if(!count($data['shippingCountries']) && count($currentShippingCountriesArray)) {
                    $this->db->query(ShippingCountrySettings::MODEL_NAMESPACE)
                        ->where('plentyId', '=', $pid)->delete();
                }
                foreach($data['shippingCountries'] as $index => $countryId) {
                    if(!in_array($countryId, $currentShippingCountriesArray)) {
                        /** @var ShippingCountrySettings $shippingCountrySettings */
                        $shippingCountrySettings = pluginApp(ShippingCountrySettings::class);
                        $shippingCountrySettings->plentyId = $pid;
                        $shippingCountrySettings->shippingCountryId = $countryId;
                        $this->db->save($shippingCountrySettings);
                    }
                }
                foreach($currentShippingCountriesArray as $index => $countryId) {
                    if(!in_array($countryId, $data['shippingCountries'])) {
                        $this->db->query(ShippingCountrySettings::MODEL_NAMESPACE)
                            ->where('plentyId', '=', $pid)
                            ->where('shippingCountryId', '=', $countryId)->delete();
                    }
                }
            }
            return 1;
        }

        return 0;
    }

    /**
     * Delete settings for one client
     *
     * @param integer $plentyId
     */
    public function deleteSettings($plentyId) {
        $this->db->query(Settings::MODEL_NAMESPACE)
            ->where("plentyId",'=',$plentyId)->delete();
        $this->db->query(ShippingCountrySettings::MODEL_NAMESPACE)
            ->where('plentyId', '=', $plentyId)->delete();
    }

    /**
     * Creates initial settings by plentyId and language
     *
     * @param $plentyId
     * @param $lang
     *
     * @return array
     * @throws ValidationException
     */
    public function createInitialSettingsForPlentyId($plentyId, $lang)
    {
        $generatedSettings    = $this->createLangIndependentInitialSettings($plentyId);

        foreach( Settings::AVAILABLE_SETTINGS as $setting => $type)
        {
            if($setting != 'plentyId' && $setting != 'lang' && !in_array($setting, Settings::LANG_INDEPENDENT_SETTINGS))
            {
                /** @var Settings $newSetting */
                $newSetting            = pluginApp(Settings::class);
                $newSetting->plentyId  = $plentyId;
                $newSetting->lang      = $lang;
                $newSetting->name      = $setting;

                if(array_key_exists($lang, Settings::SETTINGS_DEFAULT_VALUES))
                {
                    $newSetting->value     = (string)Settings::SETTINGS_DEFAULT_VALUES[$lang][$setting];
                }
                elseif(array_key_exists(Settings::DEFAULT_LANGUAGE, Settings::SETTINGS_DEFAULT_VALUES))
                {
                    $newSetting->value     = (string)Settings::SETTINGS_DEFAULT_VALUES[Settings::DEFAULT_LANGUAGE][$setting];
                }
                else
                {
                    throw new ValidationException('No such default values for language: ' . $lang);
                }

                $newSetting->updatedAt = date('Y-m-d H:i:s');

                $generatedSettings[] = $this->db->save($newSetting);
            }
        }

        return $generatedSettings;
    }

    /**
     * Create initial language independent settings
     *
     * @param $plentyId
     *
     * @return array
     */
    private function createLangIndependentInitialSettings($plentyId)
    {
        $generatedSettings = array();

        /** @var Settings[] $storedSettings */
        $storedSettings = $this->db->query(Settings::MODEL_NAMESPACE)
            ->where('plentyId', '=', $plentyId)
            ->where('lang', '=', '')->get();

        $settingIds = array();
        /** @var Settings $storedSetting */
        foreach($storedSettings as $storedSetting)
        {
            $settingIds[$storedSetting->name] = $storedSetting->id;
        }

        foreach(Settings::LANG_INDEPENDENT_SETTINGS as $setting)
        {
            if($setting != 'plentyId' && $setting != 'lang')
            {
                /** @var Settings $newSetting */
                $newSetting            = pluginApp(Settings::class);
                if(array_key_exists($setting, $settingIds) && !empty($settingIds[$setting]))
                {
                    $newSetting->id = $settingIds[$setting];
                }

                $newSetting->plentyId  = $plentyId;
                $newSetting->lang      = '';
                $newSetting->name      = $setting;
                $newSetting->value     = (string)Settings::SETTINGS_DEFAULT_VALUES[$setting];
                $newSetting->updatedAt = date('Y-m-d H:i:s');

                $generatedSettings[] = $this->db->save($newSetting);
            }
        }

        return $generatedSettings;
    }

    /**
     * Get available clients of the system
     *
     * @return array
     */
    public function getClients()
    {
        /** @var WebstoreRepositoryContract $wsRepo */
        $wsRepo = pluginApp(WebstoreRepositoryContract::class);

        $clients    = array();

        /** @var Webstore[] $result */
        $result = $wsRepo->loadAll();

        /** @var Webstore $record */
        foreach($result as $record)
        {
            if($record->storeIdentifier > 0)
                $clients[] = $record->storeIdentifier;
        }

        return $clients;
    }

    /**
     * Get available clients of the system
     *
     * @return array
     */
    public function getCashInAdvanceClients()
    {
        /** @var WebstoreRepositoryContract $wsRepo */
        $wsRepo = pluginApp(WebstoreRepositoryContract::class);

        $clients    = array();

        /** @var Webstore[] $result */
        $result = $wsRepo->loadAll();

        /** @var Webstore $record */
        foreach ($result as $record) {
            if ($record->storeIdentifier > 0) {
                $settings = $this->clientSettingsExist($record->storeIdentifier);
                if ($settings) {
                    $clients[] = $record->storeIdentifier;
                }
            }
        }

        return $clients;
    }

    /**
     * Checks if input language is valid language, instead return default language
     *
     * @param $lang
     *
     * @return string
     */
    private function checkLanguage($lang)
    {
        if(!in_array($lang, Settings::AVAILABLE_LANGUAGES))
        {
            $lang = Settings::DEFAULT_LANGUAGE;
        }
        return $lang;
    }

    /**
     * Load settings for specified system clients by plentyId and language
     *
     * @param $plentyId
     * @param $lang
     *
     * @return Settings[]
     * @throws ValidationException
     */
    private function loadClientSettings($plentyId, $lang)
    {
        /** @var Query $query */
        $query = $this->db->query(Settings::MODEL_NAMESPACE);
        $query->where('plentyId', '=', $plentyId)->where('lang', '=', $lang);
        $query->orWhere('lang',   '=', '')->where('plentyId', '=', $plentyId);

        /** @var Settings[] $clientSettings */
        $clientSettings = $query->get();

        if(!count($clientSettings))
        {
            $this->updateClients();
            $clientSettings = $query->get();
        }

        if(!count($clientSettings))
        {
            throw new ValidationException('Error loading Settings');
        }

        return $clientSettings;
    }

    /**
     * Load settings for specified system clients by plentyId
     *
     * @param $plentyId
     *
     * @return Settings[]
     * @throws ValidationException
     */
    private function loadAllClientSettings($plentyId)
    {
        /** @var Query $query */
        $query = $this->db->query(Settings::MODEL_NAMESPACE);
        $query->where('plentyId', '=', $plentyId);

        /** @var Settings[] $clientSettings */
        $clientSettings = $query->get();

        if(!count($clientSettings))
        {
            $this->updateClients();
            $clientSettings = $query->get();
        }

        if(!count($clientSettings))
        {
            throw new ValidationException('Error loading Settings');
        }

        return $clientSettings;
    }

    /**
     * Check if settings exist for plentyId and language
     *
     * @param $plentyId
     *
     * @return boolean
     */
    public function clientSettingsExist($plentyId)
    {
        /** @var Query $query */
        $query = $this->db->query(Settings::MODEL_NAMESPACE);
        $query->where('plentyId', '=', $plentyId);
        return $query->count();
    }

    /**
     * Creates new settings for clients which are not in the DB but available in the system
     */
    private function updateClients()
    {
        $clients = $this->getClients();

        foreach($clients as $plentyId)
        {
            /** @var Settings[] $query */
            $query = $this->db->query(Settings::MODEL_NAMESPACE)
                ->where('plentyId', '=', $plentyId )->get();

            if( !count($query) > 0 || !$this->areAllLanguagesAvailable($query))
            {
                $storedLangs = $this->detectStoredLanguages($query);

                foreach(Settings::AVAILABLE_LANGUAGES as $lang)
                {
                    if(!in_array($lang, $storedLangs))
                    {
                        $this->createInitialSettingsForPlentyId($plentyId, $lang);
                    }
                }
            }
        }
    }

    /**
     * Checks if all defined languages are stored in the given settings model
     *
     * @param Settings[] $settings
     *
     * @return bool
     */
    private function areAllLanguagesAvailable(array $settings)
    {
        $languages = $this->detectStoredLanguages($settings);

        foreach(Settings::AVAILABLE_LANGUAGES as $lang)
        {
            if(!in_array($lang, $languages))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Detects all languages contained in the settings model
     *
     * @param Settings[] $settings
     *
     * @return array
     */
    private function detectStoredLanguages(array $settings)
    {
        $storedLanguages = array();

        /** @var Settings $setting */
        foreach($settings as $setting)
        {
            if(!in_array($setting->lang, $storedLanguages))
            {
                $storedLanguages[] = $setting->lang;
            }
        }

        return $storedLanguages;
    }

    /**
     * Convert settings of type string to the correct format defined in Settings.php
     * NOTE: Array types only can be of 1 value type, e.g. float
     *
     * @param array $settings
     * @param array $format
     *
     * @return array
     */
    private function convertSettingsToCorrectFormat(array $settings, array $format)
    {
        $convertedSettings = array();
        foreach( $format as $setting => $type)
        {
            if(!is_array($type))
            {
                $convertedSettings[$setting] = $this->setType($settings[$setting], $type);
            }
            /*else
            {
                if( is_string($settings[$setting]) && $settings[$setting] != "") //Check if the field is a string to convert it to array.
                {
                    $settingArray = explode('-/-', $settings[$setting]);
                    $arrayType    = array();
                    for($x = 0; $x < count($settingArray); $x++){ $arrayType[] = $type[0]; }
                    $convertedSettings[$setting] = $this->convertSettingsToCorrectFormat($settingArray, $arrayType);
                }
                else
                {
                    if(!empty($settings[$setting]) && is_array($settings[$setting]))
                    {
                        $arrayType    = array();
                        for($x = 0; $x < count($settings[$setting]); $x++){ $arrayType[] = $type[0]; }
                        $convertedSettings[$setting] = $this->convertSettingsToCorrectFormat($settings[$setting], $arrayType);
                    }
                    else
                    {
                        $convertedSettings[$setting] = array();
                    }
                }
            }*/
        }

        return $convertedSettings;
    }

    /**
     * settype() is not allowed, this method should do nearly the same except for array / object / null.
     *
     * @param $value
     * @param $type
     *
     * @return bool|float|int|string
     */
    private function setType($value, $type)
    {
        switch($type)
        {
            case "boolean": return $value == 0 ? false : true;
            case "bool":    return $value == 0 ? false : true;
            case "integer": return (int)$value;
            case "int":     return (int)$value;
            case "float":   return (float)$value;
            case "string":  return (string)$value;
        }
    }

    /**
     * Load the current activated shipping countries
     *
     * @return mixed|Settings
     * @throws ValidationException
     */
    public function getShippingCountries()
    {
        $plentyId = $this->app->getPlentyId();

        return $this->getShippingCountriesByPlentyId($plentyId);
    }

    /**
     * Load the activated shipping countries for plentyId
     *
     * @return mixed
     */
    public function getShippingCountriesByPlentyId($plentyId)
    {
        /** @var Query $query */
        $query = $this->db->query(ShippingCountrySettings::MODEL_NAMESPACE);
        $query->where('plentyId', '=', $plentyId);

        /** @var ShippingCountrySettings[] $shippingCountrySettings */
        $shippingCountrySettings = $query->get();

        $shippingCountriesArray = [];
        foreach($shippingCountrySettings as $shippingSetting){
            $shippingCountriesArray[] = (int)$shippingSetting->shippingCountryId;
        }

        return $shippingCountriesArray;
    }
}
