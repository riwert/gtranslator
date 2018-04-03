<?php
/**
 * GTranslator
 *
 * Translate text or array between multiple languages.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.0.0
 */
class GTranslator
{
    const API_URL = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&ie=UTF-8&oe=UTF-8';
    const USER_AGENT = 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1';
    const DETECT_LANGUAGE = false;
    const ITEM_DELIMITER = "\n";

    private $sourceLanguage = 'en';
    private $targetLanguage = 'pl';
    private $fields;
    private $result;
    private $format = ['%d', '%s'];
    private $formatWrap = ['(% d)', '(% s)'];

    public function __construct($sourceLanguage = null, $targetLanguage = null)
    {
        if (self::DETECT_LANGUAGE) {
            $this->detectLanguage();
        }
        if ($sourceLanguage) {
            $this->sourceLanguage = $sourceLanguage;
        }
        if ($targetLanguage) {
            $this->targetLanguage = $targetLanguage;
        }
    }

    private function detectLanguage()
    {
        $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if ($language) {
            $this->targetLanguage = $language;
        }
    }

    private function fetchText($text)
    {
        if (strlen($text) >= 5000) {
            throw new \Exception('Maximum number of characters exceeded: 5000 is the limit.');
        }

        $fields = array(
            'sl' => urlencode($this->sourceLanguage),
            'tl' => urlencode($this->targetLanguage),
            'q' => urlencode($text)
        );

        $queryFields = http_build_query($fields);
        $postFields = urldecode($queryFields);

        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        // Execute post
        $this->result = curl_exec($ch);
        // Close connection
        curl_close($ch);
    }

    private function wrapFormat($text)
    {
        return str_replace($this->format, $this->formatWrap, $text);
    }

    private function unWrapFormat($text)
    {
        return str_replace($this->formatWrap, $this->format, $text);
    }

    public function getJsonResult()
    {
        return $this->result;
    }

    public function getArrayResult()
    {
        return json_decode($this->result);
    }

    public function getResult()
    {
        $arrayResult = $this->getArrayResult();

        $sentences = $arrayResult->{'sentences'};

        $result = '';
        foreach ($sentences as $sentence) {
            $result .= (isset($sentence->trans)) ? $sentence->trans : '';
        }

        return $result;
    }

    public function translateText($text)
    {
        $this->fetchText($text);

        return $this->getResult();
    }

    public function translateArray(array $array)
    {        
        $array = array_map(function($value) { return $this->wrapFormat($value); }, $array);

        $text = implode(self::ITEM_DELIMITER, $array);
        $this->fetchText($text);
        $result = $this->getResult();
        $resultArray = explode(self::ITEM_DELIMITER, $result);
                
        $i = 0;
        $translated = [];
        foreach ($array as $key => $value) {
            $translated[$key] = (isset($resultArray[$i])) ? $resultArray[$i] : '';
            $i++;
        }
        
        $translated = array_map(function($value) { return $this->unWrapFormat($value); }, $translated);

        return $translated;
    }

    public function exportToPhpFile($array, $fileName)
    {
        $contents = var_export($array, true);
        file_put_contents($fileName, "<?php\nreturn {$contents};\n");
    }
}
