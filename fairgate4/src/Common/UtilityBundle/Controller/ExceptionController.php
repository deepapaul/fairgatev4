<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Common\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * ExceptionController.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionController
{

    use ContainerAwareTrait;

    /**
     * Converts an Exception to a Response.
     *
     * @param Request              $request   The request
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     * @param string               $_format   The format to use for rendering (html, xml, ...)
     *
     * @return Response
     *
     * @throws \InvalidArgumentException When the exception template does not exist
     */
    public function showExceptionAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null, $_format = 'html')
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $code = $exception->getStatusCode();
        $templating = $this->container->get('templating');
        $club = $this->container->get('club');
        $applicationArea = $club->get('applicationArea');
        $this->container->get('translator')->setLocale($club->get('default_system_lang'));
        $errorpageTitle = '';
        if ($applicationArea == 'backend') {
            $transVar = 'ERRORPAGE_' . $code . '_BACKEND_TITLE';
            $errorpageTitle = $this->container->get('translator')->trans($transVar);
        } else {
            $code = 404;
            $transVar = 'ERRORPAGE_' . $code . '_BACKEND_TITLE';
            $errorpageTitle = $this->container->get('translator')->trans($transVar);
        }

        return new Response($templating->render(
                $this->findTemplate($request, $_format, $code, $this->container->get('kernel')->isDebug(), $templating), array(
                'status_code' => $code,
                'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception' => $exception,
                'logger' => $logger,
                'currentContent' => $currentContent,
                'applicationArea' => $applicationArea,
                'pageTitle' => $errorpageTitle,
                'clubTitle' => $club->get('title')
                )
        ));
    }

    /**
     * @param int     $startObLevel
     *
     * @return string
     */
    protected function getAndCleanOutputBuffering($startObLevel)
    {
        // ob_get_level() never returns 0 on some Windows configurations, so if
        // the level is the same two times in a row, the loop should be stopped.
        $previousObLevel = null;
        $currentContent = '';

        while (($obLevel = ob_get_level()) > $startObLevel && $obLevel !== $previousObLevel) {
            $previousObLevel = $obLevel;
            $currentContent .= ob_get_clean();
        }

        return $currentContent;
    }

    /**
     * Function to find template
     * @param Request $request    Request
     * @param string  $format     Format
     * @param int     $code       An HTTP response status code
     * @param bool    $debug      Debgu
     * @param HTML    $templating Template
     *
     * @return TemplateReference
     */
    protected function findTemplate($request, $format, $code, $debug, $templating)
    {
        /*
          $name = $debug ? 'exception' : 'error';
          if ($debug && 'html' == $format) {
          $name = 'exception_full';
          }

         */
        $name = 'exception_full';
        // when not in debug, try to find a template for the specific HTTP status code and format
        if (!$debug) {
            $template = new TemplateReference('TwigBundle', 'Exception', $name . $code, $format, 'twig');
            if ($templating->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, $format, 'twig');
        if ($templating->exists($template)) {
            return $template;
        }


        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return new TemplateReference('TwigBundle', 'Exception', $name, 'html', 'twig');
    }

    // to be removed when the minimum required version of Twig is >= 2.0
    protected function templateExists($template)
    {
        $templating = $this->container->get('templating');
        $templating = $this->container->get('templating');
        $loader = $templating->getLoader();
        if ($loader instanceof \Twig_ExistsLoaderInterface) {
            return $loader->exists($template);
        }

        try {
            $loader->getSource($template);

            return true;
        } catch (\Twig_Error_Loader $e) {

        }

        return false;
    }
}
