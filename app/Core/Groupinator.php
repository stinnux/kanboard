<?php

namespace Kanboard\Core;

use Kanboard\Core\Paginator;
use Pimple\Container;
use Picodb\Table;
use Kanboard\Core\Helper;
use Kanboard\Helper\UrlHelper;
use Kanboard\Core\Base;

/**
 * Grouping Helper
 *
 * When using a query to print out a table you can now group values together.
 *
 * For any field specified you can print out a header and a footer if the value changes
 * and you can count or sum up any columns.
 */
class Groupinator extends Paginator
{
  /**
   * @var $_grouping
   *
   * Structur of the array:
   * "groupColumn" => array(
   *     'groupName' => "project",
   *     'funcColumns' => array(
   *          'column1' => 'sum',
   *          'column2' => 'count',
   *          'column3' => 'avg'),
   *      'headerTemplate' => 'analytics/projectHeader',
   *      'footerTemplate' => 'anayltics/projectFooter'
   *      )
   */
   private $_grouping=array();

   /**
    * @var $_aggregate
    */

   private $_aggregate=array();

  /**
   * @var $_groupValues
   *
   * Structur of the array:
   * 'groupColumn' => array(
   *      'lastValue' => 'last seen value, used for grouping',
   *      'funcColumns' => array(
   *         'column1' => 'value1',
   *         'column2' => 'value2',
   *         'column3' => 'value3'))
   */
   private $_groupValues=array();

   /**
    *
    * var $_select
    */
    private $_select;

    /**
     * var $_groupby
     */
    private $_groupby;

   /**
    * var $details;
    */
    private $details;


   /**
    * Set a PicoDb query
    *
    * @access public
    * @param  \PicoDb\Table $query
    * @return Paginator
    */
   public function setQuery(Table $query)
   {
       $this->query = $query;
       $this->total = $this->query->count();
       return $this;
   }

 /**
  * Add a Grouping Column
  *
  * @access public
  * @param $groupColumn name of the column to group by
  * @param $groupName name of this grouping
  * @param array $funcColumns array of column names and corresponding functions
  * @param template $header header function to call
  * @param template $footer footer function to call
  * @return boolean
  */
  public function addGroup($groupColumn, $groupName)
  {
    $this->_grouping[$groupColumn] = array(
        'groupName' => $groupName
      );

      return $this;
  }

  /**
   * Add a aggregate function
   *
   * @access public
   * @var $column The column name
   * @var $function the aggregate function
   */
   public function addAggregate($column, $function)
   {
     $this->_aggregate[$column] = $function;

     return $this;
   }

   /**
    * Set the details template
    *
    * @access public
    * @var $details the details template
    */

   public function setDetails($details)
   {
     $this->details = $details;

     return $this;
   }

  /**
   * Execute a PicoDb query
   *
   * @access public
   * @return array
   */
  public function executeQuery()
  {
      if ($this->query !== null) {
          $this->groupquery = clone $this->query;
          // Allways add counting query.
          $this->addSelect("count(*) as _count");
          // Change the query, so that we retrieve all grouping values
          foreach ($this->_grouping as $column => $group) {
              $this->addGroupBy($column);
              $this->addSelect($column.' AS `'.$column.'`');
          }
          $this->addSelects($this->_aggregate);


          $this->groupquery->select($this->_select);
          $this->groupquery->groupBy($this->_groupby);

          $current_offset = 0;
          $result = array();

          foreach ($this->groupquery->findAll() as $groupvalue) {
            sumUpGroupValues($groupvalue);
            // Move through the groups until the offset is reached
            if ($current_offset + $groupvalue['_count'] >= $this->offset) {
              // Read details first, so that they are available for the header
              $values = $this->getDetails($groupvalue);
              $this->addAllHeaders($result,$groupvalue,$values[0]);
              $this->addDetails($result,$groupvalue,$values);
              $this->addAllFooters($result,$groupvalue,$values[0]);
              $oldgroupvalue = $groupvalue;
              $current_offset += $groupvalue['_count'];
            } else {
              $current_offset += $groupvalue['_count'];
            }
          }
      }
      return $result;
  }

  private function sumUpGroupValue($groupvalue)
  {
    // TODO: Sum up all values in a nice array
    //foreach ($this->_gr

    //}
  }

  private function addAllHeaders(&$result,$groupValues,$values)
  {
    foreach ($this->_grouping as $groupColumn) {
      $this->addHeader($result,$groupColumn, $groupValues, $values);
    }
  }

  private function addHeader(&$result,$groupColumn,$groupValues,$values)
  {
    array_push($result, array(
      'groupType' => 'header',
      'groupName' => $groupColumn['groupName'],
      'groupValues' => $groupValues,
      'values' => $values));
  }

  private function addAllFooters(&$result, $groupValues, $values)
  {
    foreach ($this->_grouping as $groupColumn) {
      $this->addFooter($result, $groupColumn, $groupValues, $values);
    }
  }

  private function addFooter(&$result, $groupColumn, $groupValues, $values)
  {
    array_push($result, array(
      'groupType' => 'footer',
      'groupName' => $groupColumn['groupName'],
      'groupValues' => $groupValues,
      'values' => $values
    ));
  }

  private function getDetails($groupValues)
  {
    // Execute the original query, but add additional where clauses for the group
    $detailquery = clone $this->query;
    foreach ($this->_grouping as $groupColumn => $groupDetails) {
         $detailquery->eq($groupColumn,$groupValues[$groupColumn]);
    }
   return $detailquery->findAll();
  }

  private function addDetails(&$result, $groupValues, $values)
  {
    array_push($result, array(
      'groupType' => 'details',
      'groupName' => "__details__",
      'groupValues' => $groupValues,
      'values' => $values
    ));
  }

  private function addGroupBy(string $column)
  {
    if (!empty($this->_groupby)) {
      $this->_groupby .= ", ";
    }
    $this->_groupby .= $column;
  }

  /**
   *
   *
   */
   private function addSelect(string $column)
   {
     if (!empty($this->_select)) {
       $this->_select .= ", ";
     }
     $this->_select .= $column;
   }

   /**
    *
    */
    private function addSelects(array $columns)
    {
      foreach ($columns as $col => $func) {
        $this->addSelect($func . "(" . $col . ")");
      }
    }

/**
 * reset all grouping values to 0.
 *
 * @access private
 */
 private function initAllValues()
 {
   $this->_groupValues=array();
 }

/**
 * reset grouping values for a named field to 0.
 *
 * @access private
 */
 private function initValue($field)
 {
   $this->_groupValues[$field]=array();
 }

 /**
 * call all registered Headers
 *
 * @access private
 */
 private function callHeaders($values)
 {
   foreach ($this->_grouping as $groupColumn) {
     callGroupHeader($groupColumn, $values);
   }
 }

 /**
  * Call group Header for a specified groupColumn
  *
  * @access private
  */
  private function callGroupHeader($groupColumn, $values)
  {
    if (!isset($this->groupValues[$groupColumn]) ||
        !isset($this->groupValues[$groupColumn]['lastValue']) ||
        $this->groupValues[$groupColumn]['lastValue'] != $values[$groupColumn])
    {

      if (!empty($this->_grouping[$groupColumn]['headerTemplate']))
      {
        $this->render($this->_grouping[$groupColumn]['headerTemplate'],
          array('values'=> $values,
                'lastValue' => $this->_groupValues[$groupColumn]['lastValue'],
                'funcColumns' => $this->_groupValues[$groupColumn]['funcColumns']
              )
            );
    }
  }
}

  /**
   * Call group footer for a specified groupColumn
   *
   * @access private
   */
   private function callGroupFooter($groupColumn, $values)
   {
     if (!isset($this->groupValues[$groupColumn]) ||
         !isset($this->groupValues[$groupColumn]['lastValue']) ||
         $this->groupValues[$groupColumn]['lastValue'] != $values[$groupColumn])
     {

       if (!empty($this->_grouping[$groupColumn]['footerTemplate'])) {
         $this->render($this->_grouping[$groupColumn]['footerTemplate'],
           array('values'=> $values,
                 'lastValue' => $this->_groupValues[$groupColumn]['lastValue'],
                 'funcColumns' => $this->_groupValues[$groupColumn]['funcColumns']
               )
             );
     }
   }
  }



}



 ?>
