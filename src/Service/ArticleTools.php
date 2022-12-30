<?php

namespace App\Service;

use App\Repository\ArticleRepository;

class ArticleTools
{
    private $limit;

    public function __construct($limit)
    {
        $this->limit = $limit;
    }

    public function articlePagination(ArticleRepository $articleRepository)
    {

    }
}