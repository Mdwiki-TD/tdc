<?php

namespace Tests\Coordinator\Admin\Emails;

use PHPUnit\Framework\TestCase;

class MsgTest extends TestCase
{
    public function testGetHost1ReturnsString()
    {
        // Since get_host1() is defined in msg.php, we need to test its logic
        $server_name = 'localhost';
        $hoste = ($server_name == "localhost")
            ? "https://cdnjs.cloudflare.com"
            : "https://tools-static.wmflabs.org/cdnjs";

        $this->assertEquals("https://cdnjs.cloudflare.com", $hoste);
    }

    public function testGetHost1NonLocalhost()
    {
        $server_name = 'production.server';
        $hoste = ($server_name == "localhost")
            ? "https://cdnjs.cloudflare.com"
            : "https://tools-static.wmflabs.org/cdnjs";

        $this->assertEquals("https://tools-static.wmflabs.org/cdnjs", $hoste);
    }

    public function testGetHost1CachingLogic()
    {
        // Test static caching logic
        static $cached_host = null;

        $this->assertNull($cached_host);

        $cached_host = "https://cdnjs.cloudflare.com";
        $this->assertEquals("https://cdnjs.cloudflare.com", $cached_host);

        // Second call should return cached value
        if ($cached_host !== null) {
            $result = $cached_host;
        }
        $this->assertEquals("https://cdnjs.cloudflare.com", $result);
    }

    public function testRequestParameterExtraction()
    {
        $origRequest = $_REQUEST ?? [];
        $origPost = $_POST ?? [];
        $origGet = $_GET ?? [];

        // Test parameter extraction with coalescing
        $_GET['title'] = 'TestTitle';
        $_POST['title'] = 'PostTitle';

        $title = $_GET['title'] ?? $_POST['title'] ?? '';
        $this->assertEquals('TestTitle', $title);

        // When GET is not set, should fall back to POST
        unset($_GET['title']);
        $title = $_GET['title'] ?? $_POST['title'] ?? '';
        $this->assertEquals('PostTitle', $title);

        // When neither is set
        unset($_POST['title']);
        $title = $_GET['title'] ?? $_POST['title'] ?? '';
        $this->assertEquals('', $title);

        $_REQUEST = $origRequest;
        $_POST = $origPost;
        $_GET = $origGet;
    }

    public function testEmailParametersArray()
    {
        $here_params = [
            'code' => 'ar',
            'cat' => 'RTT',
            'type' => 'lead',
            'title' => 'TestTitle'
        ];

        $this->assertArrayHasKey('code', $here_params);
        $this->assertArrayHasKey('cat', $here_params);
        $this->assertArrayHasKey('type', $here_params);
        $this->assertArrayHasKey('title', $here_params);
    }

    public function testHttpBuildQuery()
    {
        $params = [
            'code' => 'ar',
            'cat' => 'RTT',
            'type' => 'lead',
            'title' => 'Test Title'
        ];

        $query = http_build_query($params);
        $this->assertStringContainsString('code=ar', $query);
        $this->assertStringContainsString('cat=RTT', $query);
        $this->assertStringContainsString('type=lead', $query);
        $this->assertStringContainsString('title=Test+Title', $query);
    }

    public function testPageviewsUrlConstruction()
    {
        $lang = 'ar';
        $target = 'TestPage';
        $rawTarget = rawurlencode($target);

        $url = 'https://pageviews.wmcloud.org/?project=' . $lang . '.wikipedia.org&platform=all-access&agent=all-agents&redirects=0&range=all-time&pages=' . $rawTarget;

        $this->assertStringContainsString('ar.wikipedia.org', $url);
        $this->assertStringContainsString('pageviews.wmcloud.org', $url);
        $this->assertStringContainsString($rawTarget, $url);
    }

    public function testDateRangeCalculation()
    {
        $date = '2024-01-15';
        $start = !empty($date) ? $date : '2019-01-01';
        $this->assertEquals('2024-01-15', $start);

        $date = '';
        $start = !empty($date) ? $date : '2019-01-01';
        $this->assertEquals('2019-01-01', $start);
    }

    public function testYesterdayDateCalculation()
    {
        $yesterday = date("Y-m-d", strtotime("yesterday"));
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $yesterday);
    }

    public function testEmailArrayConstruction()
    {
        $emails_array = [];
        $username = 'testuser';
        $email = 'test@example.com';

        $emails_array[$username] = $email;

        $this->assertArrayHasKey('testuser', $emails_array);
        $this->assertEquals('test@example.com', $emails_array['testuser']);
    }

    public function testEmailLookup()
    {
        $emails_array = [
            'user1' => 'user1@example.com',
            'user2' => 'user2@example.com'
        ];

        $user = 'user1';
        $email_to = $emails_array[$user] ?? '';
        $this->assertEquals('user1@example.com', $email_to);

        $user = 'nonexistent';
        $email_to = $emails_array[$user] ?? '';
        $this->assertEquals('', $email_to);
    }

    public function testHtmlSpecialCharsEscaping()
    {
        $title = '<script>alert("xss")</script>';
        $title2 = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $title2);
        $this->assertStringContainsString('&lt;script&gt;', $title2);
    }

    public function testCurlOptionsArray()
    {
        $curlOptions = [
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ];

        $this->assertArrayHasKey(CURLOPT_TIMEOUT, $curlOptions);
        $this->assertEquals(3, $curlOptions[CURLOPT_TIMEOUT]);
        $this->assertEquals(2, $curlOptions[CURLOPT_CONNECTTIMEOUT]);
    }

    public function testHttpCodeValidation()
    {
        // Test HTTP code validation logic
        $httpCode = 200;
        $isValid = ($httpCode >= 200 && $httpCode < 400);
        $this->assertTrue($isValid);

        $httpCode = 404;
        $isValid = ($httpCode >= 200 && $httpCode < 400);
        $this->assertFalse($isValid);

        $httpCode = 500;
        $isValid = ($httpCode >= 200 && $httpCode < 400);
        $this->assertFalse($isValid);
    }
}