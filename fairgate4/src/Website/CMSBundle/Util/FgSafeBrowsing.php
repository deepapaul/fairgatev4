<?php

/**
 * Manage CMS Website container functionalities
 *
 *
 */
namespace Website\CMSBundle\Util;

/**
 * Manage CMS theme container functionalities
 *
 * 
 */
class FgSafeBrowsing
{

    /**
     * @var string Fairgate API key
     */
    public $apiKey;

    /**
     * @var string Safe browsing post data
     */
    public $postData;

    /**
     * Constructor of FgCmsThemeContainerDetails class.
     *
     * @param ContainerInterface $container
     */
    public function __construct()
    {
        $this->apiKey = "AIzaSyAH8aNG-whPcbHlMXbeGYKswcBjTVwpLVk";
    }

    /**
     * Function to validate URL 
     * 
     * @param string $url
     * 
     * @return type
     */
    public function validateUrl($url)
    {
        try {
            $this->setUrlToCheck($url);
            $APIURL = "https://safebrowsing.googleapis.com/v4/threatMatches:find?key=" . $this->apiKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $APIURL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", 'Content-Length: ' . strlen($this->postData)));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            return json_decode($result);
        } catch (Exception $ex) {

            return json_decode('{}');
        }
    }

    /**
     * Function to set post data array
     * @param string $url
     */
    private function setUrlToCheck($url)
    {
        $this->postData = $data = '{
        "client": {
          "clientId": "fairgate",
          "clientVersion": "1.0"
        },
        "threatInfo": {
          "threatTypes":      ["MALWARE", "SOCIAL_ENGINEERING"],
          "platformTypes":    ["LINUX","WINDOWS"],
          "threatEntryTypes": ["URL"],
          "threatEntries": [
            {"url": "' . $url . '"}
          ]
        }
      }';
    }
}
