
<?= $this->render('project_analytics/daterange',
      array('values' => $values,
            'function' => $function,
            'date_format' => $date_format,
            'date_formats' => $date_formats,
      )
); ?>


<?php if ($paginator->isEmpty()): ?>
    <p class="alert"><?= t('No Subtask Time Tracking entries found.') ?></p>
<?php elseif (!$paginator->isEmpty()): ?>
    <table class="table-analytics">
        <tr>
            <th class="column-10"><?= $paginator->order(t('User'), 'username') ?></th>
            <th><?= t('comment') ?></th>
            <th class="column-20"><?= $paginator->order(t('Start'), 'date') ?></th>
            <th class="column-10 right"><?= $paginator->order(t('Time billable'), 'time_spent') ?></th>
        </tr>
        <?php
          $project_id = null;
          $project_hours = 0;
          $project_name = null;

          $subtask_id = null;
          $subtask_hours = 0;
          $subtask_title = null;

          $task_id = null;
          $task_hours = 0;
          $task_title = null;
         ?>
        <?php foreach ($paginator->getCollection() as $record): ?>
          <?php if ($project_id == null): ?>
            <?= $this->render('project_analytics/project_header',
                array(
                    'project_id' => $project_id,
                    'project_name' => $project_name,
                    'project_hours' => $project_hours,
                    'values' => $record, )); ?>
          <?php $project_id = $record['project_id']; ?>
          <?php endif ?>

          <?php if ($subtask_id != null && $subtask_id != $record['subtask_id']): ?>
          <?= $this->render('project_analytics/subtask_summary',
            array('values' => $record,
                  'subtask_id' => $subtask_id,
                  'subtask_hours' => $subtask_hours,
                  'subtask_title' => $subtask_title,
                )
            ); ?>
          <?php endif ?>

          <?php if ($subtask_id == null || $subtask_id != $record['subtask_id']): ?>
            <?php $subtask_hours = $record['time_spent'];
                  $subtask_id = $record['subtask_id'];
                  $subtask_title = $record['subtask_title'];
                  ?>
          <?php else: ?>
            <?php $subtask_hours += $record['time_spent']; ?>
          <?php endif ?>


          <?php if ($task_id != null && $task_id != $record['task_id']): ?>
            <?= $this->render('project_analytics/task_summary',
              array('values' => $record,
                    'task_id' => $task_id,
                    'task_hours' => $task_hours,
                    'task_title' => $task_title,
                  )
              ); ?>
            <?php endif ?>

            <?php if ($task_id == null || $task_id != $record['task_id']): ?>
              <?php $task_hours = $record['time_spent'];
                    $task_id = $record['task_id'];
                    $task_title = $record['task_title'];
                    ?>
            <?php else: ?>
              <?php $task_hours += $record['time_spent']; ?>
            <?php endif ?>

          <?php if ($project_id != $record['project_id']): ?>
            <?= $this->render('project_analytics/project_summary',
                array('values' => $record,
                      'project_id' => $project_id,
                      'project_hours' => $project_hours,
                      'project_name' => $project_name,
                    )); ?>
            <?php $project_hours = 0;
                  $project_name = $record['project_name'];
                  $project_id = $record['project_id']; ?>
            <?= $this->render('project_analytics/project_header',
                array('values' => $record,
                      'project_id' => $project_id,
                      'project_hours' => $project_hours,
                      'project_name' => $project_name,
                      )); ?>
          <?php else: ?>
                <?php $project_hours += $record['time_spent']; ?>
          <?php endif ?>
        <tr>
            <td><?= $this->url->link($this->text->e($record['user_fullname'] ?: $record['username']), 'UserViewController', 'show', array('user_id' => $record['user_id'])) ?></td>
            <td><?= $this->text->markdown($record['comment']) ?></td>
            <td><?= $this->dt->date($record['start']) ?></td>
            <td class='right'><?= n($record['time_spent']).' '.t('hours') ?></td>
        </tr>
        <?php endforeach ?>
        <?= $this->render('project_analytics/subtask_summary',
          array('values' => $record,
                'subtask_id' => $subtask_id,
                'subtask_hours' => $subtask_hours,
                'subtask_title' => $subtask_title,
              )
          ); ?>
          <?= $this->render('project_analytics/task_summary',
            array('values' => $record,
                  'task_id' => $task_id,
                  'task_hours' => $task_hours,
                  'task_title' => $task_title,
                )
            ); ?>
            <?= $this->render('project_analytics/project_summary',
                array('values' => $record,
                      'project_id' => $project_id,
                      'project_hours' => $project_hours,
                      'project_name' => $project_name,
                    )); ?>

    </table>
    <?= $paginator ?>
<?php endif ?>
