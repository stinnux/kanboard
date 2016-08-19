
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
    <?php  // var_dump($paginator->getCollection()); ?>
    <table class="table-analytics">
        <tr>
            <th class="column-10"><?= t('User') ?></th>
            <th><?= t('comment') ?></th>
            <th class="column-20"><?= t('Start') ?></th>
            <th class="column-10 right"><?= t('Time spent') ?></th>
            <th class="column-10 right"><?= t('Time billable') ?></th>
        </tr>
        <?php foreach ($paginator->getCollection() as $record): ?>
          <?php if ($record['groupType'] == "header"): ?>
            <?php if ($record['groupName'] == "Project"): ?>
              <?= $this->render('project_analytics/project_header', array(
                      'values' => $record['values'],
                      'groupValues' => $record['groupValues'])
                    ); ?>
            <?php endif ?>
          <?php endif ?>
          <?php if ($record['groupType'] == "footer"): ?>
            <?php if ($record['groupName'] == "Task"): ?>
              <?php if ($record['values']['is_billable'] == 0): ?>
                <?php $record_task_notbillable = $record; ?>
              <?php else: ?>
                <?= $this->render('project_analytics/task_footer',
                  array('values' => $record["values"],
                      'groupValues' => $record["groupValues"],
                      'groupValues_notbillable' => isset($record_task_notbillable) ? $record_task_notbillable['groupValues'] : array())
                ); ?>
            <?php endif ?>
          <?php endif ?>
          <?php if ($record['groupName'] == 'Subtask'): ?>
              <?php if ($record['values']['is_billable'] == 0): ?>
                <?php $record_subtask_notbillable = $record; ?>
              <?php else: ?>
                <?= $this->render('project_analytics/subtask_footer', array(
                  'values' => $record['values'],
                  'groupValues' => $record['groupValues'],
                  'groupValues_notbillable' => isset($record_subtask_notbillable) ? $record_subtask_notbillable['groupValues'] : array())
                ); ?>
              <?php endif ?>
            <?php endif ?>
            <?php if ($record['groupName'] == 'Project'): ?>
              <?= $this->render('project_analytics/project_footer', array(
                  'values' => $record['values'],
                  'groupValues' => $record['groupValues'])
                ); ?>
            <?php endif ?>
          <?php endif ?>
          <?php if ($record['groupType'] == 'details'): ?>
            <?php foreach ($record['values'] as $values): ?>
            <tr>
                <td><?= $this->url->link($this->text->e($values['user_fullname'] ?: $values['username']), 'UserViewController', 'show', array('user_id' => $values['user_id'])) ?></td>
                <td><?= $this->text->markdown($values['comment']) ?></td>
                <td><?= $this->dt->date($values['start']) ?></td>
                <?php if ($values['is_billable'] == 0): ?>
                  <td class='right'><?= n($values['time_spent']).' '.t('hours') ?></td>
                  <td />
                <?php else: ?>
                  <td />
                  <td class='right'><?= n($values['time_spent']).' '.t('hours') ?></td>
                <?php endif ?>
            </tr>
          <?php endforeach ?>
          <?php endif ?>
        <?php endforeach ?>
    </table>
    <?= $paginator ?>
<?php endif ?>
