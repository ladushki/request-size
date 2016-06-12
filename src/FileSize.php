<?php
namespace src;

class FileSize
{
    public $url;
    public $isDataUri;
    private $content;
    private $info;
    private $stats;

    public function __construct($url, $isDataUri = false){
      $this->setIsDataUri($isDataUri);
      $this->url = $url;
      if (filter_var($url, FILTER_VALIDATE_URL) === FALSE && !$isDataUri) {
          throw new \Exception('URL is not valid');
      }

        try {
            //$this->headers = get_headers($this->url, 1);
           $data = $this->getCurlData($url, true);
           $content = $data['content'];
           $info = $data['info'];
           $this->setContent($content);
           $this->setInfo($info);
        } catch (\Exception $e){
            die("Error: can not read url.\n");
        }
    }

    /**
     * @return the $headers
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return the $info
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return the $stats
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @return the $isDataUri
     */
    public function getIsDataUri()
    {
        return $this->isDataUri;
    }

    /**
     * @param field_type $isDataUri
     */
    public function setIsDataUri($isDataUri)
    {
        $this->isDataUri = $isDataUri;
    }


    /**
     * @param field_type $headers
     */
    private function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param field_type $info
     */
    private function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @param field_type $stats
     */
    private function setStats($stats)
    {
        $this->stats = $stats;
    }


    private function getCurlData($url, $sizeOnly = true) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        $content = '';
        if ($sizeOnly) {
            curl_setopt($curl, CURLOPT_NOBODY, TRUE);
        } else {
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
        }
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        $size = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($curl);
        return ['content'=>$content, 'info'=>$info, 'size'=>$size];
    }

    public function getStatus(){
        if (key_exists('http_code', $this->info)) {
            return $this->info['http_code'];
        }
    }

    public function getType(){
        if (key_exists('content_type', $this->info)) {
            list($mime) = explode(';',  $this->info['content_type']);
            return $mime;
        }
    }

    public function getSize(){
        if ($this->isDataUri === true) {
            return strlen(base64_decode($this->url));
        }
        if (key_exists('download_content_length', $this->info) && $this->info['download_content_length']>=0) {
            return $this->info['download_content_length'];
        } else {
           $content = $this->getCurlData($this->url, false);
           return strlen($content['content']);
        }
    }


    static function formatHeaders($headers) {
        if (!is_array($headers)) return false;
        $formated = [];
        foreach ($headers as $i=>$v) {
            $v= trim($v);
            if (empty($v)) continue;
            $values = explode(":", $v);
            if (!key_exists(0, $values)) continue;
            if (!key_exists(1, $values)) {
                $key = $i;
                $value = $values[0];
            } else {
                $key = $values[0];
                $value = $values[1];
            }
            $formated[$key] = $value;
        }
        return $formated;
    }
}

?>