<?php
namespace Ups;

use DOMDocument;
use SimpleXMLElement;
use Exception;
use stdClass;

/**
 * Tracking API Wrapper
 *
 * @package ups
 */
class Check extends Ups
{
    const ENDPOINT = '/Void';

    /**
     * @param string|null $accessKey UPS License Access Key
     * @param string|null $userId UPS User ID
     * @param string|null $password UPS User Password
     * @param bool $useIntegration Determine if we should use production or CIE URLs.
     * @param RequestInterface $request
     */
    public function __construct($accessKey = null, $userId = null, $password = null, $useIntegration = false, RequestInterface $request = null)
    {
        if (null !== $request) {
            $this->setRequest($request);
        }
        parent::__construct($accessKey, $userId, $password, $useIntegration);
    }

    /**
     * Get package tracking information
     *
     * @param string $trackingNumber The package's tracking number.
     * @param string $requestOption Optional processing. For Mail Innovations the only valid options are Last Activity and All activity.
     * @return stdClass
     * @throws Exception
     */
    public function check()
    {
        $response = file_get_contents($this->compileEndpointUrl(self::ENDPOINT));

        if (false === $response) {
            throw new Exception("Failure (0): Unknown error", 0);
        }

        $document = new DOMDocument();
        $document->loadHTML($response);

        $xpath = new \DOMXPath($document);

        $element = $xpath->query('/html/body/h2');

        $content = $element->item(0)->textContent;

        $result = array();

        if (false !== preg_match('/Service Name: (\w+)/i', $content, $matches)) {
            $result['serviceName'] = $matches[1];
        }

        if (false !== preg_match('/Remote User: (\w+)/i', $content, $matches)) {
            $result['remoteUser'] = $matches[1];
        }

        if (false !== preg_match('/Server Port: (\d+)/', $content, $matches)) {
            $result['serverPort'] = $matches[1];
        }

        if (false !== preg_match('/Server Name: ([\w\.]+)/i', $content, $matches)) {
            $result['serverName'] = $matches[1];
        }

        if (false !== preg_match('/Application Version: ([\d\.]+)/', $content, $matches)) {
            $result['applicationVersion'] = $matches[1];
        }

        return $result;
    }
}