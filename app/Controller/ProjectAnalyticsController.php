<?php

namespace Kanboard\Controller;
use Kanboard\Controller\BaseController;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\SubtaskTimeTrackingModel;
use Kanboard\Model\TaskModel;

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
      $values['to'] = $this->request->getStringParam('to', date('Y-m-d'), strtotime('first day of this month'));
    }

    print "From: " . $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['from']));
    print "To: " .   $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['to']));

    $groupinator = $this->groupinator
        ->setQuery($this->subtaskTimeTrackingModel->getBillableHoursQuery(
          $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['from'])),
          $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['to']))))
        ->addAggregate(SubtaskTimeTrackingModel::TABLE.'.time_spent','sum')
        ->addGroup(SubtaskTimeTrackingModel::TABLE.'.subtask_id', 'Subtask')
        ->addGroup(SubtaskModel::TABLE.'.task_id', 'Task')
        ->addGroup(TaskModel::TABLE.'.project_id', 'Project')
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
