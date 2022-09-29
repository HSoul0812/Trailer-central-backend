<?php

declare(strict_types=1);

namespace App\Services\Export;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * Can be used for a simple PDF export tasks
 */
abstract class PdfExporter
{
    /**
     * @var View|null
     */
    protected $view;

    /**
     * @var callable
     */
    protected $afterRenderHandler;

    /**
     * @var callable
     */
    protected $afterLoadHtmlHandler;

    public function __construct(?View $view = null, ?callable $afterRenderFn = null,  ?callable $afterLoadHtmlFn = null)
    {
        $this->view = $view;
        $this->afterRenderHandler = $afterRenderFn ?? static function (): void {
        };
        $this->afterLoadHtmlHandler = $afterLoadHtmlFn ?? static function (): void {
        };
    }

    /**
     * Fluent set the view
     *
     * @param View $view
     * @return PdfExporter
     */
    public function withView(View $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Fluent set the after render handler
     *
     * @param callable $fn
     * @return PdfExporter
     */
    public function afterRender(callable $fn): self
    {
        $this->afterRenderHandler = $fn;
        return $this;
    }

    /**
     * Fluent set the after load html handler
     *
     * @param callable $fn
     * @return PdfExporter
     */
    public function afterLoadHtml(callable $fn): self
    {
        $this->afterLoadHtmlHandler = $fn;
        return $this;
    }

    /**
     * Fluent set the view
     *
     * @param array|Collection|LazyCollection $data
     * @return PdfExporter
     */
    public function withData($data): self
    {
        $this->view->with('data', $data);
        return $this;
    }
}
