<tr>
  <td />
  <td><strong><?= t('Sum for project ') . $values['project_name'] ?></strong></td>
  <td />
  <td class='right'><strong><?= n($groupValues['sum(subtask_time_tracking.time_spent)']).' '.t('hours') ?></strong></td>
</tr>
