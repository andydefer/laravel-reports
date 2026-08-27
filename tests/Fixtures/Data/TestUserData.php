<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Tests\Fixtures\Data;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Utils\DataObject;
use AndyDefer\LaravelReports\Tests\Fixtures\Collections\TestPostDataCollection;
use AndyDefer\LaravelReports\Tests\Fixtures\Enums\TestUserRole;
use AndyDefer\LaravelReports\Tests\Fixtures\Enums\TestUserStatus;

final class TestUserData extends AbstractData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly TestUserStatus $status,
        public readonly TestUserRole $role,
        public readonly ?int $age,
        public readonly ?DataObject $metadata,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?TestPostDataCollection $posts = null,
    ) {}
}
