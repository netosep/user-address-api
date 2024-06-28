<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Address\AddressStoreFormRequest;
use App\Http\Requests\Address\AddressUpdateFormRequest;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    /**
     * @OA\Get(
     *   tags={"User Address"},
     *   path="/api/user/address",
     *   summary="Show all User Addresses",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="country", in="query", description="Filter by country (case sensitive)", @OA\Schema(type="string")),
     *   @OA\Parameter(name="state", in="query", description="Filter by state (case sensitive)", @OA\Schema(type="string")),
     *   @OA\Parameter(name="city", in="query", description="Filter by city (case sensitive)", @OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="User Addresses response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200),
     *       @OA\Property(
     *         property="result",
     *         type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="total", type="integer", example=1),
     *         @OA\Property(property="per_page", type="integer", example=1),
     *         @OA\Property(property="last_page", type="integer", example=1),
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Address"))
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Address::where('user_id', $user->id);

        $filters = [
            'country' => $request->query('country'),
            'state' => $request->query('state'),
            'city' => $request->query('city'),
        ];

        foreach ($filters as $field => $value) {
            if ($value) {
                $query->where($field, $value);
            }
        }

        $userAddresses = $this->transformPaginate($query->paginate(self::ITEMS_PER_PAGE));

        return $this->jsonResponse($userAddresses);
    }

    /**
     * @OA\Get(
     *   tags={"User Address"},
     *   path="/api/user/address/{id}",
     *   summary="Show User Address",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string"),
     *     description="The Address ID"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User Address response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200),
     *       @OA\Property(property="result", type="object", ref="#/components/schemas/Address")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function show(Request $request, string $id)
    {
        $address = $this->findOrFail(Address::class, $id, $request->user()->id);

        return $this->jsonResponse($address);
    }

    /**
     * @OA\Post(
     *   tags={"User Address"},
     *   path="/api/user/address",
     *   summary="Create new User Address",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     @OA\JsonContent(type="object", ref="#/components/schemas/AddressCreateRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User Address created response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Address created successfully"),
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200),
     *       @OA\Property(property="result", type="object", ref="#/components/schemas/Address")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error response",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/ValidationException")
     *   )
     * )
     */
    public function store(Request $request)
    {
        $this->validateRequest(AddressStoreFormRequest::class, $request);

        $user = $request->user();
        $address = new Address();
        $address->user_id = $user->id;
        $address->fill($request->all());
        $address->save();

        return $this->jsonResponse($address, 'Address created successfully', JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *   tags={"User Address"},
     *   path="/api/user/address/{id}",
     *   summary="Update User Address",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string"),
     *     description="The Address ID"
     *   ),
     *   @OA\RequestBody(
     *     @OA\JsonContent(type="object", ref="#/components/schemas/AddressUpdateRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User Address updated response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Address updated successfully"),
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200),
     *       @OA\Property(property="result", type="object", ref="#/components/schemas/Address")
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad request response",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/BadRequestException")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error response",
     *     @OA\JsonContent(type="object", ref="#/components/schemas/ValidationException")
     *   )
     * )
     */
    public function update(Request $request, string $id)
    {
        $validator = $this->validateRequest(AddressUpdateFormRequest::class, $request);
        $this->validateExistsFieldRequest($request, $validator);

        $address = $this->findOrFail(Address::class, $id, $request->user()->id);
        $address->fill($request->all());
        $address->save();

        return $this->jsonResponse($address, 'Address updated successfully');
    }

    /**
     * @OA\Delete(
     *   tags={"User Address"},
     *   path="/api/user/address/{id}",
     *   summary="Delete User Address",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string"),
     *     description="The Address ID"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User Address deleted response",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Item deleted successfully"),
     *       @OA\Property(property="success", type="boolean", default=true, example=true),
     *       @OA\Property(property="code", type="integer", example=200)
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated response",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated.")
     *     )
     *   )
     * )
     */
    public function destroy(Request $request, string $id)
    {
        $address = $this->findOrFail(Address::class, $id, $request->user()->id);
        $address->delete();

        return $this->jsonResponse(message: 'Item deleted successfully');
    }
}
