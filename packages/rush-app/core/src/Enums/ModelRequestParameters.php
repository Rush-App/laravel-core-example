<?php

namespace RushApp\Core\Enums;

class ModelRequestParameters
{
    /**
     * For server paginate
     * You should always use "paginate" in all requests.
     * You also can use paramName "page" for show current user`s page
     *
     * Example: http://127.0.0.1:8000/test?paginate=2&page=1
     * @var string
     */
    public const PAGINATE = 'paginate';

    /**
     * You should always use "language_id" in all Translations tables.
     * Example: $table->foreign('language_id')->references('id')->on('languages');
     *
     * @var string
     */
    public const LANGUAGE_FOREIGN_KEY = "language_id";

    /**
     * Select column for sorting data
     *
     * You should always use "order_by_field" in all requests.
     * One record or whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?order_by_field=year:desc
     *
     * @var string
     */
    public const ORDER_BY_FIELD = "order_by_field";

    /**
     * Before using this parameter you must be sure that you add this relations in model.
     *
     * Example: http://127.0.0.1:8000/test?with=user:id,email|categories:id,title
     * @var string
     */
    public const WITH = "with";

    /**
     * For server to get limited data
     * You should always use "limit" in all requests.
     *
     * Example: http://127.0.0.1:8000/test?limit=2
     * @var string
     */
    public const LIMIT = "limit";

    /**
     * For partial selection of fields from tables
     *
     * You should always use "selected_fields" in all requests.
     * Whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?selected_fields=year,id,name
     *
     * @var string
     */
    public const SELECTED_FIELDS = "selected_fields";

    /**
     * To remove those records where the selected fields are empty
     *
     * You should always use "where_not_null" in all requests.
     * Whole string without spaces separated by commas
     * Example: http://127.0.0.1:8000/test?where_not_null=year,id,name
     *
     * @var string
     */
    public const WHERE_NOT_NULL = "where_not_null";
}