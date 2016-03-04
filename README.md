A Yii validator for EU VAT numbers

# EU VAT validator for Yii2 #

based on https://github.com/herdani/vat-validation

## About ##
The
- Validate a VAT number
- Retrieve information like the name or the address of the company

The data is extracted from a European Commission webservice

__It ONLY works for European countries__

## Requirements ##

PHP with Soap enabled

## Install ##

    composer require elvenpath/yii2-eu-vatvalidator

## Usage ##
    public function rules()
    {
        return [
            [
                'vat',
                EuVatValidator::className(),
                'country_code' => $this->country_code,
            ],
        ];
    }

You can also populate the model with the company name and address got from the EU database

    public function rules()
    {
        return [
            [
                'vat',
                EuVatValidator::className(),
                'country_code' => $this->country_code,
                'populate_model' => true,
                'model_name_attribute' => 'name',
                'model_address_attribute' => 'address'
            ],
        ];
    }

## Disclaimer ##

Take a look at http://ec.europa.eu/taxation_customs/vies/viesdisc.do to know when/how you're allowed to use this service and his information
