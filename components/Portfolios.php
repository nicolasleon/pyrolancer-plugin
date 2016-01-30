<?php namespace Ahoy\Pyrolancer\Components;

use RainLab\Location\Models\State;
use RainLab\Location\Models\Country;
use Ahoy\Pyrolancer\Models\Worker as WorkerModel;
use Ahoy\Pyrolancer\Models\Portfolio as PortfolioModel;
use Ahoy\Pyrolancer\Models\Vicinity as VicinityModel;
use Ahoy\Pyrolancer\Models\Attribute as AttributeModel;
use Ahoy\Pyrolancer\Models\SkillCategory;
use Cms\Classes\ComponentBase;

class Portfolios extends ComponentBase
{
    use \Ahoy\Traits\ComponentUtils;

    public $filterCategory;
    public $filterVicinity;
    public $filterBudget;

    public function componentDetails()
    {
        return [
            'name'        => 'Portfolios',
            'description' => 'Lists workers who have portfolios set up'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->handleFilter();
    }

    public function skillCategories()
    {
        return SkillCategory::applyVisible()->get();
    }

    public function popularVicinities()
    {
        return VicinityModel::limit(15)->orderBy('count_workers', 'desc')->get();
    }

    public function workers($options = null)
    {
        if ($options === null) {
            $options = $this->getFilterOptionsFromRequest();
        }

        return $this->lookupObject(__FUNCTION__, function() use ($options) {
            return WorkerModel::with('portfolio')
                ->applyVisible()
                ->applyPortfolio()
                ->listFrontEnd($options + ['sort' => 'updated_at desc'])
            ;
        });
    }

    public function onPageWorkers()
    {
        $options = $this->getFilterOptionsFromRequest();
        $options['page'] = post('page');

        $this->page['workers'] = $this->workers($options);
    }

    public function popularCountries()
    {
        return $this->lookupObject(__FUNCTION__, function() {
            return Country::make()
                ->with('states.vicinities')
                ->with('user_count')
                ->isEnabled()
                ->limit(5)
                ->get()
                ->sortBy('user_count.count')
            ;
        });
    }

    public function otherCountries()
    {
        return $this->lookupObject(__FUNCTION__, function() {
            return Country::make()
                ->isEnabled()
                ->offset(5)
                ->limit(0)
                ->get()
            ;
        });
    }

    public function activeFilters()
    {
        $selection = [
            'category' => null,
            'vicinity' => null,
            'budget' => null,
        ];

        if ($requestSelection = $this->getFilterOptionsFromRequest()) {
            $selection = array_merge($selection, $requestSelection);
        }

        return $selection;
    }

    //
    // Filtering
    //

    protected function getFilterOptionsFromRequest()
    {
        $options = [];

        if ($pageNumber = input('page')) {
            $options['page'] = $pageNumber;
        }

        if ($this->filterCategory) {
            $options['categories'] = $options['category'] = $this->filterCategory->id;
        }

        if ($this->filterVicinity) {
            $options['vicinities'] = $options['vicinity'] = $this->filterVicinity->id;
        }

        if ($this->filterBudget) {
            $options['budgets'] = $options['budget'] = $this->filterBudget->id;
        }

        return $options;
    }

    protected function handleFilter()
    {
        if ($category = $this->param('category')) {
            $this->filterCategory = SkillCategory::whereSlug($category)->first();
        }

        if ($vicinity = $this->param('vicinity')) {
            $this->filterVicinity = VicinityModel::whereSlug($vicinity)->first();
        }

        if ($budget = $this->param('budget')) {
            $this->filterBudget = AttributeModel::applyType(AttributeModel::WORKER_BUDGET)
                ->whereCode($budget)
                ->first();
        }
    }

}