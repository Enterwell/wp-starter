<?php

// Enterwell namespace
namespace Ew\WpHelpers\Services;
use Ew\WpHelpers\Classes\Request_Validation_Result;

/**
 * Created by PhpStorm.
 * User: Matej
 * Date: 29.7.2016.
 * Time: 13:11
 */
class Validation_Service
{


    public function not_empty($array, $field, $request = "Default_Request"){
        $result = new Request_Validation_Result();

        if(empty($array[$field]) || is_string($array[$field]) && empty(trim($array[$field]))){

            $result->set_valid(false);
            $result->add_message("Required filed $field [$request] is empty!");


        }else {
            $result->set_valid(true);
        }

        return $result;
    }

    public function is_set($array, $field, $request = "Default_Request"){
        $result = new Request_Validation_Result();


        if(!isset($array[$field])){

            $result->set_valid(false);
            $result->add_message("Required filed $field [$request] is not set!");

        }else{
            $result->set_valid(true);
        }

        return $result;
    }

    public function is_valid_id($array, $field, $request = "Default_Request"){
        $result = new Request_Validation_Result();

        if(empty($array[$field]))
        {
            $result->set_valid(false);
            $result->add_message("Required field $field [$request] is empty!");
            return $result;

        }

        $array_field_val = !is_numeric($array[$field]) ? 0 : intval($array[$field]);

        if(empty($array_field_val)){

            $result->set_valid(false);
            $result->add_message("Required id filed $field [$request] is not valid!");

        }else{
            $result->set_valid(true);
        }

        return $result;
    }

    public function is_numeric($array, $field, $request = "Default_Request"){
        $result = new Request_Validation_Result();

        if(!isset($array[$field]))
        {
            $result->set_valid(false);
            $result->add_message("Required field $field [$request] is not set!");
            return $result;
        }


        if(!is_numeric($array[$field])){

            $result->set_valid(false);
            $result->add_message("Required numeric filed $field [$request] is not valid!");

        }else{
            $result->set_valid(true);
        }

        return $result;
    }

}