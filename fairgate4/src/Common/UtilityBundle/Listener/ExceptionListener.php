<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Kernel exception listener
 */
class ExceptionListener
{
    /**
     * The template engine
     *
     * @var EngineInterface
     */
    private $templateEngine;

    /**
     * Service Container
     * @var Obeject
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param EngineInterface $templateEngine The template engine
     */
    public function __construct(EngineInterface $templateEngine, ContainerInterface $container)
    {
        $this->templateEngine = $templateEngine;
        $this->container = $container;
    }

    /**
     * Handles a kernel exception and returns a relevant response.
     *
     * Aims to deliver content to the user that explains the exception, rather than falling
     * back on symfony's exception handler which displays a less verbose error message.
     *
     * @param GetResponseForExceptionEvent $event The exception event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        /* Writing exception to error log file */
        // $this->logErrors($event);
        // Checking whether debug mode is ON or not
        switch ($this->container->get('kernel')->isDebug()) {

            // If the debug mode is on, the need to display error message as such for easy development
            case true : 
                echo $event->getException()->getMessage(); 
                exit;
                break;

            // If debug mode ia Off, then display error pages according to the error categories.
            case false :

                // Some errors don't have any error code and messages. So for that kind of error we set error code as 500.
                try {
                    $exception = $event->getException();
                    if(method_exists($exception, 'getStatusCode')){
                        $errorCode = $event->getException()->getStatusCode();
                    }else{
                        $errorCode = '500'; 
                    }
                } catch (Exception $e) {
                    $errorCode = '500';
                }
              
                $club=$this->container->get('club');
                $applicationArea = $club->get('applicationArea');
                $this->container->get('translator')->setLocale($club->get('default_system_lang'));
                $templating = $this->container->get('templating');
                $errorpageTitle =  $this->container->get('translator')->trans('ERRORPAGE_'.$errorCode.'_BACKEND_TITLE') ;

                $response = new Response($templating->render('TwigBundle:Exception:exception_full.html.twig', array('status_code'    => $errorCode,  'applicationArea'=> $applicationArea, 'clubTitle' => $club->get('title'),'pageTitle'=>$errorpageTitle)));

                // Passing response to custom error pages
                $event->setResponse($response);
        }
    }

    /**
     * Function to write exceptions to error log file.
     */
    private function logErrors($event)
    {
        $errorlogfile = "../fairgate4/app/logs/error.log";
        $current = file_get_contents($errorlogfile);
        if ($current != '') {
            $current .= "\n\n";
        }
        $current .= '[' . date('Y-m-d h:i:s') . ']: ' . $event->getException()->getMessage();
        file_put_contents($errorlogfile, $current);
    }
}