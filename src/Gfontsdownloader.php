<?php

namespace GFontsDownloader\GFontsDownloader;

/**
 * GFontsDownloader - PHP class to download google fonts in woff2 format
 * PHP version >= 7.0.0
 * 
 * @see https://github.com/Jeremy-Vaud/gfonts-downloader
 * 
 * @author    Jérémy Vaud <jeremy.vaud@protonmail.com>
 * 
 */
class GFontsDownloader {
    private $url = null;
    private $response = false;
    private $cssRules = [];
    private $dir;
    
    /**
     * __construct
     *
     * @param  string $url Google Fonts URL
     * @param  string $dir Fonts directory name
     * @throws Exeption If invalid Google Fonts URL
     * @return void
     */
    public function __construct(string $url, string $dir = "fonts") {
        try {
            if (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, "https://fonts.googleapis.com/")) {
                throw new \Exception("Invalid Google Fonts URL");
            }
            $this->url = $url;
            $this->dir = trim($dir, "/");
        } catch (\Exception $e) {
            echo ($e->getMessage());
        }
    }
    
    /**
     * Download all fonts, create directory and create font-face.css file
     *
     * @return void
     */
    public function download() {
        if ($this->url) {
            if (PHP_SAPI !== 'cli') {
                echo "<pre>";
            }
            if ($this->request()) {
                $this->responseToArray();
                if ($this->createDirectory()) {
                    $this->downloadFonts();
                }
            }
            if (PHP_SAPI !== 'cli') {
                echo "</pre>";
            }
        }
    }
    
    /**
     * Request to Google API
     *
     * @return bool
     */
    private function request() {
        $ch = curl_init($this->url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/81.0"
        ));
        $this->response = curl_exec($ch);
        if (!$this->response || curl_getinfo($ch)["http_code"] !== 200) {
            echo "An error occurred while requesting the google API\n";
            echo curl_error($ch) . "\n";
            return false;
        }
        return true;
    }
    
    /**
     * Create an array from the google API query response
     *
     * @return void
     */
    private function responseToArray() {
        $array = [];
        foreach (explode("@font-face", preg_replace("/[\/][\*](.*)[\*][\/]|[{}\r\n]/", "", $this->response)) as $val) {
            if (strlen($val) > 1) {
                $array[] = array_filter(explode(";", $val), function ($var) {
                    return ($var !== "");
                });
            }
        }
        foreach ($array as $key => $arr) {
            $this->cssRules[$key] = [];
            foreach ($arr as $rule) {
                $explode = explode(": ", $rule);
                $this->cssRules[$key][preg_replace("/[ ]/", "", $explode[0])] = $explode[1];
            }
        }
    }
    
    /**
     * Download all fonts and create font-face.css file
     *
     * @return void
     */
    private function downloadFonts() {
        $fontFace = $this->response;
        foreach ($this->cssRules as $key => $font) {
            $url = str_replace(["url(", ") format('woff2')"], "", $font["src"]);
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_HTTPGET => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/81.0"
            ));
            $response = curl_exec($ch);
            if (!$this->response || curl_getinfo($ch)["http_code"] !== 200) {
                echo "An error occurred while downloading" . $font["font-family"] . "\n";
                echo curl_error($ch) . "\n";
            } else {
                $fileName = trim(str_replace(" ", "_",$font["font-family"]), "'") . "-" . $font["font-weight"] . "-$key.woff2";
                $file = fopen($this->dir . "/" . $fileName, "w");
                if (fwrite($file, $response)) {
                    $fontFace = str_replace($url, $fileName, $fontFace);
                    echo "$fileName created\n";
                } else {
                    echo "An error occurred while creating the file : $fileName\n";
                }
            }
        }
        $file = fopen($this->dir . "/" . "font-face.css", "w");
        fwrite($file, $fontFace);
    }
    
    /**
     * Create Fonts directory
     *
     * @return bool
     */
    public function createDirectory() {
        if (!is_dir($this->dir)) {
            if (!mkdir($this->dir)) {
                echo "An error occurred while creating the fonts directory";
                return false;
            }
        }
        return true;
    }
}
