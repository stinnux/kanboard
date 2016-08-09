<?php

namespace Kanboard\Core;


use Picodb\Table;

/**
 * Grouping Helper.
 *
 * When using a query to print out a table you can now group values together.
 *
 * For any field specified you can print out a header and a footer if the value changes
 * and you can count or sum up any columns.
 */
class Groupinator extends Paginator
{
    /**
    * @var
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
   private $_grouping = array();

   /**
    * @var
    */
   private $_aggregate = array();


   /**
    * @var array $_sum Summary and Subtotals
    */
    private $_sum = array ();

   /**
    * @var
    *
    * Structur of the array:
    * 'groupColumn' => array(
    *      'lastValue' => 'last seen value, used for grouping',
    *      'funcColumns' => array(
    *         'column1' => 'value1',
    *         'column2' => 'value2',
    *         'column3' => 'value3'))
    */
   private $_groupValues = array();

    /**
     * var $_select.
     */
    private $_select;

    /**
     * var $_groupby.
     */
    private $_groupby;

    /**
     * var $details;.
     */
    private $details;

   /**
    * Set a PicoDb query.
    *
    * @param  \PicoDb\Table $query
    *
    * @return Paginator
    */
   public function setQuery(Table $query)
   {
       $this->query = $query;
       $this->total = $this->query->count();

       return $this;
   }

  /**
   * Add a Grouping Column.
   *
   * @param $groupColumn name of the column to group by
   * @param $groupName name of this grouping
   * @param array $funcColumns array of column names and corresponding functions
   * @param template $header header function to call
   * @param template $footer footer function to call
   *
   * @return bool
   */
  public function addGroup($groupColumn, $groupName, array $repeat = array())
  {
      $this->_grouping[$groupColumn] = array(
        'groupName' => $groupName,
        'repeat' => $repeat
      );

      return $this;
  }

   /**
    * Add a aggregate function.
    *
    * @var The column name
    * @var $function the aggregate function
    */
   public function addAggregate($column, $function)
   {
       $this->_aggregate[$column] = $function;

       return $this;
   }

   /**
    * Set the details template.
    *
    * @var the details template
    */
   public function setDetails($details)
   {
       $this->details = $details;

       return $this;
   }

  /**
   * Execute a PicoDb query.
   *
   * @return array
   */
  public function executeQuery()
  {
      if ($this->query !== null) {
          $this->groupquery = clone $this->query;
          // Allways add counting query.
          $this->addSelect('count(*) as _count');
          // Change the query, so that we retrieve all grouping values
          foreach ($this->_grouping as $column => $group) {
              $this->addGroupBy($column);
              $this->addSelect($column.' AS `'.$column.'`');
          }
          $this->addSelects($this->_aggregate);

          $this->groupquery->select($this->_select);
          $this->groupquery->groupBy($this->_groupby);

          $current_offset = 0;
          $result = $this->groupquery->findAll();

          for ($i=0; $i <= count($result); $i++) {
            $groupvalue = $result[$i];
            if (!empty($result[$i-1])) {
              $prev_groupvalue = $result[$i-1];
            } else {
              $prev_groupvalue = null;
            }
            if (!empty($result[$i+1])) {
              $next_groupvalue = $result[$i+1];
            } else {
              $next_groupvalue = null;
            }
            $this->sumUpGroupValues($groupvalue);
            $values = $this->getDetails($groupvalue);
            // Move through the groups until the offset is reached
            if ($current_offset + $groupvalue['_count'] >= $this->offset) {
                $this->addAllHeaders($result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values[0]);
                $this->addDetails($result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values);
                $this->addAllFooters($result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values[0]);
                $current_offset += $groupvalue['_count'];
            } else {
                $current_offset += $groupvalue['_count'];
            }
          }
      }

      return $result;
  }

  private function sumUpGroupValues($groupValue)
  {
    // TODO: Sum up all values in a nice array
    foreach ($this->_grouping as $groupColumn => $groupName) {
        foreach ($this->_aggregate as $column => $func) {
            switch ($func) {
              case 'sum':
                $column = 'sum('.$column.')';
            }
            if (empty($this->_sum[$groupColumn][$groupValue[$groupColumn]][$column])) {
                $this->_sum[$groupColumn][$groupValue[$groupColumn]][$column] = $groupValue[$column];
            } else {
                switch ($func) {
                  case 'sum':
                    $this->_sum[$groupColumn][$groupValue[$groupColumn]][$column] += $groupValue[$column];
                }
            }
        }
    }
  }

/**
 * add All Headers to the result array
 *
 * var $result the result array
 * var $groupvalue the current group values
 * var $values the current values
 * var $position the position (first/last/middle) of the header
 */

private function addAllHeaders(&$result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values)
{
      foreach ($this->_grouping as $groupColumn => $groupDetails) {
        $position = $this->getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue);
        if ($groupDetails['repeat']['header'] == 'allways' ||
            $groupDetails['repeat']['header'] == 'first'  && $position == 'first' ||
            $groupDetails['repeat']['header'] == 'last'   && $position == 'last' ||
            $groupDetails['repeat']['header'] == 'middle' && $position == 'middle')
            {
              $groupValues=$this->getGroupValues($groupColumn,$groupvalue);
              $this->addHeader($result, $groupColumn, $groupValues, $values);
            }
      }
}

private function getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue)
{
    if ($prev_groupvalue == null) {
      return "first";
    }
    if ($next_groupvalue == null) {
      return "last";
    }
    $prev = $prev_groupvalue[$groupColumn];
    $curr = $groupvalue[$groupColumn];
    $next = $next_groupvalue[$groupColumn];

    if ($prev == $curr && $curr==$next) {
      return "middle";
    }
    if ($prev != $curr) {
      return "first";
    }
    if ($curr != $next) {
      return "last";
    }

  }

  private function addHeader(&$result, $groupColumn, $groupvalue, $values)
    {
        array_push($result, array(
      'groupType' => 'header',
      'groupName' => $this->_grouping[$groupColumn]['groupName'],
      'groupValues' => $groupvalue,
      'values' => $values, ));
    }

    private function addAllFooters(&$result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values)
    {
        foreach ($this->_grouping as $groupColumn => $groupDetails) {
          $position = $this->getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue);
            if ($groupDetails['repeat']['footer'] == 'allways' ||
                $groupDetails['repeat']['footer'] == 'first'  && $position == 'first' ||
                $groupDetails['repeat']['footer'] == 'last '  && $position == 'last'  ||
                $groupDetails['repeat']['footer'] == 'middle' && $position == 'middle')
                {
                $groupValues=$this->getGroupValues($groupColumn,$groupvalue);
                $this->addFooter($result, $groupColumn, $groupValues, $values);
              }
        }
    }

    private function addFooter(&$result, $groupColumn, $groupValues, $values)
    {
        array_push($result, array(
            'groupType' => 'footer',
            'groupName' => $this->_grouping[$groupColumn]['groupName'],
            'groupValues' => $groupValues,
            'values' => $values,
          ));
    }

    private function getDetails($groupValues)
    {
        // Execute the original query, but add additional where clauses for the group
        $detailquery = clone $this->query;
        foreach ($this->_grouping as $groupColumn => $groupDetails) {
            $detailquery->eq($groupColumn, $groupValues[$groupColumn]);
        }

        return $detailquery->findAll();
    }

    private function getGroupValues($groupcolumn,$groupvalue)
    {
      $groupValues = array();
      foreach ($this->_aggregate as $column => $func) {
        switch ($func) {
          case 'sum':
            $column='sum('.$column.')';
          }
          $groupValues[$column] = $this->_sum[$groupcolumn][$groupvalue[$groupcolumn]][$column];
        }
      return $groupValues;
    }

    private function addDetails(&$result, $groupValues, $values)
    {
        array_push($result, array(
      'groupType' => 'details',
      'groupName' => '__details__',
      'groupValues' => $groupValues,
      'values' => $values,
    ));
    }

    private function addGroupBy(string $column)
    {
        if (!empty($this->_groupby)) {
            $this->_groupby .= ', ';
        }
        $this->_groupby .= $column;
    }

   /**
    *
    */
   private function addSelect(string $column)
   {
       if (!empty($this->_select)) {
           $this->_select .= ', ';
       }
       $this->_select .= $column;
   }

    /**
     *
     */
    private function addSelects(array $columns)
    {
        foreach ($columns as $col => $func) {
            $this->addSelect($func.'('.$col.')');
        }
    }

 /**
  * reset all grouping values to 0.
  */
 private function initAllValues()
 {
     $this->_groupValues = array();
 }

 /**
  * reset grouping values for a named field to 0.
  */
 private function initValue($field)
 {
     $this->_groupValues[$field] = array();
 }

 /**
  * call all registered Headers.
  */
 private function callHeaders($values)
 {
     foreach ($this->_grouping as $groupColumn) {
         callGroupHeader($groupColumn, $values);
     }
 }

  /**
   * Call group Header for a specified groupColumn.
   */
  private function callGroupHeader($groupColumn, $values)
  {
      if (!isset($this->groupValues[$groupColumn]) ||
        !isset($this->groupValues[$groupColumn]['lastValue']) ||
        $this->groupValues[$groupColumn]['lastValue'] != $values[$groupColumn]) {
          if (!empty($this->_grouping[$groupColumn]['headerTemplate'])) {
              $this->render($this->_grouping[$groupColumn]['headerTemplate'],
          array('values' => $values,
                'lastValue' => $this->_groupValues[$groupColumn]['lastValue'],
                'funcColumns' => $this->_groupValues[$groupColumn]['funcColumns'],
              )
            );
          }
      }
  }

   /**
    * Call group footer for a specified groupColumn.
    */
   private function callGroupFooter($groupColumn, $values)
   {
       if (!isset($this->groupValues[$groupColumn]) ||
         !isset($this->groupValues[$groupColumn]['lastValue']) ||
         $this->groupValues[$groupColumn]['lastValue'] != $values[$groupColumn]) {
           if (!empty($this->_grouping[$groupColumn]['footerTemplate'])) {
               $this->render($this->_grouping[$groupColumn]['footerTemplate'],
           array('values' => $values,
                 'lastValue' => $this->_groupValues[$groupColumn]['lastValue'],
                 'funcColumns' => $this->_groupValues[$groupColumn]['funcColumns'],
               )
             );
           }
       }
   }
}
