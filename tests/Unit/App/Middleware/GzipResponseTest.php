<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\GzipResponse;
use Dingo\Api\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\Common\TestCase;
use Throwable;

class GzipResponseTest extends TestCase
{
    public function testItWillNotCompressRequestWithoutProperHeader(): void
    {
        $request = new Request();

        $this->assertResponseNotEncoded($request);

        // Set the accept-encoding header to brotli, middleware shouldn't do anything
        // because we only want to compress into gzip
        $request->headers->set(GzipResponse::HEADER_ACCEPT_ENCODING, 'br');

        $this->assertResponseNotEncoded($request);
    }

    public function testItWillCompressDingoResponse(): void
    {
        $this->assertResponseIsEncoded(function () {
            return Response::makeFromJson(response()->json([
                'foo' => 'bar',
            ]));
        });
    }

    public function testItWillCompressJsonResponse(): void
    {
        $this->assertResponseIsEncoded(function () {
            return response()->json([
                'foo' => 'bar',
            ]);
        });
    }

    public function testItWillNotCompressStringResponse(): void
    {
        $request = new Request();

        $request->headers->set(GzipResponse::HEADER_ACCEPT_ENCODING, 'gzip');

        $middleware = new GzipResponse();

        $value = 'some string';

        /** @var string $response */
        $response = $middleware->handle($request, fn () => $value);

        $this->assertEquals($value, $response);
    }

    /**
     * @throws Throwable
     */
    public function testItWillNotCompressOtherResponseType(): void
    {
        $request = new Request();

        $request->headers->set(GzipResponse::HEADER_ACCEPT_ENCODING, 'gzip');

        $middleware = new GzipResponse();

        /** @var View $response */
        $response = $middleware->handle($request, function () {
            return view('stub.for-test');
        });

        $content = $response->render();

        $this->assertInstanceOf(View::class, $response);
        $this->assertStringContainsString('This view is for testing only.', $content);
    }

    private function assertResponseNotEncoded(Request $request): void
    {
        $middleware = new GzipResponse();

        /** @var JsonResponse $response */
        $response = $middleware->handle($request, function () {
            return response()->json([
                'foo' => 'bar',
            ]);
        });

        $contentEncoding = $response->headers->get(GzipResponse::HEADER_CONTENT_ENCODING);
        $value = data_get($response->getOriginalContent(), 'foo');

        $this->assertNull($contentEncoding);
        $this->assertEquals('bar', $value);
    }

    private function assertResponseIsEncoded(callable $controllerLogic): void
    {
        $request = new Request();

        $request->headers->set(GzipResponse::HEADER_ACCEPT_ENCODING, 'gzip');

        $middleware = new GzipResponse();

        /** @var JsonResponse $response */
        $response = $middleware->handle($request, $controllerLogic);

        $content = $response->content();
        $contentLength = $response->headers->get('Content-Length');
        $contentEncoding = $response->headers->get(GzipResponse::HEADER_CONTENT_ENCODING);

        $this->assertNotNull($contentLength);
        $this->assertEquals('gzip', $contentEncoding);

        $realContent = json_decode(gzdecode($content, $contentLength), true);

        $value = data_get($realContent, 'foo');

        $this->assertEquals('bar', $value);
    }
}
