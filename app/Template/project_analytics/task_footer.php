<tr>
  <td />
  <td><strong><?= t('Sum for Task ') . $values['task_title'] ?></strong></td>
  <td />
  <td class='right'><strong><?= n($groupValues['sum(subtask_time_tracking.time_spent)']).' '.t('hours') ?></strong></td>
</tr>
