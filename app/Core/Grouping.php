<?php

namespace Kanboard\Core;

use PHP_Token_PRIVATE;
use Pimple\Container;
use Picodb\Table;

/**
 * Grouping Helper
 *
 * When using a query to print out a table you can now group values together.
 *
 * For any field specified you can print out a header and a footer if the value changes
 * and you can count or sum up any columns.
 */

/**
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
 private _$grouping=array();

/**
 * Structur of the array:
 * 'groupColumn' => array(
 *      'lastValue' => 'last seen value, used for grouping',
 *      'funcColumns' => array(
 *         'column1' => 'value1',
 *         'column2' => 'value2',
 *         'column3' => 'value3'))
 */
 private _$groupValues=array();

 /**
  * Constructor
  *
  * @access public
  * @param  \Pimple\Container   $container
  */
 public function __construct(Container $container)
 {
     $this->container = $container;
 }

 /**
  * Set a PicoDb query
  *
  * @access public
  * @param  \PicoDb\Table
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
  * @param callbackup $header  header function to call
  * @param callback $footer footer function to call
  * @return boolean
  */
  public function addGroup($groupColumn, $groupName, array $funcColumns=array(), callback $header, callbackup $footer)
  {
    $this->grouping->add($groupColumn => array(
        'groupName' => $groupName,
        'funcColumns' => $funcColumns,
        'headerTemplate' => $header,
        'footerTemplate' => $footer
      ));
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
          $query = $this->query
                      ->offset($this->offset)
                      ->limit($this->limit)
                      ->orderBy($this->order, $this->direction)
                      ->findAll();

          initAllvalues();
          foreach ($query as $values):
            callHeaders();


      }

      return array();
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
   foreach ($this->_grouping as $groupColumn):
     callGroupHeader($groupColumn, $values);
 }

 /**
  * Call group Header for a specified groupColumn
  */
  * @access private
  */
  private function callGroupHeader($groupColumn, $values)
  {
    if (!isset($this->groupValues[$groupColumn]) ||
        !isset($this->groupValues[$groupColumn]['lastValue']) ||
        $this->groupValues[$groupColumn]['lastValue'] != $values[$groupColumn])
    {

      if (!empty($this->_grouping[$groupColumn]['headerTemplate'])
      {
        $this->render($this->_grouping[$groupColumn]['headerTemplate'],
          array('values'=> $values,
                'lastValue' => $this->_groupValues[$groupColumn]['lastValue'],
                'funcColumns' => $this->_groupValues[$groupColumn]['funcColumns']
              )
            );
    }
  }

  /**
   * Call group footer for a specified groupColumn
   */
   * @access private
   */
   private function callGroupFooter($groupColumn, $values)
   {
     if (!isset($this->groupValues[$groupColumn]) ||
         !isset($this->groupValues[$groupColumn]['lastValue']) ||
         $this->groupValues[$groupColumn]['lastValue'] != $values[$groupColumn])
     {

       if (!empty($this->_grouping[$groupColumn]['footerTemplate'])
       {
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
