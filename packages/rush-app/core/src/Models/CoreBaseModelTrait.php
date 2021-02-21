<?php

namespace RushApp\Core\Models;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Monolog\Logger;
use RushApp\Core\Services\LoggingService;
use RushApp\Core\Services\UserActionsService;

trait CoreBaseModelTrait
{
    /**
     * the base lang on site. Available in Languages model
     * @var string
     */
    protected string $baseLanguage = 'en';

    /**
     * controller model name
     * @var string
     */
    protected string $modelClass;

    /**
     * You should always use these model names.
     * translation table name (example: CountryTranslation)
     * @var string
     */
    protected string $modelTranslationClass;

    /**
     * You should always use "language_id" in all Translations tables.
     * Example: $table->foreign('language_id')->references('id')->on('languages');
     *
     * @var string
     */
    protected string $languageNameForeignKey = "language_id";

    /**
     * Select column for sorting data
     *
     * You should always use "order_by_field" in all requests.
     * One record or whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?order_by=year
     *
     * @var string
     */
    protected string $requestParamNameForOrderByField = "order_by_field";

    /**
     * For sorting data - "asc" or "desc"
     *
     * You should always use "order_by" in all requests.
     * Order direction must be "asc" or "desc"
     * Example: http://127.0.0.1:8000/test?order_by=desc
     *
     * @var string
     */
    protected string $requestParamNameForOrderBy = "order_by";

    /**
     * For right join
     * You should always use "rightJoin" in all requests.
     * Data in rightJoin must be like table names (users, internship_categories)
     *
     * One record or whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?rightJoin=user,userCategory
     *
     * @var string
     */
    protected string $requestParamNameForRightJoin = "rightJoin";

    /**
     * For left join
     * You should always use "leftJoin" in all requests.
     * Data in rightJoin must be like table names (users, internship_categories)
     *
     * One record or whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?rightJoin=user,userCategory
     * @var string
     */
    protected string $requestParamNameForLeftJoin = "leftJoin";

    /**
     * For server paginate
     * You should always use "paginate" in all requests.
     * You also can use paramName "page" for show current user`s page
     *
     * Example: http://127.0.0.1:8000/test?paginate=2&page=1
     * @var string
     */
    protected string $requestParamNamePaginateNumber = "paginate";

    /**
     * For server to get limited data
     * You should always use "limit" in all requests.
     *
     * Example: http://127.0.0.1:8000/test?limit=2
     * @var string
     */
    protected string $requestParamNameForLimit = "limit";

    /**
     * For left or right join (when $modelClass is a junction table (many-to-many))
     *
     * @var string
     */
    protected string $requestParamNameJunctionTable = "junction_table";

    /**
     * For partial selection of fields from tables
     *
     * You should always use "selected_fields" in all requests.
     * Whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?selected_fields=year,id,name
     *
     * @var string
     */
    protected string $requestParamNameForSelect = "selected_fields";

    /**
     * To remove those records where the selected fields are empty
     *
     * You should always use "where_not_null" in all requests.
     * Whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?where_not_null=year,id,name
     *
     * @var string
     */
    protected string $requestParamNameForWhereNotNull = "where_not_null";

    /**
     * For get the data that the user fills in the selected language
     * To interrupt the issue by language from the headers (language)
     *
     * You should always use "fill_language" in all requests to interrupt basic behavior.
     * Supports the names of languages as in the database
     * Example: http://127.0.0.1:8000/test?fill_language=en
     *
     * @var string
     */
    protected string $requestParamNameForGetFillLangData = "fill_language";

    /**
     * table singular name in the database (example: model - Country, $tableSingularName - country)
     * @var string
     */
    protected string $tableSingularName;

    /**
     * table plural name in the database (example: model - Country, $tablePluralName - countries)
     * @var string
     */
    protected string $tablePluralName;

    /**
     * table translation name for $modelTranslationClass (example: table - countries, $tableTranslationName - country_translations)
     * @var string
     */
    protected string $tableTranslationName;

    /**
     * If model belongs to user (have user_id field), this model can be updated, deleted by this user (owner).
     * @var bool
     */
    public bool $canBeManagedByOwner = true;


        /**
     * set the initial parameters from the name of the model received from the controller
     * (the name of the model must be indicated in each controller)
     *
     * @param string|null $modelClass - to rewrite class of model
     */
    protected function initBaseModel(string $modelClass = null): void
    {
        $this->modelClass = $modelClass ? $modelClass : static::class;
        $this->modelTranslationClass = $this->modelClass.'Translation';
        $this->setTableSingularName();
        $this->setTranslationTableName();
        $modelClass ? $this->setTablePluralNameNonStatic() : $this->setTablePluralName();
    }

    protected function setTableSingularName(): void
    {
        $camelCaseTableName = lcfirst(substr(strrchr($this->modelClass, "\\"), 1));
        $this->tableSingularName = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $camelCaseTableName)), '_');
    }

    protected function getTableSingularName(): string
    {
        return $this->tableSingularName;
    }

    protected function setTablePluralName(): void
    {
        $this->tablePluralName = $this->modelClass::getTable();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(
            $this->modelTranslationClass,
            $this->getNameForeignKeyForTranslationTable(),
            $this->getKeyName()
        );
    }

    protected function setTablePluralNameNonStatic(): void
    {
        $this->tablePluralName = (new $this->modelClass)->getTable();
    }

    protected function getTablePluralName(): string
    {
        return $this->tablePluralName;
    }

    protected function setTranslationTableName(): void
    {
        $this->tableTranslationName = $this->getTableSingularName().'_translations';
    }

    protected function getTranslationTableName(): string
    {
        return $this->tableTranslationName;
    }


    /**
     * returns a collection of model records with translations
     * @param int|null $id - to display the specific record
     * @param string $lang - Like 'en' or 'ru'
     *
     * @return mixed
     */
    protected function getCollectionsWithTranslate(string $lang, int $id = null) {
        $language = $this->getLanguage($lang);
        if (empty($language)) {
            $language = $this->getLanguage($this->baseLanguage);

            LoggingService::CRUD_errorsLogging('CoreBaseModelTrait/getCollectionsWithTranslate - this lang not correct - '.$lang, Logger::WARNING);
        }

        $translationsTableName = $this->getTranslationTableName();

        $query = $this->modelClass::leftJoin(
            $translationsTableName,
            $this->tablePluralName.'.id',
            $translationsTableName.'.'.$this->getNameForeignKeyForTranslationTable()
        )->where($translationsTableName.'.'.$this->languageNameForeignKey, $language->id);

        if ($id) {
            $query->where($this->tablePluralName.'.id', $id);
            if (!$this->modelClass::find($id)) {
                return ['error' => true, 'code' => 404, 'message' => 'Not found'];
            }
        }

        return $query;
    }

    /**
     * @param int $id - ID record to get
     * @return mixed
     */
    protected function getOneRecord(int $id)
    {
        return $this->modelClass::find($id);
    }

    /**
     * Example:
     * - Main table name: base_invoices
     * - Translation table name: base_invoice_translation
     * - In translation table: $table->foreign('base_invoice_id')->references('id')->on('base_invoices');
     *
     * @return string - Like this: base_invoice_id
     */
    protected function getNameForeignKeyForTranslationTable(): string
    {
        return $this->getTableSingularName().'_id' ;
    }

    /**
     * Get record from translate table
     *
     * @param int $id - ID record from main table
     * @param string $foreignKeyName
     * @param int $languageId
     * @return mixed
     */
    protected function getOneRecordForTranslateTable(string $foreignKeyName, int $id, int $languageId)
    {
        return $this->modelTranslationClass::where([$foreignKeyName => $id, $this->languageNameForeignKey => $languageId])->first();
    }

    /**
     * Updates the model and then returns it
     *
     * @param array $modelData - data for updating
     * @param object $model - specific record from the database
     * @return mixed|boolean
     */
    protected function updateOneRecord(array $modelData, object $model)
    {
        $filteredModelData = array_filter(
            $modelData,
            fn($key) => in_array($key, $model->getFillable()),
            ARRAY_FILTER_USE_KEY
        );

        $model->fill($filteredModelData);

        return $model->save() ? $model->refresh() : false;
    }

    /**
     * @param $model
     * @param $request
     * @return Collection|boolean
     */
    protected function updateOneRecordWithTranslationTable($model, $request)
    {
        $language = $this->getLanguage($request[$this->requestParamNameForGetFillLangData]);
        $foreignKeyName = $this->getNameForeignKeyForTranslationTable();
        $requestAll = $request->all();

        $mainTable = $this->updateOneRecord($requestAll, $model);

        if ($mainTable === false) {
            return $mainTable;
        }

        //To avoid ID conflicts with different tables
        unset($requestAll['id']);

        $translateTable = $this->updateOneRecord($requestAll, $this->getOneRecordForTranslateTable($foreignKeyName, $model->id, $language->id));

        if ($translateTable === false) {
            return $translateTable;
        }

        return (new Collection($mainTable))->union(new Collection($translateTable));
    }

    /**
     * Checking is Main table has Translation table and correct foreignKeyName
     *
     * @return bool
     */
    protected function isRecordWithTranslationTable(): bool
    {
        $foreignKeyName = $this->getNameForeignKeyForTranslationTable();
        $isForeignKeyExist = $this->isColumnExistInTable($foreignKeyName, $this->getTranslationTableName());
        $isLanguageIdExist = $this->isColumnExistInTable($foreignKeyName, $this->getTranslationTableName());

        return class_exists($this->modelTranslationClass) && $isForeignKeyExist && $isLanguageIdExist;
    }

    /**
     * Check if this record matches this user
     *
     * @param object $model - specific record from the database
     * @param string $columnName - column name to check whether a record matches a specific user
     * @param $valueForColumnName - column value to check whether a record matches a specific user
     * @return bool
     */
    protected function canDoActionWithModel(object $model, string $columnName, $valueForColumnName): bool {
        $userActionsService = resolve(UserActionsService::class);
        if ($userActionsService->canUserPerformAction(request())) {
            return true;
        }

        if ($this->canBeManagedByOwner) {
            return $this->isColumnExistInTable($columnName, $this->getTablePluralName())
                ? $model->{$columnName} === $valueForColumnName
                : false;
        }

        return false;
    }

    /**
     * Removes array elements by nonexistent columns in the table
     *
     * @param array $params - column names in the table (use for filter 'where')
     * @return array
     */
    protected function filteringForParams(array $params): array {
        return array_filter($params, fn($v, $k) => $this->isColumnExistInTable($k, $this->getTablePluralName()), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * adds to each column in the table the name of the table itself
     * (example: ($tablePluralName - countries, columnName - id) => result - countries.id)
     *
     * @param array $existingTableColumns
     * @return array
     */
    protected function getQueryParams(array $existingTableColumns): array {
        return array_combine(
            array_map(
                fn($k) => $this->tablePluralName. '.' . $k,
                array_keys($existingTableColumns)
            ),
            $existingTableColumns
        );
    }

    /**
     * checking for the existence of column names in the table
     *
     * @param string $columnName - column name in the table
     * @param string $tableName
     * @return bool
     */
    protected function isColumnExistInTable(string $columnName, string $tableName): bool {
        return Schema::hasColumn($tableName, $columnName);
    }

    /**
     * checking is table exists
     *
     * @param string $tableName
     * @return bool
     */
    protected function isTableExist(string $tableName): bool {
        return Schema::hasTable($tableName);
    }

    /**
     * Getting a list of values as a string.
     * Convert it to an array for substitution to query
     * Example: http://127.0.0.1:8000/test?selected_fields=year,recipient_company,id&order_by=year,recipient_company,id
     *
     * @param array $params
     * @param string $paramsFieldName
     * @return array
     */
    protected function getValueForExistingTableColumns (array $params, string $paramsFieldName): array
    {
        $resultArrForSelect = [];
        $modelTranslationClassExist = class_exists($this->modelTranslationClass);

        if (array_key_exists($paramsFieldName, $params)) {
            $selectedFields = explode(",", $params[$paramsFieldName]);
            foreach ($selectedFields as $selectedField) {

                //To avoid duplicate id in different tables. Using the ID of the main table
                if (($modelTranslationClassExist && $selectedField !== 'id') || !$modelTranslationClassExist) {
                    if ($this->isColumnExistInTable($selectedField, $this->getTranslationTableName())) {
                        array_push($resultArrForSelect, $selectedField);
                    }
                    if ($this->isColumnExistInTable($selectedField, $this->getTablePluralName())) {
                        array_push($resultArrForSelect, $selectedField);
                    }
                } else {
                    array_push($resultArrForSelect, $this->getTablePluralName().'.id');
                }
            }
        }

        return $resultArrForSelect;
    }

    /**
     * @param $language
     * @return mixed
     */
    protected function getLanguage($language) {
        return Language::select('id')->where('name', $language)->first();
    }

    /**
     * when adding a record to the main table - create empty records in the translation table
     * if the data did not come from the front-end
     *
     * @param int $languageId
     * @param int $mainModelId
     * @return void|boolean
     */
    protected function createEmptyRecords(int $languageId, int $mainModelId)
    {
        $languages = Language::select('id')->whereNotIn('id', [$languageId])->get();
        foreach ($languages as $language) {
            $tempArr = [
                $this->languageNameForeignKey => $language->id,
                $this->getNameForeignKeyForTranslationTable() => $mainModelId
            ];

            $modelFill = $this->modelFill($tempArr, $this->modelTranslationClass);

            if ($modelFill === false) {
                return $modelFill;
            }
        }
    }

    /**
     * @param array $data
     * @param string $modelClassName
     * @return mixed|boolean
     */
    protected function modelFill(array $data, string $modelClassName) {
        $translationModel = new $modelClassName;
        $translationModel->fill($data);

        try {
            $result = $translationModel->save();

            return $result ? $translationModel : false;
        } catch (\Exception $e) {
            LoggingService::CRUD_errorsLogging('CoreBaseModelTrait/modelFill - '.$e, Logger::CRITICAL);

            return false;
        }
    }

    /**
     * Get id from request url (example: .../112)
     *
     * @param $request
     * @return int
     */
    protected function getRequestId($request): int
    {
        return (int) ($request->route($this->getTableSingularName()) ?: $request->route('id'));
    }

    /**
     * @param $request
     * @param int $id
     */
    protected function setRequestId($request, int $id): void {
        $request->route()->setParameter('id',  $id);
    }

    /**
     * using left or right Join for current $modelClass
     *
     * @param $query
     * @param array $relationshipTableNamesArray
     * @param string $typeOfJoin
     * @param string $language
     * @param string $junctionTable
     * @return mixed
     */
    public function expandData($query, array $relationshipTableNamesArray, string $typeOfJoin, string $language, string $junctionTable = null)
    {
        $resultQuery = $query;
        foreach ($relationshipTableNamesArray as $relationshipTableName) {
            if ($junctionTable) {
                $relationshipTableSingularName = Str::singular($relationshipTableName);
                $relationshipColumnName = $relationshipTableSingularName.'_id';

                if ($this->isTableExist($relationshipTableName) && $this->isColumnExistInTable($relationshipColumnName, $this->getTablePluralName())) {
                    $resultQuery = $resultQuery->{$typeOfJoin}($relationshipTableName, $this->getTablePluralName().'.'.$relationshipColumnName, '=', $relationshipTableName.'.id')
                        ->addSelect($relationshipTableName.'.*');

                    $resultQuery = $this->expandDataModifySelect($relationshipTableName, $resultQuery, $language);
                }
            } else {
                $relationshipColumnName = $this->getTableSingularName().'_id';

                if ($this->isTableExist($relationshipTableName) && $this->isColumnExistInTable($relationshipColumnName, $relationshipTableName)) {
                    $resultQuery = $resultQuery->{$typeOfJoin}($relationshipTableName, $this->getTablePluralName().'.id', '=', $relationshipTableName.'.'.$relationshipColumnName)
                        ->addSelect($relationshipTableName.'.*');

                    $resultQuery = $this->expandDataModifySelect($relationshipTableName, $resultQuery, $language);
                }
            }
        }
        return $resultQuery->addSelect($this->getTablePluralName().'.*');
    }

    /**
     * @param string $relationshipTableName
     * @param $resultQuery
     * @param string $language
     * @return mixed
     */
    public function expandDataModifySelect(string $relationshipTableName, $resultQuery, string $language)
    {
        $relationshipTableSingularName = Str::singular($relationshipTableName);

        $translationsTableName = $relationshipTableSingularName.'_translations';

        if ($this->isTableExist($translationsTableName)) {
            return $this->getExpandWithTranslate($language, $relationshipTableName, $translationsTableName, $relationshipTableSingularName, $resultQuery);
        } else {
            $tableColumnsForTranslations = Config::get('for_base_architecture.table_columns_for_translations');

            foreach ($tableColumnsForTranslations as $tableColumn) {
                if ($this->isColumnExistInTable($tableColumn, $relationshipTableName)) {
                    $resultQuery->addSelect($relationshipTableName.'.'.$tableColumn.' as '.$relationshipTableName.'_'.$tableColumn);
                }
            }

            return $resultQuery;
        }
    }

    /**
     * @param string $lang
     * @param string $relationshipTableName
     * @param string $translationsTableName
     * @param string $relationshipTableSingularName
     * @param $resultQuery
     * @return mixed
     */
    public function getExpandWithTranslate(string $lang, string $relationshipTableName, string $translationsTableName, string $relationshipTableSingularName, $resultQuery) {
        $language = $this->getLanguage($lang);

        $resultQuery->leftJoin(
            $translationsTableName,
            $relationshipTableName.'.id',
            $translationsTableName.'.'.$relationshipTableSingularName.'_id'
        )
            ->addSelect($translationsTableName.'.*')
            ->where($translationsTableName.'.'.$this->languageNameForeignKey, $language->id);

        $tableColumnsForTranslations = Config::get('for_base_architecture.table_columns_for_translations');

        foreach ($tableColumnsForTranslations as $tableColumn) {
            if ($this->isColumnExistInTable($tableColumn, $relationshipTableName)) {
                $resultQuery->addSelect($relationshipTableName.'.'.$tableColumn.' as '.$relationshipTableName.'_'.$tableColumn);
            }
            if ($this->isColumnExistInTable($tableColumn, $translationsTableName)) {
                $resultQuery->addSelect($translationsTableName.'.'.$tableColumn.' as '.$translationsTableName.'_'.$tableColumn);
            }
        }

        return $resultQuery;
    }
}
