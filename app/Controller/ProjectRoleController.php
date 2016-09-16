<?php

namespace Kanboard\Controller;

use Kanboard\Core\Controller\AccessForbiddenException;

/**
 * Class ProjectRoleController
 *
 * @package Kanboard\Controller
 * @author  Frederic Guillot
 */
class ProjectRoleController extends BaseController
{
    /**
     * Show roles and permissions
     */
    public function show()
    {
        $project = $this->getProject();

        $this->response->html($this->helper->layout->project('project_role/show', array(
            'project' => $project,
            'roles' => $this->projectRoleModel->getAllWithRestrictions($project['id']),
            'title' => t('Custom Project Roles'),
        )));
    }

    /**
     * Show form to create new role
     *
     * @param  array $values
     * @param  array $errors
     * @throws AccessForbiddenException
     */
    public function create(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();

        $this->response->html($this->template->render('project_role/create', array(
            'project' => $project,
            'values' => $values + array('project_id' => $project['id']),
            'errors' => $errors,
        )));
    }

    /**
     * Save new role
     */
    public function save()
    {
        $project = $this->getProject();
        $values = $this->request->getValues();

        list($valid, $errors) = $this->projectRoleValidator->validateCreation($values);

        if ($valid) {
            $role_id = $this->projectRoleModel->create($project['id'], $values['role']);

            if ($role_id !== false) {
                $this->flash->success(t('Your custom project role has been created successfully.'));
            } else {
                $this->flash->failure(t('Unable to create custom project role.'));
            }

            $this->response->redirect($this->helper->url->to('ProjectRoleController', 'show', array('project_id' => $project['id'])));
        } else {
            $this->create($values, $errors);
        }
    }

    /**
     * Confirm suppression
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProject();
        $role_id = $this->request->getIntegerParam('role_id');

        $this->response->html($this->helper->layout->project('project_role/remove', array(
            'project' => $project,
            'role' => $this->projectRoleModel->getById($project['id'], $role_id),
        )));
    }

    /**
     * Remove a custom role
     *
     * @access public
     */
    public function remove()
    {
        $project = $this->getProject();
        $this->checkCSRFParam();
        $role_id = $this->request->getIntegerParam('role_id');

        if ($this->projectRoleModel->remove($project['id'], $role_id)) {
            $this->flash->success(t('Custom project role removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this project role.'));
        }

        $this->response->redirect($this->helper->url->to('ProjectRoleController', 'show', array('project_id' => $project['id'])));
    }
}
