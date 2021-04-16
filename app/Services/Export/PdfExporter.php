<?php

declare(strict_types=1);

namespace App\Services\Export;

use Illuminate\Contracts\View\View;

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
     * @param View|null $view
     */
    public function __construct(?View $view = null)
    {
        $this->view = $view;
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
     * Fluent set the view
     *
     * @param array $data
     * @return PdfExporter
     */
    public function withData(array $data): self
    {
        $this->view->with('data', $data);
        return $this;
    }
}
