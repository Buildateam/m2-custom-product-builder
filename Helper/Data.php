<?php


namespace Buildateam\CustomProductBuilder\Helper;


use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * retrieve JsonData decoded
     */
    public function getJsonDataDecoded($data)
    {
        $dataJson = json_decode($data);
        return $dataJson;
    }

    /**
     * Return Custom Builder Product file
     */
    public function getJsonBuilderFile()
    {
        $params = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();

        /** get json content from product */
        echo file_put_contents(Mage::getBaseDir('var').'/test.json',"Hello World. Testing!");

        $handle = fopen("./var/product-builder.json", "w+");
        header('Access-Control-Allow-Origin: *');
        header('Content-type: application/json');

        echo json_encode($handle);
    }

    /**
     * validating json format
     * @param $string
     * @return mixed
     */
    public function validate($string)
    {
        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            // throw the Exception or exit // or whatever :)
            return($error);
        }

        // everything is OK
        return '';
    }

    /**
     * creating folder if does not exist yet
     * @param $media path to folder
     */
    public function createFolder($media)
    {
        if (!file_exists($media)) {
            mkdir($media, 0777, true);
        }
    }

    public function getDataByKey($data, $key)
    {
        return !empty($data[$key]) ? $data[$key] : '';
    }
}