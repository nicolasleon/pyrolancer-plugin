<?php namespace Responsiv\Pyrolancer\Classes;

use Session;
use Validator;
use ValidationException;
use Responsiv\Pyrolancer\Models\Project as ProjectModel;
use Responsiv\Pyrolancer\Models\ProjectOption;

/**
 * A deferred object for holding project data
 */

class ProjectData
{

    const SESSION_NAME = 'pyrolancer-project';

    /**
     * This will validate the project type and project name.
     * @return void
     */
    public static function startProject()
    {
        /*
         * Validate input
         */
        $data = input('Project');
        $rules = [
            'name'            => 'required|min:5|max:255',
            'project_type'    => 'required'
        ];


        $validation = Validator::make($data, $rules);
        if ($validation->fails())
            throw new ValidationException($validation);

        self::reset();
        self::saveProjectData();
    }

    /**
     * This will validate the remaining data.
     * @return void
     */
    public static function previewProject()
    {
        $data = self::load();
        if (!$data) $data = input('Project');
        else $data = array_merge($data, input('Project'));

        /*
         * Validate input
         */
        $rules = [
            'name'             => 'required|min:5|max:255',
            'project_type'     => 'required',
            'skills'           => 'required|array',
            'position_type'    => 'required',
            'description'      => 'required',
        ];

        if (!array_get($data, 'is_remote'))
            $rules['address'] = 'required';

        $projectTypes = ProjectOption::forType(ProjectOption::PROJECT_TYPE)->lists('id', 'code');

        /*
         * Advertisement
         */
        if (array_get($data, 'project_type') == array_get($projectTypes, 'advert')) {
            $rules['instructions'] = 'required';
        }

        /*
         * Auction
         */
        else if (array_get($data, 'project_type') == array_get($projectTypes, 'auction')) {
            $budgetTypes = ProjectOption::forType(ProjectOption::BUDGET_TYPE)->lists('id', 'code');

            $rules['budget_type'] = 'required';

            if (array_get($data, 'budget_type') == array_get($budgetTypes, 'fixed'))
                $rules['budget_fixed'] = 'required';

            if (array_get($data, 'budget_type') == array_get($budgetTypes, 'hourly'))
                $rules['budget_timeframe'] = $rules['budget_hourly'] = 'required';
        }

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
            throw new ValidationException($validation);

        /*
         * Validate State and Country from Google
         */
        // @todo

        self::saveProjectData();
    }

    /**
     * This create the project and assign it to the user
     * @return void
     */
    public static function submitProject($user)
    {
        if (!$user)
            return false;

        $project = self::getProjectObject();
        $project->user = $user;
        $project->save();

        return $project;
    }

    public static function getProjectObject()
    {
        $data = self::load();
        if (empty($data))
            return null;

        $project = new ProjectModel($data);
        return $project;
    }

    public static function saveProjectData()
    {
        $data = self::load();
        $data = array_merge($data, input('Project', []));
        self::save($data);
    }

    public static function saveSessionKey()
    {
        $data = self::load();
        $data['session_key'] = input('session_key');
        self::save($data);
    }

    public static function getSessionKey()
    {
        $data = self::load();
        if (isset($data['session_key']))
            return $data['session_key'];

        return null;
    }

    public static function reset()
    {
        Session::put(self::SESSION_NAME, []);
    }

    public static function load()
    {
        return Session::get(self::SESSION_NAME, []);
    }

    public static function save($data)
    {
        Session::put(self::SESSION_NAME, $data);
    }

}