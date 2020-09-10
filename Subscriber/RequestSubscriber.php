<?php

namespace ScopRedirecter\Subscriber;

use Enlight\Event\SubscriberInterface;
use Doctrine\DBAL\Connection;
use ScopRedirecter\Models\Redirecter;


class RequestSubscriber implements SubscriberInterface
{

    /**
     * @var string
     */
    private $pluginBaseDirectory;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * RequestSubscriber constructor.
     * @param $pluginBaseDirectory
     * @param Connection $dbalConnection
     */
    public function __construct($pluginBaseDirectory, Connection $dbalConnection)
    {
        $this->pluginBaseDirectory = $pluginBaseDirectory;
        $this->dbalConnection = $dbalConnection;
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
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(\Enlight_Event_EventArgs $args)
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
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPreRoutingDispatch(\Enlight_Event_EventArgs $args)
    {
        //get controller and response object
        $controller = $args->getSubject();
        $response = $controller->Response();

        /** @var \Enlight_Controller_Request_Request $request */

        $request = $controller->Request();

        if ($request->getModuleName() === 'frontend') {
            $requestedUri = $request->getRequestUri();

            $redirecterRepo = Shopware()->Container()->get('models')->getRepository(Redirecter::class);

            $data = $redirecterRepo->getRedirect($requestedUri);

            $target = (string)$data[0]["targetUrl"];

            $trimmedTarget = trim($target, "/");

            if ($target === '' ){
                $basePath = Shopware()->Shop()->getBasePath();
                $unsetBasePath = ltrim($requestedUri, $basePath);
                $data = $redirecterRepo->getRedirect("/" . $unsetBasePath);
                $target = (string)$data[0]["targetUrl"];
                $trimmedTarget = trim($target, "/");
            }

            $httpCode = $data[0]["httpCode"];
            if ($target !== '' ) {
                if($httpCode === 301 || $httpCode === 302){
                    $this->redirectUrl($trimmedTarget, $httpCode, $response, $requestUriType);
                }else{
                    $this->redirectUrl($trimmedTarget, 302, $response);
                }
            }
        }
    }

    /**
     * checks if target_url is a full url or path and redirects accordingly
     *
     * @param string $targetURL
     * @param string $targetCode
     * @param object $resObj
     */
    protected function redirectUrl($targetURL, $targetCode, $resObj){

        if(substr($targetURL, 0,5) === "http:" || substr($targetURL, 0,6) === "https:" ){

            $resObj->setRedirect($targetURL, $targetCode);
        }elseif(substr($targetURL, 0,4) === "www."){

            $resObj->setRedirect("http://" . $targetURL, $targetCode);
        }else{
            if (strpos($targetURL, '?')) {
                $targetURL = "/" . $targetURL;
            } else {
                $targetURL = "/" . $targetURL . "/";
            }

            $resObj->setRedirect($targetURL , $targetCode);
        }
    }
}
