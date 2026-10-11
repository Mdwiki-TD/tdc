<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin;

use App\Coordinator\Admin\AdminResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdminResolver::class)]
class AdminResolverTest extends TestCase
{
    public function testResolveTyAliases(): void
    {
        $resolver = new AdminResolver(true, true);

        $this->assertSame('edit_qid', $resolver->resolveTy('qids/edit_qid'));
        $this->assertSame('translate_type', $resolver->resolveTy('tt'));
        $this->assertSame('users_not_inprocess', $resolver->resolveTy('users_no_inprocess'));
        $this->assertSame('campaigns', $resolver->resolveTy('Campaigns'));
        $this->assertSame('unknown_route', $resolver->resolveTy('unknown_route'));
    }

    public function testDispatchNonCoordinatorRendersNotFound(): void
    {
        $resolver = new AdminResolver(false, false);

        ob_start();
        $resolver->dispatch('last_coord');
        $output = ob_get_clean();

        $this->assertStringContainsString('404', $output);
    }
}
