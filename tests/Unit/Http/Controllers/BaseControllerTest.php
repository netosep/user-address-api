<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Http\Exceptions\ValidationException;
use App\Http\Requests\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Tests\TestCase;
use Tests\Unit\Http\Controllers\Mock\MockFormRequest;

class BaseControllerTest extends TestCase
{
    private BaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BaseController();
    }

    public function testJsonResponseWithSuccessAndNoData()
    {
        $response = $this->controller->jsonResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());

        $expected = ['success' => true, 'code' => JsonResponse::HTTP_OK];
        $this->assertEquals($expected, $response->getData(true));
    }

    public function testJsonResponseWithSuccessAndData()
    {
        $data = ['id' => 1, 'name' => 'John Doe'];
        $response = $this->controller->jsonResponse($data);

        $expected = ['success' => true, 'code' => JsonResponse::HTTP_OK, 'result' => $data];
        $this->assertEquals($expected, $response->getData(true));
    }

    public function testJsonResponseWithSuccessAndMessage()
    {
        $message = 'Operation completed successfully';
        $response = $this->controller->jsonResponse(null, $message);

        $expected = ['success' => true, 'code' => JsonResponse::HTTP_OK, 'message' => $message];
        $this->assertEquals($expected, $response->getData(true));
    }

    public function testJsonResponseWithCustomCode()
    {
        $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        $response = $this->controller->jsonResponse(null, null, $code);

        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testJsonResponseWithSuccessAndDataAndMessageAndCustomCode()
    {
        $data = ['id' => 1, 'name' => 'John Doe'];
        $message = 'Operation completed successfully';
        $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        $response = $this->controller->jsonResponse($data, $message, $code);

        $expected = ['success' => true, 'code' => $code, 'result' => $data, 'message' => $message];
        $this->assertEquals($expected, $response->getData(true));
    }

    public function testMakeValidatorReturnsValidatorWithRulesFromFormRequest()
    {
        $class = MockFormRequest::class;
        $request = new Request();

        $validator = $this->controller->makeValidator($class, $request);

        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertEquals(['field' => ['required']], $validator->getRules());
    }

    public function testMakeValidatorHandlesArrayRequest()
    {
        $class = MockFormRequest::class;
        $request = ['field' => 'value'];

        $validator = $this->controller->makeValidator($class, $request);

        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertEquals(['field' => ['required']], $validator->getRules());
    }

    public function testMakeValidatorThrowsInvalidArgumentExceptionWhenClassIsNotFormRequest()
    {
        $class = 'stdClass';
        $request = new Request();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class must be instance of FormRequest');

        $this->controller->makeValidator($class, $request);
    }

    public function testValidateRequestWithValidData()
    {
        $formRequest = new class extends FormRequest
        {
            public function rules(): array
            {
                return ['name' => 'required|string'];
            }
        };
        $request = new Request(['name' => 'John Doe']);

        $this->controller->validateRequest(get_class($formRequest), $request);
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testValidateRequestWithInvalidData()
    {
        $this->expectException(ValidationException::class);

        $request = new Request(['name' => null]);

        $this->controller->validateRequest(MockFormRequest::class, $request);
    }

    public function testValidateRequestWithInvalidFormRequestClass()
    {
        $this->expectException(\InvalidArgumentException::class);

        $invalidFormRequest = 'stdClass';
        $request = new Request(['name' => 'John Doe']);

        $this->controller->validateRequest($invalidFormRequest, $request);
    }

    public function testValidateRequestWithArrayRequest()
    {
        $request = ['field' => 'Foo Bar'];

        $this->controller->validateRequest(MockFormRequest::class, $request);
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testValidateRequestWithEmptyRequest()
    {
        $this->expectException(ValidationException::class);

        $request = new Request([]);

        $this->controller->validateRequest(MockFormRequest::class, $request);
    }

    public function testTransformPaginateReturnsDataAsArray()
    {
        $items = new Collection([1, 2, 3, 4, 5]);
        $paginator = new LengthAwarePaginator($items, $items->count(), 10);

        $transformedData = $this->controller->transformPaginate($paginator);

        $this->assertIsArray($transformedData['data']);
        $this->assertEquals($items->toArray(), $transformedData['data']);
    }

    public function testTransformPaginateReturnsCurrentPage()
    {
        $items = new Collection([1, 2, 3, 4, 5]);
        $paginator = new LengthAwarePaginator($items, $items->count(), 10, 2);

        $transformedData = $this->controller->transformPaginate($paginator);

        $this->assertEquals(2, $transformedData['current_page']);
    }

    public function testTransformPaginateReturnsTotal()
    {
        $items = new Collection([1, 2, 3, 4, 5]);
        $paginator = new LengthAwarePaginator($items, $items->count(), 10);

        $transformedData = $this->controller->transformPaginate($paginator);

        $this->assertEquals(5, $transformedData['total']);
    }

    public function testTransformPaginateReturnsPerPage()
    {
        $items = new Collection([1, 2, 3, 4, 5]);
        $paginator = new LengthAwarePaginator($items, $items->count(), 10);

        $transformedData = $this->controller->transformPaginate($paginator);

        $this->assertEquals(10, $transformedData['per_page']);
    }

    public function testTransformPaginateReturnsLastPage()
    {
        $items = new Collection([1, 2, 3, 4, 5]);
        $paginator = new LengthAwarePaginator($items, $items->count(), 10);

        $transformedData = $this->controller->transformPaginate($paginator);

        $this->assertEquals(1, $transformedData['last_page']);
    }
}
