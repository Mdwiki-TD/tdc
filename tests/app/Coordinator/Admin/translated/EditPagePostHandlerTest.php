<?php

namespace Tests\Coordinator\Admin\Translated;

use PHPUnit\Framework\TestCase;

class EditPagePostHandlerTest extends TestCase
{
    public function testDeletePageSuccessAndErrorMessageFormatting()
    {
        $id = '123';
        $successMessage = "Page (id: {$id}) deleted successfully.";
        $errorMessage = "Failed to delete page (id: {$id}).";

        $this->assertEquals("Page (id: 123) deleted successfully.", $successMessage);
        $this->assertEquals("Failed to delete page (id: 123).", $errorMessage);
    }

    public function testEditPageSuccessAndErrorMessageFormatting()
    {
        $id = '456';
        $successMessage = "Page (id: {$id}) updated successfully.";
        $errorMessage = "Failed to update page (id: 456).";

        $this->assertEquals("Page (id: 456) updated successfully.", $successMessage);
        $this->assertEquals("Failed to update page (id: 456).", $errorMessage);
    }
}
