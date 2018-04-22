<?php
/**
 * Created by PhpStorm.
 * User: mcones
 * Date: 1/6/18
 * Time: 5:29 PM
 */

namespace Mcones\LaravelBaseHelpers\Traits;


use Mcones\LaravelBaseHelpers\Exceptions\BaseException;

trait BaseModel
{

    public function scopeQueryParamsParse($query,$params){


        if(isset($params['with'])){
            $query->with($params['with']);
        }

        if(isset($params['with_count'])){
            $query->with($params['with_count']);
        }


        if(isset($params['filter'])){
            $this->validateFilterParams($params['filter']);
            $query->filter($params['filter']);
        }

        if(isset($params['scopes'])){
            $query->scopes($params['scopes']);
        }

        if(isset($params['sort'])){
            $this->validateSortParams($params['sort']);
            $query->sort($params['sort']);
        }

        if(isset($params['group'])){
            $query->addSelect($params['group']);
            $query->groupBy($params['group']);
        }

    }

    private function validateFilterParams($params){

        if(is_array($params)){
            $field_value_regex = '/^[a-zA-Z_]+\|.+$/';
            $field_value_operation_regex = '/^[a-zA-Z_]+\|(=|<|>|<>)\|?.{1,1}\|?(or|and)?$/';

            foreach($params as $param){
                preg_match_all($field_value_regex, $param, $field_value_matches, PREG_SET_ORDER, 0);
                preg_match_all($field_value_operation_regex, $param, $field_value_operation_matches, PREG_SET_ORDER, 0);

                if (count($field_value_matches)==0 && count($field_value_operation_matches)==0){
                    throw new BaseException('INVALID_FILTER_PARAM','The param '.$param.' has an invalid format');
                }
            }
        }
    }

    private function validateSortParams($params){

        if(is_array($params)){

            $field_value_regex = '/^[a-zA-Z_]+\|(asc|desc)$/';

            foreach($params as $param){
                preg_match_all($field_value_regex, $param, $field_value_matches, PREG_SET_ORDER, 0);

                if (count($field_value_matches)==0){
                    throw new BaseException('INVALID_SORT_PARAM','The param '.$param.' has an invalid format');
                }
            }
        }
    }

    public function scopeSort($query,$sort_request){

        if(is_array($sort_request)){

            foreach($sort_request as $sort_element){

                $sort = explode('|', $sort_element);
                $query->orderBy($sort[0], $sort[1]);

            }

        }else{
            $sort = explode('|', $sort_request);
            $query->orderBy($sort[0], $sort[1]);
        }


    }

    public function scopeFilter($query, $filter_request)
    {
        if(!isset($this->filter_fields)){
            $this->filter_fields=$this->getTableColumns();
        }


        if(is_array($filter_request)){

            foreach($filter_request as $filter)
            {
                $filter_array = explode('|', $filter);

                if(!in_array($filter_array[0],$this->filter_fields)){

                    throw new BaseException('INVALID_FIELD','The field '.$filter_array[0].' is not filterable for '
                        .$this->getTable());
                }


                if(count($filter_array)==2){
                    $query->where($filter_array[0],$filter_array[1]);
                }else if(count($filter_array)==3){
                    $query->where($filter_array[0],$filter_array[1],$filter_array[2]);
                }else if(count($filter_array)==4){
                    if(strtolower($filter_array[3])=='and'){
                        $query->where($filter_array[0],$filter_array[1],$filter_array[2]);
                    }else{
                        $query->orWhere($filter_array[0],$filter_array[1],$filter_array[2]);
                    }
                }

            }

        }else if($filter_request!=''){


            foreach($this->filter_fields as $filter_field)
            {
                $query->orWhere($filter_field,'like', '%' . $filter_request . '%');
            }
        }


        return $query;
    }

    public static function findOrFail($ids,$request=null){

        $query=parent::query();
        if(isset($request) && is_array($request->with)){
            $query->with($request->with);
        }

        if(isset($request) && is_array($request->with_count)){
            $query->with($request->with_count);
        }

        return $query->findOrFail($ids);
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

//    In order to declare a custom path we can do
//    private static $serviceClass='your path';
//    in model

    public static function service(){

        $getInstanceMethod='getFacadeRoot';
        $class=null;

        if(isset(self::$serviceClass)){
            $class=self::$serviceClass;
        }else{
            $class_name=static::class;
            $class_name=explode('\\',$class_name);
            $class_name=$class_name[0].'\\Services\\'.$class_name[1];
            $class ='Facades\\'.$class_name.'Service';
        }

        return $class::$getInstanceMethod();
    }
}