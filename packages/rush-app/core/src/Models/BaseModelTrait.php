<?php

namespace RushApp\Core\Models;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Monolog\Logger;
use RushApp\Core\Enums\ModelRequestParameters;
use RushApp\Core\Exceptions\CoreHttpException;
use RushApp\Core\Services\LoggingService;

trait BaseModelTrait
{
    use CoreBaseModelTrait;

    /**
     * Return a collections of one or more records with or without translation
     *
     * @param Request $request
     * @param array $withRelationNames
     * @return Builder
     */
    public function getQueryBuilder(Request $request, array $withRelationNames): Builder
    {
        $params = $request->all();

        //checking for the issuance of data that belongs to the current user
        if ($this->isOwner()) {
            $params['user_id'] = Auth::id();
        }

        /** @var Builder $query */
        $query = class_exists($this->modelTranslationClass)
            ? $this->getTranslationQuery($params['language_id'])
            : (new $this->modelClass)->query();

        //adding data from translation main table
        if (class_exists($this->modelTranslationClass)) {
            $query->addSelect($this->getTranslationTableName().'.*');
        }

        //adding data from main table
        $query->addSelect($this->tablePluralName.'.*');

        $this->addWithData($query, $params, $withRelationNames);
        $this->addQueryOptions($query, $params);

        //Parameters for "where", under what conditions the request will be displayed
        $whereParams = $this->getQueryParams($this->filteringForParams($params));

        return $query->where($whereParams);
    }

    public function getQueryBuilderOne(Request $request, array $withRelationNames): Builder
    {
        $entityId = $request->route($this->getTableSingularName());
        $query = $this->getQueryBuilder($request, $withRelationNames);

        return $query->where($this->getTable().'.id', $entityId);
    }

    protected function addWithData(Builder $query, array $params, array $withRelationNames)
    {
        if (!empty($params[ModelRequestParameters::WITH])) {
            $requestedWithParameters = $this->parseParameterWithAdditionalValues($params[ModelRequestParameters::WITH]);

            $withRelations = [];
            foreach ($requestedWithParameters as $withParameter) {
                if (in_array($withParameter['name'], $withRelationNames) && method_exists($this, $withParameter['name'])) {
                    $withRelations[$withParameter['name']] = function ($q) use ($withParameter) {
                        if (isset($withParameter['values'])) {
                            $tableName = Str::plural($withParameter['name']);
                            $values = $this->filterExistingColumnsInTable($withParameter['values'], $tableName);

                            $relationsFields = array_map(fn ($value) => $tableName.'.'.$value, $values);
                            $relationsFields ? $q->select($relationsFields) : $q->select('*');
                        } else {
                            $q->select('*');
                        }
                    };
                }
            }
            $query->with($withRelations);
        }
    }

    protected function addQueryOptions(Builder $query, array $params)
    {
        //Select only this data
        if(!empty($select = $this->getValueForExistingTableColumns($params, ModelRequestParameters::SELECTED_FIELDS))) {
            $query->select($select);
        }

        //Sort by a given field
        if(!empty($params[ModelRequestParameters::ORDER_BY_FIELD])) {
            $parsedOrderParameters = $this->parseParameterWithAdditionalValues($params[ModelRequestParameters::ORDER_BY_FIELD]);
            if ($parsedOrderParameters->isNotEmpty()) {
                $query->orderBy($parsedOrderParameters->get('name'), $parsedOrderParameters->get('values', 'asc'));
            }
        }

        //give data where some field is whereNotNull
        if (
            !empty($params[ModelRequestParameters::WHERE_NOT_NULL]) &&
            !empty($whereNotNull = $this->getValueForExistingTableColumns($params, ModelRequestParameters::WHERE_NOT_NULL))
        ) {
            $query->whereNotNull($whereNotNull);
        }

        //Get limited data
        if(!empty($params[ModelRequestParameters::LIMIT])) {
            $query->limit($params[ModelRequestParameters::LIMIT]);
        }
    }

    /**
     * Creating a new record in the database
     * Return the created record
     *
     * @param Request $request
     * @return array
     */
    public function createOne(Request $request): array
    {
        $request->merge(["user_id" => Auth::id()]);

        try {
            /** @var Model|static $mainModel */
            $mainModel = $this->modelClass::create($request->all());

            $modelAttributes = $mainModel->getAttributes();
            if ($this->isTranslatable()) {
                $translationModel = $mainModel->translations()->create($request->all());
                $modelAttributes = array_merge($translationModel->getAttributes(), $modelAttributes);
            }

            return $modelAttributes;
        } catch (\Exception $e) {
            LoggingService::CRUD_errorsLogging('Model creation error - '.$e, Logger::CRITICAL);
            throw new CoreHttpException(409, __('core::error_messages.save_error'));
        }
    }

    /**
     * Updates the model and then returns it (with checking for compliance of the record to the user)
     *
     * @param Request $request
     * @param string $columnName - column name to check whether a record matches a specific user
     * @param $valueForColumnName - column value to check whether a record matches a specific user
     * @return array
     */
    public function updateOne(Request $request, $valueForColumnName, string $columnName = 'user_id'): array
    {
        $model = $this->getOneRecord($this->getRequestId($request));
        if (!$model) {
            throw new CoreHttpException(404, __('core::error_messages.not_found'));
        }

        if (!$this->canDoActionWithModel($model, $columnName, $valueForColumnName)) {
            throw new CoreHttpException(403, __('core::error_messages.permission_denied'));
        }

        return $this->updateOneRecord($model, $request->all());
    }


    /**
     * Delete one record with checking for compliance of the record to the user
     *
     * @param string $columnName - column name to check whether a record matches a specific user
     * @param $valueForColumnName - column value to check whether a record matches a specific user
     * @param Request $request
     * @return void
     */
    public function deleteOne(Request $request, $valueForColumnName, string $columnName = 'user_id'): void
    {
        /** @var Model $model */
        $model = $this->getOneRecord($this->getRequestId($request));
        if (!$model) {
            throw new CoreHttpException(404, __('core::error_messages.not_found'));
        }

        if (!$this->canDoActionWithModel($model, $columnName, $valueForColumnName)) {
            throw new CoreHttpException(403, __('core::error_messages.permission_denied'));
        }

        if (!$model->delete()) {
            throw new CoreHttpException(409, __('core::error_messages.destroy_error'));
        }
    }
}