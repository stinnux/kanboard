SELECT COUNT(*)
FROM "subtask_time_tracking"
LEFT JOIN "subtasks" ON "subtasks"."id"="subtask_time_tracking"."subtask_id"
LEFT JOIN "tasks" ON "tasks"."id"="subtasks"."task_id"
LEFT JOIN "users" ON "users"."id"="subtask_time_tracking"."user_id"
LEFT JOIN "projects" ON "projects"."id"="tasks"."project_id"
WHERE SUBTASK_TIME_TRACKING.IS_BILLABLE = 1
    AND SUBTASK_TIME_TRACKING.START >= 1467331200
    AND SUBTASK_TIME_TRACKING.START <= 1470614400
ORDER BY TASKS.PROJECT_ID ASC,
         SUBTASKS.TASK_ID ASC,
         SUBTASK_TIME_TRACKING.SUBTASK_ID ASC,
         SUBTASK_TIME_TRACKING.START ASC
