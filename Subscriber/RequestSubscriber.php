<?php

namespace ScopRedirecter\Subscriber;

use Enlight\Event\SubscriberInterface;
use Doctrine\DBAL\Connection;
use ScopRedirecter\Models\Redirecter;
use ScopRedirecter\Models\ScopRedirecterRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;

class RequestSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    protected $pluginBaseDirectory;

    /**
     * @var Connection
     */
    protected $dbalConnection;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * RequestSubscriber constructor.
     * @param string $pluginBaseDirectory
     * @param string $pluginName
     * @param Connection $dbalConnection
     * @param ConfigReader $configReader
     * @param ModelManager $modelManager
     */
    public function __construct(string $pluginBaseDirectory, string $pluginName, Connection $dbalConnection, ConfigReader $configReader, ModelManager $modelManager)
    {
        $this->pluginBaseDirectory = $pluginBaseDirectory;
        $this->dbalConnection = $dbalConnection;
        $this->config = $configReader->getByPluginName($pluginName);
        $this->modelManager = $modelManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_DispatchLoopStartup' => 'onPreRoutingDispatch',
            'Enlight_Controller_Action_PostDispatch_Backend_ScopRedirecter' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Backend' => 'onPostDispatchSecureBackend',
        ];
    }

    /**
     * Post Dispatch
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();
        $controller->View()->addTemplateDir($this->pluginBaseDirectory . '/Resources/views');
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchSecureBackend(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->extendsTemplate($this->pluginBaseDirectory . '/Resources/views/backend/scope_menu_item.tpl');
    }

    /**
     * Pre Dispatch, watches if requested Route matches a start_url of a redirect in DB and redirects accordingly
     *
     * @param \Enlight_Controller_EventArgs $args
     */
    public function onPreRoutingDispatch(\Enlight_Controller_EventArgs $args)
    {
        //get controller and response object
        $controller = $args->getSubject();
        $response = $controller->Response();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if ($request->getModuleName() === 'frontend') {
            $dontAddSlash = false;

            // Getting the plugin configuration
            if (\is_array($this->config)) {
                if (isset($this->config['dontAddSlash'])) {
                    $dontAddSlash = (bool)$this->config['dontAddSlash'];
                }
            }

            $requestedUri = $request->getRequestUri();

            /** @var ScopRedirecterRepository $redirecterRepo */
            $redirecterRepo = $this->modelManager->getRepository(Redirecter::class);

            $data = $redirecterRepo->getRedirect($requestedUri);

            $target = (string)$data[0]["targetUrl"];
            $trimmedTarget = \trim($target, "/");

            if ($target === '') {
                $basePath = Shopware()->Shop()->getBasePath();
                $unsetBasePath = \ltrim($requestedUri, $basePath);
                $data = $redirecterRepo->getRedirect("/" . $unsetBasePath);
                $target = (string)$data[0]["targetUrl"];
                $trimmedTarget = \trim($target, "/");
            }

            $httpCode = $data[0]["httpCode"];
            if ($target !== '') {
                if ($httpCode === 301 || $httpCode === 302) {
                    $this->redirectUrl($trimmedTarget, $httpCode, $response, $dontAddSlash);
                } else {
                    $this->redirectUrl($trimmedTarget, 302, $response, $dontAddSlash);
                }
            }
        }
    }

    /**
     * checks if target_url is a full url or path and redirects accordingly
     *
     * @param string $targetURL
     * @param int $targetCode
     * @param \Enlight_Controller_Response_ResponseHttp $resObj
     * @param bool $dontAddSlash
     */
    protected function redirectUrl(string $targetURL, int $targetCode, \Enlight_Controller_Response_ResponseHttp $resObj, bool $dontAddSlash)
    {
        if (\substr($targetURL, 0, 5) === "http:" || substr($targetURL, 0, 6) === "https:") {
            $resObj->setRedirect($targetURL, $targetCode);
        } elseif (\substr($targetURL, 0, 4) === "www.") {
            $resObj->setRedirect("http://" . $targetURL, $targetCode);
        } else {
            if (\strpos($targetURL, '?') || $dontAddSlash) {
                $targetURL = "/" . $targetURL;
            } else {
                $targetURL = "/" . $targetURL . "/";
            }

            $resObj->setRedirect($targetURL, $targetCode);
        }
    }
}
