<?php

declare(strict_types=1);

namespace BeyondCode\ViewXray;

use View as ViewFacade;
use Illuminate\View\View;
use File;

/**
 * Class Xray
 *
 * @package BeyondCode\ViewXray
 */
class Xray
{
    /** @var View $baseView */
    protected $baseView;

    /** @var int $viewId */
    protected $viewId = 0;

    /**
     * Boot the Xray class
     */
    public function boot()
    {
        ViewFacade::composer('*', function (View $view) {
            if (is_null($this->baseView)) {
                $this->baseView = clone $view;
            }

            if ($this->isEnabledForView($view->getName())) {
                $this->modifyView($view);
            }
        });
    }

    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        return (boolean)config('xray.enabled');
    }

    /**
     * @param $view
     */
    public function modifyView(View $view)
    {
        $viewContent = file_get_contents($view->getPath());

        $file = tempnam(sys_get_temp_dir(), $view->getName());

        $viewContent = preg_replace_callback(
            '/(@section\(([^))]+)\)+)(.*?)(@endsection|@show|@overwrite|@append)/s',
            function (array $matches) use ($view) {
                ++$this->viewId;

                $sectionName = str_replace(["'", '"'], '', $matches[2]);

                return sprintf(
                    '%1$s<!--XRAY START %6$s %4$s@section:%7$s %5$s-->%2$s<!--XRAY END %6$s-->%3$s',
                    $matches[1],
                    $matches[3],
                    $matches[4],
                    $view->getName(),
                    $view->getPath(),
                    $this->viewId,
                    $sectionName
                );
            },
            $viewContent
        );

        $viewContent = sprintf(
            '<!--XRAY START %1$s %2$s %3$s-->%4$s%5$s%4$s<!--XRAY END %1$s-->',
            $this->viewId,
            $view->getName(),
            $view->getPath(),
            PHP_EOL,
            $viewContent
        );

        file_put_contents($file, $viewContent, LOCK_EX);

        $view->setPath($file);

        ++$this->viewId;
    }

    /**
     * @return View
     */
    public function getBaseView() : View
    {
        return $this->baseView;
    }

    /**
     * @param string $viewName
     *
     * @return bool
     */
    protected function isEnabledForView(string $viewName): bool
    {
        return !in_array($viewName, config('xray.excluded', []), true);
    }
}
