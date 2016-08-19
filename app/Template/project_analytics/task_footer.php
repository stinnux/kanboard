<tr>
  <td />
  <td><strong><?= t('Sum for Task ') . $values['task_title'] ?></strong></td>
  <td />
  <td class='right'><strong><?= n( (isset($groupValues_notbillable['sum(subtask_time_tracking.time_spent)']) ? $groupValues_notbillable['sum(subtask_time_tracking.time_spent)']: 0)).' '.t('hours') ?></strong></td>
  <td class='right'><strong><?= n( (isset($groupValues['sum(subtask_time_tracking.time_spent)']) ? $groupValues['sum(subtask_time_tracking.time_spent)'] : 0)) .' '.t('hours') ?></strong></td>
</tr>
