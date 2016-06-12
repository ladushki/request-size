<?php
namespace src;

use src\AnalizeUrl;

class RequestSize
{

    public $url;

    public function __construct($argv)
    {
        if (! key_exists(1, $argv)) {
            throw new \Exception('Pls enter valid url');
        } else {
            $this->setUrl($argv[1]);
        }

        try {
            $analizer = new AnalizeUrl($this->url);
            $statistics = $analizer->getStatistics();
            if (! is_array($statistics)) {
                echo self::formatBytes($statistics) . "\n";
            } else {
                foreach ($statistics as $resourceTag => $data) {
                    echo '<' . $resourceTag . '> requests: ' . $data['count'] . '; size: ' . self::formatBytes($data['size']) . "\n";
                }
            }
        } catch (\Exception $e) {
            die("Error:" . $e->getMessage() . "\n");
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            throw new \Exception("<" . $url . "> is not valid url");
        }

        $this->url = $url;

        return $this;
    }

    static function formatBytes($bytes, $precision = 2)
    { // php.net
        $units = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB'
        );

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return number_format(round($bytes, $precision), $precision, '.', ',') . ' ' . $units[$pow];
    }
}

?>