<?php

require_once __DIR__.'/../Base.php';

use Kanboard\Core\Groupinator;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\SubtaskTimeTrackingModel;
use Kanboard\Model\TaskCreationModel;

class GroupinatorTest extends Base
{
  function prepare()
  {
    $projectModel = new ProjectModel($this->container);
    $taskCreationModel = new TaskCreationModel($this->container);
    $subtaskModel = new SubtaskModel($this->container);
    $subtaskTimeTrackingModel = new SubtaskTimeTrackingModel($this->container);

    $this->assertEquals(1, $projectModel->create(array(
      'name' => 'Test Project 1',
      'is_active' => 1,
    )));

    $this->assertEquals(2, $projectModel->create(array(
      'name' => 'Test Project 2',
      'is_active' => 1,
    )));

    $this->assertEquals(1, $taskCreationModel->create(array(
      'project_id' => 1,
      'title' => 'Task 1',
    )));
    $this->assertEquals(2, $taskCreationModel->create(array(
        'project_id' => 1,
        'title' => 'Task 2',
    )));
    $this->assertEquals(3, $taskCreationModel->create(array(
        'project_id' => 1,
        'title' => 'Task 3',
    )));
    $this->assertEquals(4, $taskCreationModel->create(array(
        'project_id' => 2,
        'title' => 'Task 4',
    )));
    $this->assertEquals(5, $taskCreationModel->create(array(
        'project_id' => 2,
        'title' => 'Task 5',
    )));


  }



}
