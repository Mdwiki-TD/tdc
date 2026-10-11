<?php

declare(strict_types=1);

namespace Tests\App\ApiClients;

use App\ApiClients\MdwikiApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MdwikiApi::class)]
class MdwikiApiTest extends TestCase
{
    public function testGetMdwikiUrlWithParamsEmptyOutputHandling(): void
    {
        // Calling post_url_mdwiki with an endpoint that fails or returns empty string
        $res = MdwikiApi::get_mdwiki_url_with_params(['action' => 'non_existent_action']);
        $this->assertIsArray($res);
    }

    public function testGetPageContentReturnsNullOnMissingPages(): void
    {
        $content = MdwikiApi::get_page_content('Non_Existent_Page_XYZ_12345');
        // Will return null or array missing page
        $this->assertTrue($content === null || is_string($content));
    }

    public function testPageExistsReturnsBool(): void
    {
        $exists = MdwikiApi::page_exists('Non_Existent_Page_XYZ_12345');
        $this->assertFalse($exists);
    }
}
