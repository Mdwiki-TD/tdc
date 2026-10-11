<?php

namespace Tests\Coordinator\Admin\Qids;

use App\Coordinator\Admin\Qids\EditQidController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EditQidController::class)]
class EditQidTest extends TestCase
{
    public function testHeaderTitleWithId()
    {
        $_GET['id'] = '123';
        $headerTitle = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
        $this->assertEquals("Edit Qid", $headerTitle);

        unset($_GET['id']);
    }

    public function testHeaderTitleWithoutId()
    {
        unset($_GET['id']);
        $headerTitle = (($_GET['id'] ?? "") != "") ? "Edit Qid" : "Add New Qid";
        $this->assertEquals("Add New Qid", $headerTitle);
    }
}
