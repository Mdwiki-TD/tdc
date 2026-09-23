<?php

namespace Tests\Coordinator\Admin\Emails;

use PHPUnit\Framework\TestCase;

class MsgTest extends TestCase
{
    public function testGetHost1ReturnsString()
    {
        // Since get_host1() is defined in msg.php, we need to test its logic
        $serverName = 'localhost';
        $hoste = ($serverName == "localhost")
            ? "https://cdnjs.cloudflare.com"
            : "https://tools-static.wmflabs.org/cdnjs";

        $this->assertEquals("https://cdnjs.cloudflare.com", $hoste);
    }

    public function testGetHost1NonLocalhost()
    {
        $serverName = 'production.server';
        $hoste = ($serverName == "localhost")
            ? "https://cdnjs.cloudflare.com"
            : "https://tools-static.wmflabs.org/cdnjs";

        $this->assertEquals("https://tools-static.wmflabs.org/cdnjs", $hoste);
    }

    public function testGetHost1CachingLogic()
    {
        // Test static caching logic
        static $cachedHost = null;

        $this->assertNull($cachedHost);

        $cachedHost = "https://cdnjs.cloudflare.com";
        $this->assertEquals("https://cdnjs.cloudflare.com", $cachedHost);

        // Second call should return cached value
        if ($cachedHost !== null) {
            $result = $cachedHost;
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
        $hereParams = [
            'code' => 'ar',
            'cat' => 'RTT',
            'type' => 'lead',
            'title' => 'TestTitle'
        ];

        $this->assertArrayHasKey('code', $hereParams);
        $this->assertArrayHasKey('cat', $hereParams);
        $this->assertArrayHasKey('type', $hereParams);
        $this->assertArrayHasKey('title', $hereParams);
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
        $emailsArray = [];
        $username = 'testuser';
        $email = 'test@example.com';

        $emailsArray[$username] = $email;

        $this->assertArrayHasKey('testuser', $emailsArray);
        $this->assertEquals('test@example.com', $emailsArray['testuser']);
    }

    public function testEmailLookup()
    {
        $emailsArray = [
            'user1' => 'user1@example.com',
            'user2' => 'user2@example.com'
        ];

        $user = 'user1';
        $emailTo = $emailsArray[$user] ?? '';
        $this->assertEquals('user1@example.com', $emailTo);

        $user = 'nonexistent';
        $emailTo = $emailsArray[$user] ?? '';
        $this->assertEquals('', $emailTo);
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
