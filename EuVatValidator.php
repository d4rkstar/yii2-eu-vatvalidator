<?php
namespace elvenpath\yii2_eu_vatvalidator;

use Exception;
use SoapClient;
use SoapFault;
use stdClass;
use Yii;
use yii\validators\Validator;

/**
 * Class EuVatValidator
 *
 * @package elvenpath\yii2_eu_vatvalidator
 */
class EuVatValidator extends Validator
{
    /** @var string */
    public $wsdl_uri = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";
    /** @var string 2 letter country code */
    public $country_code;
    /**
     * @var bool
     *
     * If true the $model_name_attribute and $model_address_attribute will be populated
     * with the EU official company name and address
     */
    public $populate_model = false;
    /** @var string */
    public $model_name_attribute = 'name';
    /** @var string */
    public $model_address_attribute = 'address';
    /** @var SoapClient */
    private $client;

    public function init()
    {
        parent::init();

        if (!class_exists('SoapClient')) {
            throw new Exception('The Soap library has to be installed and enabled');
        }

        $this->client = new SoapClient($this->wsdl_uri, ['trace' => false]);
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @return bool
     */
    public function validateAttribute($model, $attribute)
    {
        try {
            /** @var stdClass $rs */
            /** @noinspection PhpUndefinedMethodInspection */
            $rs = $this->client->checkVat([
                'countryCode' => $this->country_code,
                'vatNumber' => preg_replace('/^' . $this->country_code . '/', '', $model->$attribute)
            ]);
        } catch (SoapFault $e) {
            $this->addError($model, $attribute,
                $this->message ? $this->message : Yii::t('yii',
                    'Cannot check {attribute} at this point. Please try again later.'));
        }
        if (isset($rs)) {

            if (!$rs->valid) {
                $this->addError($model, $attribute,
                    $this->message ? $this->message : Yii::t('yii', '{attribute} is invalid.'));
            } else {
                if ($this->populate_model) {
                     $model->{$this->model_name_attribute} = $this->cleanUpString($rs->name);
                     $model->{$this->model_address_attribute} = $this->cleanUpString($rs->address);
                }
            }
        }
    }

    private function cleanUpString($string)
    {
        for ($i = 0; $i < 100; $i++) {
            $newString = str_replace("  ", " ", $string);
            if ($newString === $string) {
                break;
            } else {
                $string = $newString;
            }
        }

        $newString = "";
        $words = explode(" ", $string);
        foreach ($words as $k => $w) {
            $newString .= ucfirst(strtolower($w)) . " ";
        }
        return $newString;
    }
}
