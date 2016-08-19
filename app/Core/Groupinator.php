<?php

namespace Kanboard\Core;

use Picodb\Table;

/**
 * TODO: Min, Max, Avg, Count
 * Print Output without paging
 * CSS
 * Unit-Tests
 *
 */


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
 * constants for header and footer positions
 *
 * @var $NEVER never show
 * @var $FIRST show on first occurence of this value
 * @var $LAST show on last occurence of this value
 * @var $MIDDLE do not show on first and last occurence, but in the middle
 * @var $ALWAYS show always
 */
    const NEVER  = 0;
    const FIRST  = 1;
    const LAST   = 2;
    const MIDDLE = 4;
    const ALWAYS = 8;


    const AVG   = 1;
    const SUM   = 2;
    const MIN   = 4;
    const MAX   = 8;
    const COUNT = 16;
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
          $result = array();
          $groupresult = $this->groupquery->findAll();

          for ($i=0; $i < count($groupresult); $i++) {
            // array_push($result, $groupresult[$i]);
            $groupvalue = $groupresult[$i];
            if (!empty($groupresult[$i-1])) {
              $prev_groupvalue = $groupresult[$i-1];
            } else {
              $prev_groupvalue = null;
            }
            if (!empty($groupresult[$i+1])) {
              $next_groupvalue = $groupresult[$i+1];
            } else {
              $next_groupvalue = null;
            }
            $this->sumUpGroupValues($groupvalue);
            $values = $this->getDetails($groupvalue);
            // Move through the groups until the offset is reached
            if ($current_offset + $groupvalue['_count'] < $this->offset) {
                $current_offset += $groupvalue['_count'];
                $current_offset += $this->calculateHeaderAndFooterCount($prev_groupvalue, $groupvalue, $next_groupvalue);
            } else if ($current_offset + $groupvalue['_count'] >= $this->offset &&
              $current_offset < $this->end)
              {
                if ($current_offset + $groupvalue['_count'] > $this->end) {
                  $values = array_slice($values, 0, $this->end-$current_offset);
                }
                if ($current_offset < $this->offset) {
                  $values = array_slice($values, $this->offset-$current_offset);
                  $current_offset = $this->offset;
                }
                $current_offset += $this->addAllHeaders($result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values[0]);
                $this->addDetails($result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values);

                if ($current_offset + count($values) < $this->end) {
                    $current_offset += $this->addAllFooters($result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values[0]);
                }

                $current_offset += $groupvalue['_count'];
            } else {
                break;
            }
          }
      }
      var_dump($this->_sum);
      return $result;
  }

  public function calculate()
  {
    parent::calculate();
    $this->end = $this->offset + $this->limit;

    return $this;
  }

  private function sumUpGroupValues($groupValue)
  {
    $next = &$this->_sum;
    foreach ($this->_grouping as $groupColumn => $groupName) {
      $next[$groupColumn][$groupValue[$groupColumn]] = array();
      $next = &$next[$groupColumn][$groupValue[$groupColumn]];
    }

    print "<pre>";
    var_dump($this->_sum);
    print "</pre>";

    foreach ($this->_aggregate as $column => $func) {
      $column=$this->sqlColumnName($column, $func);
            if (empty($next[$column])) {
                $next[$column] = $groupValue[$column];
            } else {
                switch ($func) {
                  case self::SUM:
                  case self::COUNT:
                    $next[$column] += $groupValue[$column];
                  case self::MIN:
                    $next[$column] = min($next[$column], $groupValue[$column]);
                  case self::MAX:
                    $next[$column] = max($next[$column], $groupValue[$column]);
                  case self::AVG:
                    // @TODO: Check if this is correct, don't think so
                    // @TODO: Hint: multiply with count and divide by total count
                    $next[$column] = ($next[$column] + $groupValue[$column]) / 2;
                }
        }
    }
  }

/**
 * build the sql column name from function and columnname
 *
 * @var $func function
 */
private function sqlColumnName($column, $func) {
  switch ($func) {
    case self::SUM:
      $column='sum('.$column.')';
      break;
    case self::AVG:
      $column='avg('.$column.')';
      break;
    case self::MIN:
      $column='min('.$column.')';
      break;
    case self::MAX:
      $column='max('.$column.')';
      break;
    case self::COUNT:
      $column='count('.$column.')';
      break;
    }
    return $column;
}

/**
 * calculate the count of headers and footers that would be printed
 * @param $result the result array
 * @param $prev_groupvalue the groupvalue of the previous row
 * @param $groupvalue the current group values
 * @param $next_groupvalue the groupvalue of the next row
*/
private function calculateHeaderAndFooterCount($prev_groupvalue, $groupvalue, $next_groupvalue)
  {
    $countrows=0;
    foreach ($this->_grouping as $groupColumn => $groupDetails) {
      $position = $this->getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue);
      if ($groupDetails['repeat']['header'] == self::ALWAYS ||
          $groupDetails['repeat']['header'] == self::FIRST  && $position & self::FIRST ||
          $groupDetails['repeat']['header'] == self::LAST   && $position & self::LAST ||
          $groupDetails['repeat']['header'] == self::MIDDLE && $position & self::MIDDLE)
          {
            $countrows++;
          }

        if ($groupDetails['repeat']['footer'] == self::ALWAYS ||
            $groupDetails['repeat']['footer'] == self::FIRST  && $position & self::FIRST ||
            $groupDetails['repeat']['footer'] == self::LAST   && $position & self::LAST ||
            $groupDetails['repeat']['footer'] == self::MIDDLE && $position & self::MIDDLE)
            {
              $countrows++;
            }
          }
        return $countrows;
  }

/**
 * add All Headers to the result array
 *
 * @param $result the result array
 * @param $prev_groupvalue the groupvalue of the previous row
 * @param $groupvalue the current group values
 * @param $next_groupvalue the groupvalue of the next row
 * @param $values the current values
 */

private function addAllHeaders(&$result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values)
{
  $countrows=0;
      foreach ($this->_grouping as $groupColumn => $groupDetails) {
        $position = $this->getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue);
        if ($groupDetails['repeat']['header'] == self::ALWAYS ||
            $groupDetails['repeat']['header'] == self::FIRST  && $position & self::FIRST ||
            $groupDetails['repeat']['header'] == self::LAST   && $position & self::LAST ||
            $groupDetails['repeat']['header'] == self::MIDDLE && $position & self::MIDDLE)
            {
              $countrows++;
              $groupValues=$this->getGroupValues($groupColumn,$groupvalue);
              $this->addHeader($result, $groupColumn, $groupValues, $values);
            }
      }
      return $countrows;
}

private function getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue)
{

    if ($prev_groupvalue == null && $next_groupvalue == null) {
      return self::FIRST || self::LAST;
    }
    if ($prev_groupvalue == null) {
      return self::FIRST;
    }
    if ($next_groupvalue == null) {
      return self::LAST;
    }
    $prev = $prev_groupvalue[$groupColumn];
    $curr = $groupvalue[$groupColumn];
    $next = $next_groupvalue[$groupColumn];

    if ($prev == $curr && $curr==$next) {
      return self::MIDDLE;
    }
    if ($prev != $curr && $curr != $next) {
      return self::FIRST || self::LAST;
    }
    if ($prev != $curr) {
      return self::FIRST;
    }
    if ($curr != $next) {
      return self::LAST;
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
      $countrows = 0;
        foreach ($this->_grouping as $groupColumn => $groupDetails) {
          $position = $this->getPosition($groupColumn, $prev_groupvalue, $groupvalue, $next_groupvalue);
            if ($groupDetails['repeat']['footer'] == self::ALWAYS ||
                $groupDetails['repeat']['footer'] == self::FIRST  && $position == self::FIRST ||
                $groupDetails['repeat']['footer'] == self::LAST   && $position == self::LAST  ||
                $groupDetails['repeat']['footer'] == self::MIDDLE && $position == self::MIDDLE)
                {
                  $countrows++;
                  $groupValues=$this->getGroupValues($groupColumn,$groupvalue);
                  $this->addFooter($result, $groupColumn, $groupValues, $values);
              }
        }
        return $countrows;
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
          $column = $this->sqlColumnName($column, $func);
          $groupValues[$column] = $this->_sum[$groupcolumn][$groupvalue[$groupcolumn]][$column];
        }
      return $groupValues;
    }

    private function addDetails(&$result, $prev_groupvalue, $groupvalue, $next_groupvalue, $values)
    {
        array_push($result, array(
            'groupType' => 'details',
            'groupName' => '__details__',
            'groupValues' => $groupvalue,
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
        foreach ($columns as $column => $func) {
          $this->addSelect($this->sqlColumnName($column, $func));
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

}
