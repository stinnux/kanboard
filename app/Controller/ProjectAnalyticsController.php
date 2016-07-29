<?php

namespace Kanboard\Controller;
use Kanboard\Controller\BaseController;
use Kanboard\Model\SubtaskTimeTrackingModel;

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

    var_dump($values);

    if (defined($values['from']) && defined($value['to'])) {
        print "From: " . $values['from'];
        print "To: " . $values['to'];

        $paginator = $this->paginator
            ->setUrl('ProjectAnalyticController', 'billable', array())
            ->setMax(30)
            ->setOrder(SubtaskTimeTrackingModel::TABLE.'.start')
            ->setQuery($this->subtaskTimeTrackingModel->getBillableHoursQuery()->getQuery())
            ->calculate();
    } else {
        $paginator = $this->paginator;
    }

    $this->response->html($this->helper->layout->projectAnalytics('project_analytics/billable', array(
        'title' => t("Billable hours"),
        'function' => 'billable',
        'values' => $values,
        'paginator' => $paginator,
        'date_format' => $this->configModel->get('application_date_format'),
        'date_formats' => $this->dateParser->getAvailableFormats($this->dateParser->getDateFormats()),
    )));

    }
}

 ?>
