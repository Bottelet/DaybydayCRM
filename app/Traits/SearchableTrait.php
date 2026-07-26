<?php

namespace App\Traits;

trait SearchableTrait
{
    public function getSearchType()
    {
        if (property_exists($this, 'useSearchType')) {
            return $this->useSearchType;
        }

        return $this->getTable();
    }

    public function searchLink()
    {
        return '/' . $this->getSearchType() . '/' . $this->external_id;
    }
}
