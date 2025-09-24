<?php
//---
if (user_in_coord == false) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
}
//---
if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
//---
include_once 'coordinator/admin/Emails/sugust.php';
//---
use Tables\Main\MainTables;
use function Actions\MdwikiSql\fetch_query;
use function Actions\WikiApi\get_views;
use function Actions\Html\make_mdwiki_title;
use function Actions\Html\make_target_url;
use function Emails\Sugust\get_sugust;
use function TDWIKI\csrf\generate_csrf_token;
//---
echo "</div>";
//---
function get_host1()
{
    // $hoste = get_host1();
    //---
    static $cached_host = null;
    //---
    if ($cached_host !== null) {
        return $cached_host; // استخدم القيمة المحفوظة
    }
    //---
    $hoste = ($_SERVER["SERVER_NAME"] == "localhost")
        ? "https://cdnjs.cloudflare.com"
        : "https://tools-static.wmflabs.org/cdnjs";
    //---
    if ($hoste == "https://tools-static.wmflabs.org/cdnjs") {
        $url = "https://tools-static.wmflabs.org";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true, // لا نريد تحميل الجسم
            CURLOPT_RETURNTRANSFER => true, // لمنع الطباعة
            CURLOPT_TIMEOUT => 3, // المهلة القصوى للاتصال
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; CDN-Checker)',
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // إذا فشل الاتصال أو لم تكن الاستجابة ضمن 200–399، نستخدم cdnjs
        if ($result === false || !empty($curlError) || $httpCode < 200 || $httpCode >= 400) {
            $hoste = "https://cdnjs.cloudflare.com";
        }
    }

    $cached_host = $hoste;

    return $hoste;
}

$hoste = get_host1();
//---
//---
echo <<<HTML
    <script src='$hoste/ajax/libs/summernote/0.8.20/summernote-lite.min.js'></script>
    <link rel='stylesheet' href='$hoste/ajax/libs/summernote/0.8.20/summernote-lite.min.css' type='text/css' media='screen' charset='utf-8'>
    <script>
        $('#mainnav').hide();
        $('#maindiv').hide();
    </script>
    <div id='yeye' class='container-fluid'>
HTML;
//---
$test   = $_REQUEST['test'] ?? '';
//---
$title  = $_GET['title'] ?? $_POST['title'] ?? '';
$date   = $_GET['date'] ?? $_POST['date'] ?? '';
$user   = $_GET['user'] ?? $_POST['user'] ?? '';
$lang   = $_GET['lang'] ?? $_POST['lang'] ?? '';
$target = $_GET['target'] ?? $_POST['target'] ?? '';
//---
$views  = get_views($target, $lang, $date);
//---
$sugust_tab = get_sugust($title, $lang);
$sugust = $sugust_tab['sugust'] ?? '';
//---
$here_params = array(
    // 'username' => rawurlencode($user),
    'code' => $lang,
    'cat' => 'RTT',
    'type' => 'lead',
    'title' => $sugust
);
//---
$here_url = "https://mdwiki.toolforge.org/Translation_Dashboard/translate/medwiki.php?" . http_build_query($here_params);
//---
$HERE = "<a target='_blank' href='$here_url'><b>HERE</b></a>";
//---

$Emails_array = [];
//---

foreach (fetch_query("select username, email from users;") as $Key => $ta) {

    $Emails_array[$ta['username']] = $ta['email'];
};
//---
$email_to = $Emails_array[$user] ?? '';
$cc_to    = $Emails_array[$username] ?? '';
//---
$title2  =    make_mdwiki_title($title);
$sugust2 = make_mdwiki_title($sugust);

//---
$url_views_2 = 'https://'
    . 'pageviews.wmcloud.org/?project=' . $lang . '.wikipedia.org&platform=all-access&agent=all-agents&redirects=0&range=all-time&pages=' . rawurlEncode($target);
//---
$start = !empty($date) ? $date : '2019-01-01';
$end = date("Y-m-d", strtotime("yesterday"));
//---
$url_views_3  = 'https://' . 'pageviews.wmcloud.org/?' . http_build_query(array(
    'project' => "$lang.wikipedia.org",
    'platform' => 'all-access',
    'agent' => 'all-agents',
    'start' => $start,
    'end' => $end,
    'redirects' => '0',
    'pages' => $target,
));
//---
// $views2 = "<font color='#0000ff'>$views people</font>";
$views2 = "<a target='_blank' href='$url_views_3'><font color='#0000ff'>$views people</font></a>";
//---
$lang2 = MainTables::$x_Langs_table[$lang]['name'] ?? $lang;
$lang2 = make_target_url($target, $lang, $name = $lang2);
//---
// print tabs values
$msg = <<<HTML
<font color='#0000ff'>Thank you</font> for your prior translation of $title2 into $lang2.<br>
Since this translation has gone live on <font color='#311873'>$date</font> it has been read by $views2.<br>
Would you be interested in translating $sugust2? If so, simply click $HERE.<br>
Once again thank you for improving access to knowledge.<br>
HTML;
//---
$mag = <<<HTML
    <div dir='ltr' style='
        background-color: #f8f9fa !important;
        position: relative;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        -ms-flex-align: center;
        align-items: center;
        -ms-flex-pack: justify;
        justify-content: space-between;
        padding: 0.5rem 1rem;'>
      <table>
      <tbody>
        <tr>
          <td>
            <img style='width: 40px;
            hight: auto;
            display: inline-block;
            margin-right: 1rem;
            vertical-align: middle;
            border-style: none;' src='https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Wiki_Project_Med_Foundation_logo.svg/400px-Wiki_Project_Med_Foundation_logo.svg.png' alt='Wiki Project Med Foundation logo'>
          </td>
          <td>
            <a style='display: inline-block;
            padding-top: 0.3125rem;
            padding-bottom: 0.3125rem;
            margin-right: 1rem;
            font-size: 1.2rem;
            line-height: inherit;
            color: #007bff;
            text-decoration: none;
            background-color: transparent;
            /* white-space: nowrap;*/
            ' href='https://mdwiki.toolforge.org/Translation_Dashboard'>Wiki Project Med Translation Dashboard</a>
          </td>
        </tr>
      </tbody>
      </table>
    </div>
    <br>
    <div style=' padding-right: 5px; padding-left: 5px; max-width: 95%;'>
        <div style='
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: 0.25rem;'>
            <table>
              <tbody>
                <tr>
                  <td>
                    <div style='padding: 0.75rem 1.25rem 0.1rem;margin-bottom: 0;background-color: rgba(0,0,0,.03);border-bottom: 1px solid rgba(0,0,0,.125);'>
                      <h3>Dear $user:</h3>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style='-ms-flex: 1 1 auto;flex: 1 1 auto;min-height: 1px;padding: 1rem;'>
                      $msg
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style='padding: 0.75rem 1.25rem;background-color: rgba(0,0,0,.03);border-top: 1px solid rgba(0,0,0,.125);'></div>
                  </td>
                </tr>
              </tbody>
            </table>
        </div>
    </div>


    HTML;
//---
$post_php = "/gmail1/index.php";
//---
$csrf_token = generate_csrf_token(); // <input name='csrf_token' value="$csrf_token" type="hidden"/>
//---
echo <<<HTML
    <div class1='container-fluid'>
        <form action='$post_php' method="POST">
          <input name='csrf_token' value="$csrf_token" type="hidden"/>
            <input type='hidden' name='test' value='$test'/>
            <input type='hidden' name='lang' value='$lang'/>
            <input type='hidden' name='nonav' value='1'/>
            <div class='row mt-3'>
                <div class='col-sm-12 col-md-5'>
                    <div class='input-group mb-2'>
                        <div class='input-group-prepend'>
                            <span class='input-group-text'>
                                <label class='mr-sm-2' for='email_to'>To:</label>
                            </span>
                        </div>
                        <input class='form-control' type='text' name='email_to' value='$email_to' required/>
                    </div>
                </div>
                <div class='col-sm-12 col-md-7'>
                    <div class='input-group mb-2'>
                        <div class='input-group-prepend'>
                            <span class='input-group-text'>
                    <input class='form-check-input' type='checkbox' name='ccme'>Send me copy</input>
                            </span>
                        </div>
                        <input class='form-control' type='text' name='cc_to' value='$cc_to'/>
                    </div>
                </div>
            </div>
            <div class='col-sm-12 col-md-6'>
                <div class='input-group mb-2'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>
                            <label class='mr-sm-2' for='msg_title'>Subject:</label>
                        </span>
                    </div>
                    <input class='form-control' type='text' name='msg_title' value='Wiki Project Med Translation Dashboard'/>
                </div>
            </div>
            <div>
                <textarea id='msg' name='msg'>
                $mag
                </textarea>
            </div>
            <div class='aligncenter mt-2'>
                <button type='submit' name='send' value='send' class='btn btn-outline-primary'>Save</button>
            </div>
        </form>
    </div>
HTML;
//---
?>
<script>
    $('#msg').summernote({
        placeholder: 'Hello Bootstrap 4',
        tabsize: 6,
        // width: 370,
        height: 350
    });
</script>
