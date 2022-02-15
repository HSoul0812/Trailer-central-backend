<?php

namespace App\Http\Controllers\v1\Dms\Docupilot;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Docupilot\GetDocumentTemplateRequest;
use App\Http\Requests\Dms\Docupilot\GetDocumentTemplatesRequest;
use App\Http\Requests\Dms\Docupilot\UpdateDocumentTemplatesRequest;
use App\Repositories\Dms\Docupilot\DocumentTemplatesRepositoryInterface;
use App\Transformers\Dms\Docupilot\DocumentTemplateTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class DocumentTemplatesController
 * @package App\Http\Controllers\v1\Dms\Docupilot
 */
class DocumentTemplatesController extends RestfulControllerV2
{
    /**
     * @var DocumentTemplatesRepositoryInterface
     */
    private $templatesRepository;

    /**
     * DocumentTemplatesController constructor.
     * @param DocumentTemplatesRepositoryInterface $templatesRepository
     */
    public function __construct(DocumentTemplatesRepositoryInterface $templatesRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'show', 'update']);

        $this->templatesRepository = $templatesRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/dms/docupilot/document-templates",
     *     description="Retrieve a list of docupilot document templates",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer identifier",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of document",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type_service",
     *         in="query",
     *         description="Type of document",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of docupilot document templates",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @throws NoObjectIdValueSetException
     */
    public function index(Request $request): Response
    {
        $request = new GetDocumentTemplatesRequest($request->all());

        if ($request->validate()) {
            $templates = $this->templatesRepository->getAll($request->all());
            return $this->response->collection($templates, new DocumentTemplateTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/dms/docupilot/document-templates/{id}",
     *     description="Retrieve a document template",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Document Template ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a document template",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @throws NoObjectIdValueSetException
     */
    public function show(int $templateId, Request $request): Response
    {
        $request = new GetDocumentTemplateRequest(array_merge($request->all(), ['template_id' => $templateId]));

        if ($request->validate()) {
            $template = $this->templatesRepository->get($request->all());
            return $this->response->item($template, new DocumentTemplateTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(
     *     path="/api/dms/docupilot/document-templates/{id}",
     *     description="Update a document template",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Document Template ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Text type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_quote",
     *         in="query",
     *         description="Text type_quote",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_deal",
     *         in="query",
     *         description="Text type_deal",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type_service",
     *         in="query",
     *         description="Text type_service",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns updated template identifier",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @throws NoObjectIdValueSetException
     */
    public function update(int $templateId, Request $request): Response
    {
        $request = new UpdateDocumentTemplatesRequest(array_merge($request->all(), ['template_id' => $templateId]));

        if ($request->validate()) {
            $template = $this->templatesRepository->update($request->all());
            return $this->updatedResponse($template->id);
        }

        return $this->response->errorBadRequest();
    }
}
