<?php
namespace App\Helpers;

class MailOneCustomForm
{
    private $fields = array();
    private $fieldsData = array();

    private $newsletterId = -1;

    public function __construct($id, $vardatamaildata)
    {

        if($vardatamaildata===null)return;
        $this->newsletterId = trim($id);

        $vardata = array('fields' => $vardatamaildata);

        if (isset($vardata['fields']) && isset($vardata['fields']['item'])) {
            foreach ($vardata['fields']['item'] as $k_d => $v_custom) {
                $fieldName = trim(strtolower($v_custom ['name']));
                $this->fields [$fieldName] = $v_custom ['fieldid'];
                if ($fieldName == 'adults') {
                    $this->fields ['adulti'] = $v_custom ['fieldid'];
                } elseif (utf8_encode($fieldName) == 'città') {
                    $this->fields ['city'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'città') {
                    $this->fields ['city'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'cap') {
                    $this->fields ['zipcode'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'nazione') {
                    $this->fields ['nation'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'telefono') {
                    $this->fields ['phone'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'children') {
                    $this->fields ['bambini'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'minage') {
                    $this->fields ['etaminima'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'maxage') {
                    $this->fields ['etamassima'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'treatment') {
                    $this->fields ['trattamento'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'checkin') {
                    $this->fields ['dataarrivo'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'lingua') {
                    $this->fields ['language'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'nome') {
                    $this->fields ['name'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'cognome') {
                    $this->fields ['surname'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'checkout') {
                    $this->fields ['datapartenza'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'reservetype') {
                    $this->fields ['reservetype'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'Struttura') {
                    $this->fields ['Struttura'] = $v_custom ['fieldid'];
                } elseif ($fieldName == 'Localita' || $fieldName == 'Località' || utf8_encode($fieldName) == 'Località') {
                    $this->fields ['Localita'] = $v_custom ['fieldid'];
                    $this->fields ['Località'] = $v_custom ['fieldid'];

                }
            }

        }
    }

    public function getNormalizedName($fieldName)
    {
        $fieldName = mb_convert_case($fieldName, MB_CASE_LOWER);
        switch ($fieldName) {
            case 'adults':
            case 'adulti':
                $fieldName = 'adults';
                break;
            case 'città':
            case 'citta':
            case 'city':
                $fieldName = 'city';
                break;
            case 'cap':
            case 'zipcode':
                $fieldName = 'zipcode';
                break;
            case 'nazione':
            case 'nation':
                $fieldName = 'nation';
                break;
            case 'telefono':
            case 'phone':
                $fieldName = 'phone';
                break;
            case 'children':
            case 'bambini':
                $fieldName = 'children';
                break;
            case 'minage':
            case 'etaminima':
                $fieldName = 'minage';
                break;
            case 'maxage':
            case 'etamassima':
                $fieldName = 'maxage';
                break;
            case 'trattamento':
            case 'treatment':
                $fieldName = 'treatment';
                break;
            case 'dataarrivo':
            case 'checkin':
                $fieldName = 'checkin';
                break;
            case 'lingua':
            case 'language':
                $fieldName = 'language';
                break;
            case 'nome':
            case 'name':
                $fieldName = 'name';
                break;
            case 'cognome':
            case 'surname':
                $fieldName = 'surname';
                break;
            case 'datapartenza':
            case 'checkout':
                $fieldName = 'checkout';
                break;
            case 'reservetype':
                $fieldName = 'reservetype';
                break;
            case 'struttura':
                $fieldName = 'structure';
                break;
            case 'localita':
            case 'località':
            case 'place':
                $fieldName = 'place';
                break;
            case 'provenienza':
            case 'channel':
                $fieldName = 'channel';
                break;
            case 'mobile':
            case 'cellulare':
                $fieldName = 'mobile';
                break;
            case 'title':
            case 'titolo':
                $fieldName = 'title';
                break;
            case 'birth date':
            case 'anno di nascita':
                $fieldName = 'birth date';
                break;
        }

        return $fieldName;
    }

    public function setFieldValue($fieldName, $fieldValue)
    {
        $fieldName = trim(strtolower($fieldName));

        if (isset ($this->fields [$fieldName])) {
            $this->fieldsData [$this->fields [$fieldName]] = $fieldValue;
        }
    }

    public function getCustomFields()
    {
        return $this->fieldsData;
    }


    public function getNewsletterMailOneId()
    {

        return $this->newsletterId;
    }


}

?>
