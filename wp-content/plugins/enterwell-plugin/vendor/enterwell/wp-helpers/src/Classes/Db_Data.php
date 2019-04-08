<?php
/**
 * Created by PhpStorm.
 * User: Matej
 * Date: 21.4.2017.
 * Time: 11:33
 */
namespace Ew\WpHelpers\Classes;
/**
 * Helper class for repository Create and Update actions.
 *
 *
 * @since       1.0.0
 *
 * @package     Ew
 * @subpackage  Ew/repositories
 * @author      Matej Bosnjak <matej.bosnjak@enterwell.net>
 */
class Db_Data{

    /**
     * @since   1.0.0
     * @var array
     */
    private $values;

    /**
     * @since   1.0.0
     * @var array
     */
    private $formats;

    /**
     * Lunch_Db_Data constructor.
     * @since   1.0.0
     */
    public function __construct()
    {
        $this->values = [];
        $this->formats = [];
    }

    /**
     * Adds data to this db data.
     *
     * @since   1.0.0
     *
     * @param   string  $value_name     Database row name.
     * @param   mixed   $value          Database row value.
     *
     * @param   string  $format         Format to enter to the db.
     */
    public function add_data($value_name, $value,  $format)
    {
        $this->values[$value_name] = $value;
        $this->formats[] = $format;
    }
    /**
     * Adds data to this db data if value is not empty.
     *
     * @since   1.0.0
     *
     * @param   string  $value_name     Database row name.
     * @param   mixed   $value          Database row value.
     *
     * @param   string  $format         Format to enter to the db.
     */
    public function add_data_if_not_empty($value_name, $value, $format){
        if(empty($value)) return;

        $this->add_data($value_name, $value, $format);
    }

    /**
     * Gets array for wpdb insert or update.
     *
     * @since   1.0.0
     *
     * @return  array       Array of object (values with key =>  value and according formats) for db update
     *                      or insert.
     */
    public function get_data()
    {
        return [
            'values' => $this->values,
            'formats' => $this->formats
        ];
    }
}