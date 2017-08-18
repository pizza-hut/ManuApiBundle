<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\Api;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\CoreBundle\Helper\PhoneNumberHelper;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\ManuApiBundle\Integration\ManuApiIntegration;
use Monolog\Logger;

/*
use Joomla\Http\HttpFactory;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\CacheStorageHelper;
use Mautic\CoreBundle\Helper\EncryptionHelper;
use Mautic\CoreBundle\Helper\PathsHelper;
use Mautic\CoreBundle\Model\NotificationModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Entity\IntegrationEntity;
use Mautic\PluginBundle\Event\PluginIntegrationAuthCallbackUrlEvent;
use Mautic\PluginBundle\Event\PluginIntegrationFormBuildEvent;
use Mautic\PluginBundle\Event\PluginIntegrationFormDisplayEvent;
use Mautic\PluginBundle\Event\PluginIntegrationKeyEvent;
use Mautic\PluginBundle\Event\PluginIntegrationRequestEvent;
use Mautic\PluginBundle\Exception\ApiErrorException;
use Mautic\PluginBundle\Helper\oAuthHelper;
use Mautic\PluginBundle\PluginEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
*/

use Mautic\PluginBundle\Exception\ApiErrorException;
use MauticPlugin\ManuApiPlugin\Integration\ManuApiAIntegration;
//use MauticPlugin\MauticCrmBundle\Integration\SalesforceIntegration;

class LocalhostApi extends AbstractManuapi
{
    /**
     * @var \Services_Twilio - don't know what is it going to be
     * @var \Services_Manuapi - use this?
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $sendingPhoneNumber;
    
    protected $integration;
    
    //protected $factory;
    //protected $dispatcher;
    //protected $cache;
    
    /**
     * LocalhostApi constructor.
     *
     * @param TrackableModel    $pageTrackableModel
     * @param PhoneNumberHelper $phoneNumberHelper
     * @param IntegrationHelper $integrationHelper
     * @param Logger            $logger
     */
    public function __construct(TrackableModel $pageTrackableModel, PhoneNumberHelper $phoneNumberHelper, IntegrationHelper $integrationHelper, Logger $logger)
    {
        //$this->factory           = $factory;
        //$this->dispatcher        = $factory->getDispatcher();
        //$this->cache             = $this->dispatcher->getContainer()->get('mautic.helper.cache_storage')->getCache($this->getName());        
        $this->logger = $logger;

        $integration = $integrationHelper->getIntegrationObject('ManuApi');

        /*
        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $this->sendingPhoneNumber = $integration->getIntegrationSettings()->getFeatureSettings()['sending_phone_number'];

            $keys = $integration->getDecryptedApiKeys();

            $this->client = new \Services_Twilio($keys['username'], $keys['password']);
        }
        */
        
        //$this->client = new \Services_Manupi();
        $this->requestSettings['curl_options'] = [
            CURLOPT_SSLVERSION => defined('CURL_SSLVERSION_TLSv1_1') ? CURL_SSLVERSION_TLSv1_1 : CURL_SSLVERSION_TLSv1_1,
        ];

        parent::__construct($pageTrackableModel, $integration);
    }

    
    protected function request($operation, $parameters = [], $method = 'GET', $object = 'contacts')
    {
        //$hapikey = $this->integration->getHubSpotApiKey();
        $requestUrl = 'http://localhost:9991/api.php?action=get_app&id=1';
        //$requestUrl = 'https://localhost:9992/api.php?action=get_app&id=1';
        $response = $this->integration->makeRequest($requestUrl, $elementData, $method, $settings);
        var_dump($response);
        return $response;
    }

    /**
     * @param string $number
     * @param string $content
     *
     * @return bool|string
     */
    public function sendManuapi($number, $content)
    {
        $response = $this->request(null, null, 'GET', 'contacts');
        $this->logger->addWarning($response);    
        //return $this->request();
        //just make a simple REST call
        //$request = $this->integration->makeRequest($url, $parameters, $method, $this->requestSettings);
        
        
        /*
        if ($number === null) {
            return false;
        }

        try {
            $this->client->account->messages->sendMessage(
                $this->sendingPhoneNumber,
                $this->sanitizeNumber($number),
                $content
            );

            return true;
        } catch (\Services_Manuapi_RestException $e) {
            $this->logger->addWarning(
                $e->getMessage(),
                ['exception' => $e]
            );

            return $e->getMessage();
        } catch (NumberParseException $e) {
            $this->logger->addWarning(
                $e->getMessage(),
                ['exception' => $e]
            );

            return $e->getMessage();
        }
        */
        return $response;
        
    }
   
}
