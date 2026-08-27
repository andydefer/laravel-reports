<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Tests\Fixtures\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\LaravelReports\Tests\Fixtures\Records\TestUserRecord;

final class TestUserRecordCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(TestUserRecord::class);
    }
}
