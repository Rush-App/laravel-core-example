<?php

namespace RushApp\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    protected string $modelClassController;
    protected object $baseModel;
    protected string $requestClassController = '';
    protected array $expandParamsName = [];
    protected bool $onlyUserData = false;
    protected array $validationRules = [];

    public function __construct()
    {
        $this->baseModel = new $this->modelClassController;
        if ($this->requestClassController) {
            $requestClass = new $this->requestClassController;
            $this->validationRules = $requestClass->rules();
        }
    }

    public function getAll(Request $request)
    {
        $result = $this->baseModel->getCollections($request, $this->expandParamsName, $this->onlyUserData, true);

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function getOne(Request $request)
    {
        $result = $this->baseModel->getCollections($request, $this->expandParamsName, $this->onlyUserData, false);

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function createOne(Request $request)
    {
        if ($this->isValidateError($request)) {
            return $this->isValidateError($request);
        }

        $result = $this->baseModel->createOne($request);

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function updateOne(Request $request)
    {
        if ($this->isValidateError($request)) {
            return $this->isValidateError($request);
        }

        $result = $this->baseModel->updateOne($request, Auth::id());

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function deleteOne(Request $request)
    {
        $result =  $this->baseModel->deleteOne($request, Auth::id());

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function updateOneForAdmin(Request $request)
    {
        if ($this->isValidateError($request)) {
            return $this->isValidateError($request);
        }

        $result = $this->baseModel->updateOneForAdmin($request);

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function deleteOneForAdmin(Request $request)
    {
        $result = $this->baseModel->deleteOneForAdmin($request);

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }

    public function isValidateError (Request $request) {
        $validator = Validator::make($request->all(), $this->validationRules);

        return $validator->fails()
            ? response()->json($validator->errors(), 422)
            : false;
    }

    public function isDataValidateError (array $data) {
        $validator = Validator::make($data, $this->validationRules);

        return $validator->fails()
            ? response()->json($validator->errors(), 422)
            : false;
    }

    protected function successResponse($responseData)
    {
        return response()->json($responseData, 200);
    }

    protected function responseWithError(string $error, int $code)
    {
        return response()->json(['error' => $error], $code);
    }
}
