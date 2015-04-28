<?php namespace Ahoy\Pyrolancer\Components;

use Cms\Classes\ComponentBase;
use Ahoy\Pyrolancer\Models\Worker as WorkerModel;
use Ahoy\Pyrolancer\Models\Portfolio as PortfolioModel;
use Ahoy\Pyrolancer\Models\PortfolioItem;

class WorkerPortfolio extends ComponentBase
{

    use \Ahoy\Traits\ComponentUtils;

    public function componentDetails()
    {
        return [
            'name'        => 'Portfolio Manage Component',
            'description' => 'Management features for a worker portfolio'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    //
    // Object properties
    //

    public function worker()
    {
        return $this->lookupObject(__FUNCTION__, WorkerModel::getFromUser());
    }

    public function portfolio()
    {
        return $this->lookupObject(__FUNCTION__, PortfolioModel::getFromWorker());
    }

    public function portfolioItems()
    {
        return $this->portfolio()->items;
    }

    public function hasPortfolio()
    {
        return $this->portfolio()->items->count() > 0;
    }

    //
    // AJAX
    //

    public function onCompleteProfile()
    {
        $this->onCreateItem();
    }

    public function onDeleteItem()
    {
        $item = PortfolioItem::find(post('id'));
        if (!$item->portfolio || !$item->portfolio->isOwner())
            return;

        $item->delete();
    }

    public function onManageItem()
    {
        return post('id')
            ? $this->onUpdateItem()
            : $this->onCreateItem();
    }

    public function onCreateItem()
    {
        $portfolio = $this->portfolio();

        $item = new PortfolioItem;
        $item->portfolio = $portfolio;
        $item->fill((array) post('PortfolioItem'));
        $item->save(null, post('_session_key'));
    }

    public function onUpdateItem()
    {
        $item = PortfolioItem::find(post('id'));
        if (!$item->portfolio || !$item->portfolio->isOwner())
            return;

        $item->fill((array) post('PortfolioItem'));
        $item->save(null, post('_session_key'));
    }

    public function onLoadItemForm()
    {
        if (!$id = post('id')) return;

        $item = PortfolioItem::find($id);
        if (!$item->portfolio || !$item->portfolio->isOwner())
            return;

        $this->page['item'] = $item;
    }

}