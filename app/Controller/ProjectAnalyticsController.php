<?php

namespace Kanboard\Controller;
use Kanboard\Controller\BaseController;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\SubtaskTimeTrackingModel;
use Kanboard\Model\TaskModel;
use Kanboard\Core\Groupinator;

/**
* Project Analytics Controller
*
* @package Kanboard\Controller
* @author Thomas Stinner
*/

class ProjectAnalyticsController extends BaseController
{
  /**
  * Show all Timetracking Events within a specified time range.
  *
  */
  public function billable()
  {
    $values = $this->request->getValues();

    if (empty($values)) {
      $values['from'] = $this->request->getStringParam('from', date('Y-m-d', strtotime('first day of last month')));
      $values['to'] = $this->request->getStringParam('to', date('Y-m-d', strtotime('last day of last month')));
    }

    $groupinator = $this->groupinator
        ->setUrl("ProjectAnalyticsController","billable")
        ->setQuery($this->subtaskTimeTrackingModel->getBillableHoursQuery(
          $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['from'])),
          $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['to']))))
        ->addAggregate(SubtaskTimeTrackingModel::TABLE.'.time_spent', Groupinator::SUM)
        ->addGroup(SubtaskTimeTrackingModel::TABLE.'.subtask_id', 'Subtask', array('header' => Groupinator::NEVER, 'footer' => Groupinator::ALWAYS))
        ->addGroup(SubtaskModel::TABLE.'.task_id', 'Task', array('header' => Groupinator::NEVER, 'footer' => Groupinator::LAST))
        ->addGroup(TaskModel::TABLE.'.project_id', 'Project', array('header' => Groupinator::FIRST, 'footer' => Groupinator::LAST))
        ->setDetails("project_analytics/details")
        ->setMax(20)
        ->calculate();

    $this->response->html($this->helper->layout->projectAnalytics('project_analytics/billable', array(
        'title' => t("Billable hours"),
        'function' => 'billable',
        'values' => $values,
        'paginator' => $groupinator,
        'date_format' => $this->configModel->get('application_date_format'),
        'date_formats' => $this->dateParser->getAvailableFormats($this->dateParser->getDateFormats()),
    )));

    }
}

 ?>
