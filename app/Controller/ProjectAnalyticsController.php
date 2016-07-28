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
  *
  */
  public function billable()
  {
    $values = $this->request->getValues();

    $this->response->html($this->helper->layout->projectAnalytics('project_analytics/billable', array(
        'title' => "Billable hours",
        'function' => 'billable',
        'values' => $values,
        'date_format' => $this->configModel->get('application_date_format'),
        'date_formats' => $this->dateParser->getAvailableFormats($this->dateParser->getDateFormats()),
    )));

  }
}

 ?>
