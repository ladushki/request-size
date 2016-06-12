<?php
namespace src;

class AnalizeUrl
{

    const RESOURCE_TYPES = [
        'img' => 'src',
        'link' => 'href',
        'object' => 'data',
        'video' => 'src',
        'applet' => 'code',
        'param' => 'value',
        'audio' => 'src',
        'embed' => 'src',
        'source' => 'src',
        'frame' => 'src',
        'iframe' => 'src',
        'script' => 'src'
    ];

    const DATA_URI_REGEX = '/^data:(.+?){0,1}(?:(?:;(base64)\,){1}|\,)(.+){0,1}$/';

    public $url;

    protected $totalRequests;

    protected $totalSize;

    public function __construct($url)
    {
        $this->url = $url;
        $this->base = $this->url;
    }

    /**
     *
     * @return the $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     *
     * @param field_type $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getStatistics()
    {
        $file = new FileSize($this->url);
        $type = $file->getType();

        if ($type != 'text/html') {
            return $file->getSize();
        }

        $resources = $this->getResources($this->url);
        $stats = [];
        $this->totalSize = 0;
        foreach ($resources as $type => $files) {

            $size = 0;
            $cnt = count($files);
            if ($cnt == 0)
                continue;

            $this->totalRequests += $cnt;

            foreach ($files as $url) {

                $isDataUri = self::isDataUri($url);

                if (self::isRelative($url) && ! $isDataUri) {
                    $url = $this->base . $url;
                }

                $file = new FileSize($url, $isDataUri);
                $fileSize = $file->getSize();

                $this->totalSize += $fileSize;
                $size = $size + $fileSize;
            }
            $stats[$type] = [
                'type' => $type,
                'count' => $cnt,
                'size' => $size
            ];
        }
        $stats['total'] = [
            'type' => 'total',
            'count' => $this->totalRequests,
            'size' => $this->totalSize
        ];

        return $stats;
    }

    private function getResources($url)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = FALSE;
        @$dom->loadHTMLFile($url);

        $base = $dom->documentElement->getElementsByTagName('base');

        if ($base->length > 0) {
            foreach ($base as $baseTag) {
                $this->base = $baseTag->getAttribute('href');
            }
        }

        $this->base = preg_replace("|/$|", '', $this->base);
        $resources = [];
        $resources['html'] = [
            $url
        ];

        foreach (self::RESOURCE_TYPES as $tag => $attribute) {
            $elements = $dom->getElementsByTagName($tag);
            foreach ($elements as $val) {
                if (! $val->getAttribute($attribute))
                    continue;
                $url = $val->getAttribute($attribute);
                if ($url == $this->base)
                    continue;

                $url = preg_replace("|/$|", '', $url);
                $resources[$tag][] = $url;
            }
        }

        return $resources;
    }

    public static function isDataUri($url)
    {
        $regExp = self::DATA_URI_REGEX;
        return (preg_match($regExp, $url) === 1);
    }

    public static function getDataUriType($url)
    {
        $matches = null;
        if (preg_match_all(self::DATA_URI_REGEX, $url, $matches, PREG_SET_ORDER) !== false) {
            return isset($matches[0][1]) ? $matches[0][1] : 'Unknown data uri type';
        }
    }

    public static function isRelative($url)
    {
        return strncmp($url, '//', 2) && strpos($url, '://') === false;
    }

    public static function formatHeaders($headers)
    {
        if (! is_array($headers))
            return false;
        $formated = [];
        foreach ($headers as $i => $v) {
            $v = trim($v);
            if (empty($v))
                continue;
            $values = explode(":", $v);
            if (! key_exists(0, $values))
                continue;
            if (! key_exists(1, $values)) {
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