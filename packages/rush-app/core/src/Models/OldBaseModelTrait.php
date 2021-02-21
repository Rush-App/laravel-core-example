<?php

namespace RushApp\Core\Models;

use App\Models\Post\Post;
use RushApp\Core\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Monolog\Logger;

trait OldBaseModelTrait
{
    use CoreBaseModelTrait;

    /**
     * Return a collections of one or more records with or without translation
     *
     * @param Request $request
     * @param null $id
     * @param boolean $onlyUserData
     * @param boolean $isGet
     * @param array $expandData
     * @return array
     */
    public function getCollections(Request $request, array $expandData, bool $onlyUserData, bool $isGet = null, $id = null): array
    {
        $params = $request->all();

        //add id for (->first)
        $id = $id ? $id : $this->getRequestId($request);

        //checking for the issuance of data that belongs to the current user
        $onlyUserData = $this->isOwner();
        if ($onlyUserData) {
            if (empty(Auth::id())) {
                LoggingService::CRUD_errorsLogging('getCollections (1) - 418', Logger::NOTICE);
                return ['error' => true, 'code' => 418, 'message' => __('response_messages.access_closed')];
            } else {
                $params['user_id'] = Auth::id();
            }
        }

        //intertwining the output language that is initially installed in middleware
        $language = array_key_exists($this->requestParamNameForGetFillLangData, $params)
            ? $params[$this->requestParamNameForGetFillLangData]
            : $params['language'];

        //check for data output where translation tables exist
        if (class_exists($this->modelTranslationClass)) {
            $query = $this->getCollectionsWithTranslate($language, $id);
            if (is_array($query) && $query['error'] === true) {
                return $query;
            }
        } else {
            if($id) {
                $query = $this->modelClass::where($this->tablePluralName.'.id', $id);
                if (!$this->modelClass::find($id)) {
                    return ['error' => true, 'code' => 404, 'message' => 'Not found'];
                }
            } else {
                $query = new $this->modelClass;
            }
        }

        //check for data expansion, when - rightJoin
        if (!empty($params[$this->requestParamNameForRightJoin])) {
            $relationshipTableNamesArray = array_intersect_assoc(
                $expandData,
                explode(",", $params[$this->requestParamNameForRightJoin])
            );

            $query = array_key_exists($this->requestParamNameJunctionTable, $params)
                ? $this->expandData($query, $relationshipTableNamesArray, $this->requestParamNameForRightJoin, $language, $this->requestParamNameJunctionTable)
                : $this->expandData($query, $relationshipTableNamesArray, $this->requestParamNameForRightJoin, $language);
        }

        //check for data expansion, when - leftJoin
        if (!empty($params[$this->requestParamNameForLeftJoin])) {
            $relationshipTableNamesArray = array_intersect_assoc(
                $expandData,
                explode(",", $params[$this->requestParamNameForLeftJoin])
            );

            $query = array_key_exists($this->requestParamNameJunctionTable, $params)
                ? $this->expandData($query, $relationshipTableNamesArray, $this->requestParamNameForLeftJoin, $language, $this->requestParamNameJunctionTable)
                : $this->expandData($query, $relationshipTableNamesArray, $this->requestParamNameForLeftJoin, $language);
        }

        //adding data from translation main table
        if (class_exists($this->modelTranslationClass)) {
            $query->addSelect($this->getTranslationTableName().'.*');
        }

        //adding data from main table
        $query ->addSelect($this->tablePluralName.'.*');

        //Select only this data
        if(!empty($select = $this->getValueForExistingTableColumns($params, $this->requestParamNameForSelect))) {
            $query = $query->select($select);
        }

        //Sort by a given field
        if(!empty($params[$this->requestParamNameForOrderByField]) && !empty($params[$this->requestParamNameForOrderBy])) {
            $query = $query->orderBy($params[$this->requestParamNameForOrderByField], $params[$this->requestParamNameForOrderBy]);
        }

        //give data where some field is whereNotNull
        if (
            !empty($params[$this->requestParamNameForWhereNotNull]) &&
            !empty($whereNotNull = $this->getValueForExistingTableColumns($params, $this->requestParamNameForWhereNotNull))
        ) {
            $query = $query->whereNotNull($whereNotNull);
        }

        //check for paginate data
        $paginate = !empty($params[$this->requestParamNamePaginateNumber])
            ? $params[$this->requestParamNamePaginateNumber]
            : false;

        //Get limited data
        if(!empty($params[$this->requestParamNameForLimit])) {
            $query = $query->limit($params[$this->requestParamNameForLimit]);
        }

        //Parameters for "where", under what conditions the request will be displayed
        $params = $this->getQueryParams($this->filteringForParams($params));

        if ($paginate) {
            return ['error' => false, 'code' => 200, 'data' => $query->where($params)->paginate($paginate)];
        }

        if ($isGet === null) {
           return ['error' => false, 'code' => 200, 'data' => $query->where($params)];
        }

        return $isGet
            ? ['error' => false, 'code' => 200, 'data' => $query->where($params)->get()]
            : ['error' => false, 'code' => 200, 'data' => $query->where($params)->first()];
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
        if(!$request->has('date')) {
            $request->merge(["date" => date("Y-m-d")]);
        }

        $request->merge(["user_id" => Auth::id()]);

        if ($this->isRecordWithTranslationTable() && $this->getLanguage($request[$this->requestParamNameForGetFillLangData])) {
            $mainModel = $this->modelFill($request->all(), $this->modelClass);

            if ($mainModel === false) {
                LoggingService::CRUD_errorsLogging('createOne (2) - 409', Logger::CRITICAL);
                return ['error' => true, 'code' => 409, 'message' => __('response_messages.save_error')];
            }

            $language = $this->getLanguage($request[$this->requestParamNameForGetFillLangData]);
            $request->merge([
                $this->getNameForeignKeyForTranslationTable() => $mainModel->id,
                $this->languageNameForeignKey => $language->id
            ]);

            $createEmptyRecords = $this->createEmptyRecords($language->id, $mainModel->id);

            if ($createEmptyRecords === false) {
                LoggingService::CRUD_errorsLogging('createOne (3) - 409', Logger::CRITICAL);
                return ['error' => true, 'code' => 409, 'message' => __('response_messages.save_error')];
            }

            $translationModel = $this->modelFill($request->all(), $this->modelTranslationClass);

            if ($translationModel === false) {
                LoggingService::CRUD_errorsLogging('createOne (4) - 409', Logger::CRITICAL);
                return ['error' => true, 'code' => 409, 'message' => __('response_messages.save_error')];
            }

            if ($data = (new Collection($mainModel))->union(new Collection($translationModel))) {
                return ['error' => false, 'code' => 200, 'data' => $data];
            } else {
                LoggingService::CRUD_errorsLogging('createOne (5) - 409', Logger::CRITICAL);
                return ['error' => true, 'code' => 409, 'message' => __('response_messages.save_error')];
            }
        } elseif ($this->isRecordWithTranslationTable() && !$this->getLanguage($request[$this->requestParamNameForGetFillLangData])) {
            $mainModel = $this->modelFill($request->all(), $this->modelClass);

            if ($mainModel === false) {
                LoggingService::CRUD_errorsLogging('createOne (6) - 409', Logger::CRITICAL);
                return ['error' => true, 'code' => 409, 'message' => __('response_messages.save_error')];
            }

            $languages =  Language::all();
            foreach ($languages as $language) {
                $this->createEmptyRecords($language->id, $mainModel->id);
            }

            return ['error' => false, 'code' => 200, 'data' => $mainModel];
        } else {
            if ($data = $this->modelFill($request->all(), $this->modelClass)) {
                return ['error' => false, 'code' => 200, 'data' => $data];
            } else {
                LoggingService::CRUD_errorsLogging('createOne (7) - 409', Logger::CRITICAL);
                return ['error' => true, 'code' => 409, 'message' => __('response_messages.save_error')];
            }
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
        if ($model = $this->getOneRecord($this->getRequestId($request))) {
            if ($this->canDoActionWithModel($model, $columnName, $valueForColumnName)){
                if ($this->isRecordWithTranslationTable() && $this->getLanguage($request[$this->requestParamNameForGetFillLangData])) {
                    if ($data = $this->updateOneRecordWithTranslationTable($model, $request)) {
                        return ['error' => false, 'code' => 200, 'data' => $data];
                    } else {
                        LoggingService::CRUD_errorsLogging('updateOne (1) - 409', Logger::CRITICAL);
                        return ['error' => true, 'code' => 409, 'message' => __('response_messages.edit_error')];
                    }
                } else {
                    if ($data = $this->updateOneRecord($request->all(), $model)) {
                        return ['error' => false, 'code' => 200, 'data' => $data];
                    } else {
                        LoggingService::CRUD_errorsLogging('updateOne (2)  - 409', Logger::CRITICAL);
                        return ['error' => true, 'code' => 409, 'message' => __('response_messages.edit_error')];
                    }
                }
            } else {
                LoggingService::CRUD_errorsLogging('updateOne - 403', Logger::NOTICE);
                return ['error' => true, 'code' => 403, 'message' => __('response_messages.editing_unavailable')];
            }
        } else {
            LoggingService::CRUD_errorsLogging('updateOne - 404', Logger::NOTICE);
            return ['error' => true, 'code' => 404, 'message' => __('response_messages.data_not_found')];
        }
    }

    /**
     * Delete one record with checking for compliance of the record to the user
     *
     * @param string $columnName - column name to check whether a record matches a specific user
     * @param $valueForColumnName - column value to check whether a record matches a specific user
     * @param Request $request
     * @return array
     */
    public function deleteOne(Request $request, $valueForColumnName, string $columnName = 'user_id'): array
    {
        if ($model = $this->getOneRecord($this->getRequestId($request))) {
            if ($this->canDoActionWithModel($model, $columnName, $valueForColumnName)){
                if ($model->delete()){
                    return ['error' => false, 'code' => 200, 'data' => __('response_messages.deleted')];
                } else {
                    LoggingService::CRUD_errorsLogging('deleteOne (1) - 409', Logger::CRITICAL);
                    return ['error' => true, 'code' => 409, 'message' => __('response_messages.delete_error')];
                }
            } else {
                LoggingService::CRUD_errorsLogging('deleteOneForAdmin - 403', Logger::NOTICE);
                return ['error' => true, 'code' => 403, 'message' => __('response_messages.editing_unavailable')];
            }
        } else {
            LoggingService::CRUD_errorsLogging('deleteOneForAdmin - 404', Logger::NOTICE);
            return ['error' => true, 'code' => 404, 'message' => __('response_messages.data_not_found')];
        }
    }
//
//
//    /**
//     * Updates the model and then returns it (WITHOUT checking for compliance of the record to the user)
//     * Use for Admins
//     *
//     * @param Request $request
//     * @return array
//     */
//    public function updateOneForAdmin(Request $request): array
//    {
//        if ($model = $this->getOneRecord($this->getRequestId($request))) {
//            if (($this->isRecordWithTranslationTable() && $this->getLanguage($request[$this->requestParamNameForGetFillLangData]))) {
//                if ($data = $this->updateOneRecordWithTranslationTable($model, $request)) {
//                    return ['error' => false, 'code' => 200, 'data' => $data];
//                } else {
//                    LoggingService::CRUD_errorsLogging('updateOneForAdmin (1) - 409', Logger::CRITICAL);
//                    return ['error' => true, 'code' => 409, 'message' => __('response_messages.edit_error')];
//                }
//            } else {
//                if ($data = $this->updateOneRecord($request->all(), $model)) {
//                    return ['error' => false, 'code' => 200, 'data' => $data];
//                } else {
//                    LoggingService::CRUD_errorsLogging('updateOneForAdmin (2) - 409', Logger::CRITICAL);
//                    return ['error' => true, 'code' => 409, 'message' => __('response_messages.edit_error')];
//                }
//            }
//        } else {
//            LoggingService::CRUD_errorsLogging('deleteOneForAdmin - 404', Logger::NOTICE);
//            return ['error' => true, 'code' => 404, 'message' => __('response_messages.data_not_found')];
//        }
//    }
//
//    /**
//     * Delete one record WITHOUT checking for compliance of the record to the user
//     * Use for Admins
//     *
//     * @param Request $request
//     * @return array
//     */
//    public function deleteOneForAdmin(Request $request): array
//    {
//        if ($model = $this->getOneRecord($this->getRequestId($request))) {
//            if ($model->delete()){
//                return ['error' => false, 'code' => 200, 'data' => 'Data has been successfully deleted'];
//            } else {
//                LoggingService::CRUD_errorsLogging('deleteOneForAdmin - 409', Logger::CRITICAL);
//                return ['error' => true, 'code' => 409, 'message' => __('response_messages.delete_error')];
//            }
//        } else {
//            LoggingService::CRUD_errorsLogging('deleteOneForAdmin - 404', Logger::NOTICE);
//            return ['error' => true, 'code' => 404, 'message' => __('response_messages.data_not_found')];
//        }
//    }
}
