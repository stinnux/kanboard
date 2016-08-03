<?php

namespace Kanboard\Controller;
use Kanboard\Controller\BaseController;

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

    if (!empty($values)) {
        $paginator = $this->paginator
            ->setUrl('ProjectAnalyticController', 'billable', array())
            ->setMax(30)
            ->setQuery($this->subtaskTimeTrackingModel->getBillableHoursQuery(
              $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['from'])),
              $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($values['to']))))
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
