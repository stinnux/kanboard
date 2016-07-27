<?php if (! empty($subtasks)): ?>
    <table
        class="subtasks-table table-stripped"
        data-save-position-url="<?= $this->url->href('SubtaskController', 'movePosition', array('project_id' => $task['project_id'], 'task_id' => $task['id'])) ?>"
    >
    <thead>
        <tr>
            <th class="column-40"><?= t('Title') ?></th>
            <th><?= t('Assignee') ?></th>
            <th><?= t('Time tracking') ?></th>
            <?php if ($editable): ?>
                <th class="column-5"></th>
                <th class="column-5"></th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subtasks as $subtask): ?>
        <tr data-subtask-id="<?= $subtask['id'] ?>">
            <td>
                <?php if ($editable): ?>
                    <i class="fa fa-arrows-alt draggable-row-handle" title="<?= t('Change subtask position') ?>"></i>
                    <?= $this->subtask->toggleStatus($subtask, $task['project_id'], true) ?>
                <?php else: ?>
                    <?= $this->subtask->getTitle($subtask) ?>
                <?php endif ?>
            </td>
            <td>
                <?php if (! empty($subtask['username'])): ?>
                    <?= $this->text->e($subtask['name'] ?: $subtask['username']) ?>
                <?php endif ?>
            </td>
            <td>
                <ul class="no-bullet">
                    <li>
                        <?php if (! empty($subtask['time_spent'])): ?>
                            <strong><?= $this->text->e($subtask['time_spent']).'h' ?></strong> <?= t('spent') ?>
                        <?php endif ?>

                        <?php if (! empty($subtask['time_estimated'])): ?>
                            <strong><?= $this->text->e($subtask['time_estimated']).'h' ?></strong> <?= t('estimated') ?>
                        <?php endif ?>
                        <?php if (! empty($subtask['time_billable'])): ?>
                            <strong><?= $this->text->e($subtask['time_billable']).'h' ?></strong> <?= t('estimated') ?>
                        <?php endif ?>

                    </li>
                    <?php if ($editable && $subtask['user_id'] == $this->user->getId()): ?>
                    <li>
                        <?php if ($subtask['is_timer_started']): ?>
                            <i class="fa fa-pause"></i>
                            <?= $this->url->link(t('Stop timer'), 'SubtaskStatusController', 'timer', array('timer' => 'stop', 'project_id' => $task['project_id'], 'task_id' => $subtask['task_id'], 'subtask_id' => $subtask['id']), false, 'subtask-toggle-timer') ?>
                            (<?= $this->dt->age($subtask['timer_start_date']) ?>)
                        <?php else: ?>
                            <i class="fa fa-play-circle-o"></i>
                            <?= $this->url->link(t('Start timer'), 'SubtaskStatusController', 'timer', array('timer' => 'start', 'project_id' => $task['project_id'], 'task_id' => $subtask['task_id'], 'subtask_id' => $subtask['id']), false, 'subtask-toggle-timer') ?>
                        <?php endif ?>
                    </li>
                    <?php endif ?>
                </ul>
            </td>
            <?php if ($editable): ?>
                <td>
                    <?= $this->render('subtask/menu', array(
                        'task' => $task,
                        'subtask' => $subtask,
                    )) ?>
                </td>
                <td>
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    <?= $this->url->link(t('New'), 'TimetrackingeditorController', 'create', array('plugin' => 'TimetrackingEditor', 'task_id' => $task['id'], 'project_id' => $task['project_id'], 'subtask_id' => $subtask['id']), false, 'popover') ?>
                </td>

            <?php endif ?>
        </tr>
        <?php endforeach ?>
    </tbody>
    </table>
<?php endif ?>
