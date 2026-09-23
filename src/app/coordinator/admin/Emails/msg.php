<?php
// src/app/coordinator/admin/Emails/msg.php

namespace App\Coordinator\Admin\Emails;

use App\User\CurrentUser;
use App\Coordinator\Admin\Common\AbstractController;
use App\Tables\Main\MainTables;
use function App\APICalls\MdwikiSql\fetch_query;
use function App\APICalls\WikiApi\get_views;
use function App\Utils\Html\make_mdwiki_title;
use function App\Utils\Html\make_target_url;
use function App\Emails\Sugust\get_sugust;
use function App\csrf\generate_csrf_token;

/**
 * Class MsgController
 * Renders the "compose translation-thank-you email" form, pre-filled
 * with the translated title, view counts, and a translation suggestion.
 * Submission is posted to the external /gmail1/index.php endpoint.
 */
class MsgController extends AbstractController
{
    private string $globalUsername;
    private string $test;
    private string $title;
    private string $date;
    private string $user;
    private string $lang;
    private string $target;

    public function __construct()
    {
        $this->globalUsername = CurrentUser::getInstance()->getUsername();

        $this->test   = $_REQUEST['test'] ?? '';
        $this->title  = $_GET['title'] ?? $_POST['title'] ?? '';
        $this->date   = $_GET['date'] ?? $_POST['date'] ?? '';
        $this->user   = $_GET['user'] ?? $_POST['user'] ?? '';
        $this->lang   = $_GET['lang'] ?? $_POST['lang'] ?? '';
        $this->target = $_GET['target'] ?? $_POST['target'] ?? '';
    }

    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $this->renderHeaderAndScripts();

        $views = get_views($this->target, $this->lang, $this->date);

        $sugustTab = get_sugust($this->title, $this->lang);
        $sugust = $sugustTab['sugust'] ?? '';

        $here = $this->buildHereLink($sugust);

        $emails = $this->loadUserEmails();
        $emailTo = $emails[$this->user] ?? '';
        $ccTo = $emails[$this->globalUsername] ?? '';

        $msg = $this->buildMessageBody($views, $sugust, $here);
        $mag = $this->buildMessageWrapper($msg);

        $this->renderComposeForm($emailTo, $ccTo, $mag);
        $this->renderEditorInitScript();
    }

    /**
     * Renders UI scripts and includes the Summernote WYSIWYG editor assets.
     */
    private function renderHeaderAndScripts(): void
    {
		$this->renderHeaderScripts();

        $hoste = ($_SERVER["SERVER_NAME"] == "localhost")
            ? "https://cdnjs.cloudflare.com"
            : "https://tools-static.wmflabs.org/cdnjs";


        echo <<<HTML
            <script src='$hoste/ajax/libs/summernote/0.8.20/summernote-lite.min.js'></script>
            <link rel='stylesheet' href='$hoste/ajax/libs/summernote/0.8.20/summernote-lite.min.css' type='text/css' media='screen' charset='utf-8'>
        HTML;
    }

    /**
     * Builds the "HERE" link pointing to the translation dashboard,
     * pre-filled with the suggested next article to translate.
     */
    private function buildHereLink(string $sugust): string
    {
        $hereParams = [
            'code'  => $this->lang,
            'cat'   => 'RTT',
            'type'  => 'lead',
            'title' => $sugust,
        ];

        $hereUrl = "https://mdwiki.toolforge.org/Translation_Dashboard/translate_med/index.php?" . http_build_query($hereParams);

        return "<a target='_blank' href='$hereUrl'><b>HERE</b></a>";
    }

    /**
     * Loads a username => email lookup map.
     */
    private function loadUserEmails(): array
    {
        $emailsArray = [];

        foreach (fetch_query("select username, email from users;") as $key => $ta) {
            $emailsArray[$ta['username']] = $ta['email'];
        }

        return $emailsArray;
    }

    /**
     * Builds the plain-text (HTML fragment) body of the thank-you message.
     */
    private function buildMessageBody(int $views, string $sugust, string $here): string
    {
        $title2 = make_mdwiki_title($this->title);
        $sugust2 = make_mdwiki_title($sugust);

        $urlViews3 = 'https://' . 'pageviews.wmcloud.org/?' . http_build_query([
            'project'   => "{$this->lang}.wikipedia.org",
            'platform'  => 'all-access',
            'agent'     => 'all-agents',
            'start'     => !empty($this->date) ? $this->date : '2019-01-01',
            'end'       => date("Y-m-d", strtotime("yesterday")),
            'redirects' => '0',
            'pages'     => $this->target,
        ]);

        $views2 = "<a target='_blank' href='$urlViews3'><font color='#0000ff'>$views people</font></a>";

        $lang2 = MainTables::$xLangsTable[$this->lang]['name'] ?? $this->lang;
        $lang2 = make_target_url($this->target, $this->lang, $lang2);

        $date = $this->date;

        return <<<HTML
            <font color='#0000ff'>Thank you</font> for your prior translation of $title2 into $lang2.<br>
            Since this translation has gone live on <font color='#311873'>$date</font> it has been read by $views2.<br>
            Would you be interested in translating $sugust2? If so, simply click $here.<br>
            Once again thank you for improving access to knowledge.<br>
        HTML;
    }

    /**
     * Wraps the message body in the branded email template.
     */
    private function buildMessageWrapper(string $msg): string
    {
        $user = $this->user;

        return <<<HTML
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
                    border-style: none;' src='https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Wiki_Project_Med_Foundation_logo.svg/250px-Wiki_Project_Med_Foundation_logo.svg.png' alt='Wiki Project Med Foundation logo'>
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
    }

    /**
     * Renders the compose form used to send the email via /gmail1/index.php.
     */
    private function renderComposeForm(string $emailTo, string $ccTo, string $mag): void
    {
        $postPhp = "/gmail1/index.php";
        $csrfToken = generate_csrf_token();
        $test = $this->test;
        $lang = $this->lang;

        echo <<<HTML
            <div class1='container-fluid'>
                <form action='$postPhp' method="POST">
                  <input name='csrf_token' value="$csrfToken" type="hidden"/>
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
                                <input class='form-control' type='text' name='email_to' value='$emailTo' required/>
                            </div>
                        </div>
                        <div class='col-sm-12 col-md-7'>
                            <div class='input-group mb-2'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>
                            <input class='form-check-input' type='checkbox' name='ccme'>Send me copy</input>
                                    </span>
                                </div>
                                <input class='form-control' type='text' name='cc_to' value='$ccTo'/>
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
    }

    /**
     * Renders the Summernote WYSIWYG editor init script.
     */
    private function renderEditorInitScript(): void
    {
        echo <<<HTML
            <script>
                $('#msg').summernote({
                    placeholder: 'Hello Bootstrap 4',
                    tabsize: 6,
                    // width: 370,
                    height: 350
                });
            </script>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new MsgController();
$controller->handleRequest();
