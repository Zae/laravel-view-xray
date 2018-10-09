<?php

declare(strict_types=1);

namespace BeyondCode\ViewXray;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ReflectionFunction;
use ReflectionMethod;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class XrayMiddleware
 *
 * @package BeyondCode\ViewXray
 */
class XrayMiddleware
{
    /** @var Xray */
    private $xray;

    /**
     * XrayMiddleware constructor.
     *
     * @param Xray $xray
     */
    public function __construct(Xray $xray)
    {
        $this->xray = $xray;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->xray->isEnabled()) {
            return $next($request);
        }

        $this->xray->boot();

        /** @var SymfonyResponse $response */
        $response = $next($request);

        if ($response->isRedirection() || $this->notXRayable($request, $response)) {
            return $response;
        }

        if (is_null($response->exception) && !is_null($this->xray->getBaseView())) {
            // Modify the response to add the Debugbar
            $this->injectXrayBar($response);
        }

        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return bool
     */
    public function notXRayable(Request $request, SymfonyResponse $response) : bool
    {
        return (
            $response->headers->has('Content-Type') &&
            strpos($response->headers->get('Content-Type'), 'html') === false
        )
        || $request->getRequestFormat() !== 'html'
        || $response->getContent() === false;
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route $route
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getRouteInformation($route) : array
    {
        if (!is_a($route, Route::class)) {
            return [];
        }

        $uri = sprintf(
            '%s %s',
            head($route->methods()),
            $route->uri()
        );

        $action = $route->getAction();

        $result = [
           'uri' => $uri ?: '-',
        ];

        $result = array_merge($result, $action);

        if ($this->isControllerAction($action)) {
            list($controller, $method) = explode('@', $action['controller']);

            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflector = new ReflectionMethod($controller, $method);
            }

            unset($result['uses']);
        } elseif ($this->isClosureAction($action)) {
            $reflector = new ReflectionFunction($action['uses']);
        }

        if (isset($reflector)) {
            $filename = ltrim(str_replace(base_path(), '', $reflector->getFileName()), '/');
            $result['file'] = sprintf(
                '%s:%s-%s',
                $filename,
                $reflector->getStartLine(),
                $reflector->getEndLine()
            );
        }

        return $result;
    }

    /**
     * @param array $action
     *
     * @return bool
     */
    private function isControllerAction(array $action) : bool
    {
        return isset($action['controller']) && strpos($action['controller'], '@') !== false;
    }

    /**
     * @param array $action
     *
     * @return bool
     */
    private function isClosureAction(array $action) : bool
    {
        return isset($action['uses']) && $action['uses'] instanceof Closure;
    }

    /**
     * @param $response
     *
     * @throws \ReflectionException
     */
    protected function injectXrayBar(Response $response)
    {
        $routeInformation = $this->getRouteInformation(app('router')->current());

        $content = $response->getContent();

        $xrayJs = file_get_contents(__DIR__.'/../resources/assets/xray.js');
        $xrayCss = file_get_contents(__DIR__.'/../resources/assets/xray.css');
        $xrayBar = view('xray::xray', [
            'routeInformation' => $routeInformation,
            'viewPath' => str_replace(base_path(), '', $this->xray->getBaseView()->getPath()),
            'viewName' => $this->xray->getBaseView()->name(),
        ]);

        $renderedContent = sprintf(
            '<script>%s</script><style>%s</style>%s',
            $xrayJs,
            $xrayCss,
            $xrayBar
        );

        $pos = strripos($content, '</body>');
        if ($pos !== false) {
            $content = sprintf(
                '%s%s%s',
                substr($content, 0, $pos),
                $renderedContent,
                substr($content, $pos)
            );
        } else {
            $content .= $renderedContent;
        }

        // Update the new content and reset the content length
        $response->setContent($content);
        $response->headers->remove('Content-Length');
    }
}