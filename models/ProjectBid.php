<?php namespace Responsiv\Pyrolancer\Models;

use Model;
use Responsiv\Pyrolancer\Models\ProjectOption;

/**
 * ProjectBid Model
 */
class ProjectBid extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_pyrolancer_project_bids';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user'    => ['RainLab\User\Models\User'],
        'project' => ['Responsiv\Pyrolancer\Models\Project'],
        'status'  => ['Responsiv\Pyrolancer\Models\ProjectOption', 'conditions' => "type = 'bid.status'"],
    ];

    public function beforeCreate()
    {
        if (!$this->status_id) {
            $this->status = ProjectOption::forType(ProjectOption::BID_STATUS)
                ->whereCode('active')
                ->first();
        }
    }

}