<tr>
  <td />
  <td><strong><?= t('Sum for subtask ') . $values['subtask_title'] ?></strong></td>
  <td />
  <td class='right'><strong><?= n($groupValues_notbillable['sum(subtask_time_tracking.time_spent)']). ' '.t('hours') ?></strong></td>
  <td class='right'><strong><?= n($groupValues['sum(subtask_time_tracking.time_spent)']). ' '.t('hours') ?></strong></td>
</tr>
