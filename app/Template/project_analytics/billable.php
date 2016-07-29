
<?= $this->render('project_analytics/daterange',
      array('values' => $values,
            'function' => $function,
            'date_format' => $date_format,
            'date_formats' => $date_formats
      )
); ?>


<?php if ($paginator->isEmpty()): ?>
    <p class="alert"><?= t('No Subtask Time Tracking entries found.') ?></p>
<?php elseif (! $paginator->isEmpty()): ?>
    <table class="table-fixed">
        <tr>
            <th class="column-15"><?= $paginator->order(t('User'), 'username') ?></th>
            <th><?= $paginator->order(t('Subtask'), 'subtask_title') ?></th>
            <th class="column-20"><?= $paginator->order(t('Start'), 'start') ?></th>
            <th class="column-20"><?= $paginator->order(t('End'), 'end') ?></th>
            <th class="column-10"><?= $paginator->order(t('Time billable'), \Kanboard\Model\SubtaskTimeTrackingModel::TABLE.'.time_billable') ?></th>
        </tr>
        <?php foreach ($paginator->getCollection() as $record): ?>
        <tr>
            <td><?= $this->url->link($this->text->e($record['user_fullname'] ?: $record['username']), 'UserViewController', 'show', array('user_id' => $record['user_id'])) ?></td>
            <td><?= t($record['subtask_title']) ?></td>
            <td><?= $this->dt->datetime($record['start']) ?></td>
            <td><?= $this->dt->datetime($record['end']) ?></td>
            <td><?= n($record['time_billable']).' '.t('hours') ?></td>
        </tr>
        <?php endforeach ?>
    </table>
    <?= $paginator ?>
<?php endif ?>
