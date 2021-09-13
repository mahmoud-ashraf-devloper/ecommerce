<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'links' => [
                'next' => $this->getNextPage(),
                'previous' => $this->getPreviousPage(),
                'last'  => $this->getLastPage(),
            ],
            'meta' => [
                'currentPage' => $this->currentPage(),
                'lastPage' => $this->lastPage(),
                'path' => $this->path(),
                'perPage' => $this->perPage(),
            ]
        ];
    }

    private function getNextPage()
    {
        return ($this->currentPage() == $this->lastPage()) ? null : $this->path().'?page='.$this->currentPage() + 1 ;
    }
    private function getLastPage()
    {
        return ($this->currentPage() == $this->lastPage()) ? null : $this->path().'?page='.$this->lastPage();
    }
    private function getPreviousPage()
    {
        return ($this->currentPage() == 1) ? null :$this->path().'?page='.$this->currentPage() - 1;
    }
}
